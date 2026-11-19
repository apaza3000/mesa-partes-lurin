<?php

namespace App\Models;

use PDO;
use PDOException;
use App\Support\Catalogos;
use Src\Core\Conexion;

/**
 * TAREA E06-1: Archivo Central.
 *
 * Los expedientes atendidos se archivan con su ubicación física (caja,
 * archivador y estante) y quedan consultables por código, año y área.
 */
class Archivo
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Expedientes atendidos o archivados, con su ubicación y área actual.
     *
     * @param array{anio?:int|string,id_area?:int|string,codigo?:string,estado?:string} $filtros
     */
    public function listarAtendidos(array $filtros = []): array
    {
        try {
            $sql = "SELECT e.id_expediente, e.codigo_expediente, e.asunto, e.estado_actual,
                           e.prioridad, e.canal_ingreso, e.fecha_registro, e.fecha_atencion,
                           e.fecha_archivado, e.caja, e.archivador, e.estante,
                           p.tipo_documento AS remitente_tipo_doc,
                           p.numero_documento AS remitente_documento,
                           COALESCE(NULLIF(p.razon_social, ''), CONCAT(p.nombres, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, ''))) AS remitente_nombre,
                           (SELECT a.nombre FROM derivaciones d
                             INNER JOIN areas a ON a.id_area = d.id_area_destino
                             WHERE d.id_expediente = e.id_expediente
                             ORDER BY d.id_derivacion DESC LIMIT 1) AS area_actual,
                           (SELECT d.id_area_destino FROM derivaciones d
                             WHERE d.id_expediente = e.id_expediente
                             ORDER BY d.id_derivacion DESC LIMIT 1) AS id_area_actual
                    FROM expedientes e
                    INNER JOIN personas p ON p.id_persona = e.id_remitente
                    WHERE e.estado_actual IN ('Atendido', 'Archivado')";

            $params = [];

            $anio = (int) ($filtros['anio'] ?? 0);
            if ($anio > 0) {
                $sql .= " AND YEAR(e.fecha_registro) = :anio";
                $params[':anio'] = $anio;
            }

            $idArea = (int) ($filtros['id_area'] ?? 0);
            if ($idArea > 0) {
                $sql .= " AND (SELECT d.id_area_destino FROM derivaciones d
                                WHERE d.id_expediente = e.id_expediente
                                ORDER BY d.id_derivacion DESC LIMIT 1) = :id_area";
                $params[':id_area'] = $idArea;
            }

            $codigo = trim((string) ($filtros['codigo'] ?? ''));
            if ($codigo !== '') {
                $sql .= " AND e.codigo_expediente LIKE :codigo";
                $params[':codigo'] = '%' . $codigo . '%';
            }

            $estado = (string) ($filtros['estado'] ?? '');
            if (in_array($estado, ['Atendido', 'Archivado'], true)) {
                $sql .= " AND e.estado_actual = :estado";
                $params[':estado'] = $estado;
            }

            $sql .= " ORDER BY e.fecha_atencion DESC, e.id_expediente DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en Archivo::listarAtendidos: ' . $e->getMessage());
            return [];
        }
    }

    public function obtener(int $idExpediente): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM expedientes WHERE id_expediente = :id");
            $stmt->execute([':id' => $idExpediente]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Archiva un expediente atendido exigiendo ubicación física.
     *
     * @param array{caja:string,archivador:string,estante:string} $ubicacion
     */
    public function archivar(int $idExpediente, array $ubicacion, int $idUsuario): array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM expedientes WHERE id_expediente = :id FOR UPDATE");
            $stmt->execute([':id' => $idExpediente]);
            $expediente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$expediente) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El expediente no existe.'];
            }

            if (!in_array($expediente['estado_actual'], ['Atendido', 'Archivado'], true)) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Solo se pueden archivar expedientes atendidos; estado actual: ' . $expediente['estado_actual'] . '.'];
            }

            $caja = trim((string) ($ubicacion['caja'] ?? ''));
            $archivador = trim((string) ($ubicacion['archivador'] ?? ''));
            $estante = trim((string) ($ubicacion['estante'] ?? ''));

            if ($caja === '' || $estante === '') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La caja y el estante son obligatorios para ubicar el expediente físico.'];
            }

            $stmtUp = $this->db->prepare(
                "UPDATE expedientes
                 SET estado_actual = 'Archivado',
                     fecha_archivado = COALESCE(fecha_archivado, NOW()),
                     caja = :caja,
                     archivador = :archivador,
                     estante = :estante,
                     actualizado_en = NOW()
                 WHERE id_expediente = :id_exp"
            );
            $stmtUp->execute([
                ':caja'       => Catalogos::ajustar('expedientes.caja', $caja, 50),
                ':archivador' => Catalogos::ajustar('expedientes.archivador', $archivador, 50),
                ':estante'    => Catalogos::ajustar('expedientes.estante', $estante, 50),
                ':id_exp'     => $idExpediente,
            ]);

            $stmtHist = $this->db->prepare(
                "INSERT INTO historial_expediente (id_expediente, estado_anterior, estado_nuevo, id_usuario, comentario, fecha)
                 VALUES (:id_exp, :anterior, 'Archivado', :id_usuario, :comentario, NOW())"
            );
            $stmtHist->execute([
                ':id_exp'     => $idExpediente,
                ':anterior'   => $expediente['estado_actual'],
                ':id_usuario' => $idUsuario,
                ':comentario' => Catalogos::ajustar(
                    'historial_expediente.comentario',
                    'Expediente archivado. Ubicación física: caja ' . $caja
                    . ($archivador !== '' ? ', archivador ' . $archivador : '')
                    . ', estante ' . $estante . '.'
                ),
            ]);

            $auditoria = new Auditoria();
            $auditoria->insertar(
                $idUsuario,
                'archivo',
                'archivar',
                'expedientes',
                $idExpediente,
                'Expediente ' . $expediente['codigo_expediente'] . ' archivado en caja ' . $caja . ', estante ' . $estante . '.',
                $_SERVER['REMOTE_ADDR'] ?? null
            );

            $this->db->commit();

            return [
                'success' => true,
                'codigo'  => $expediente['codigo_expediente'],
                'message' => 'Expediente ' . $expediente['codigo_expediente'] . ' archivado correctamente.',
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Archivo::archivar: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo archivar el expediente.'];
        }
    }

    /**
     * Reubica un expediente ya archivado.
     */
    public function reubicar(int $idExpediente, array $ubicacion, int $idUsuario): array
    {
        return $this->archivar($idExpediente, $ubicacion, $idUsuario);
    }

    /**
     * Años con expedientes registrados (para el filtro).
     */
    public function anios(): array
    {
        try {
            $stmt = $this->db->query("SELECT DISTINCT YEAR(fecha_registro) AS anio FROM expedientes ORDER BY anio DESC");
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (PDOException $e) {
            return [];
        }
    }
}
