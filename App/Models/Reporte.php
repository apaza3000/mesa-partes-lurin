<?php

namespace App\Models;

use PDO;
use PDOException;
use Src\Core\Conexion;

/**
 * TAREA E09-1: consultas agregadas del módulo de reportes.
 *
 * Todos los indicadores comparten el mismo universo filtrado
 * (fecha, área, tipo, estado, canal y prioridad) para que las tarjetas,
 * los gráficos y las exportaciones muestren exactamente lo mismo.
 *
 * SLA institucional: 5 días calendario desde el registro hasta la atención.
 */
class Reporte
{
    private PDO $db;

    public const SLA_DIAS = 5;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /**
     * Totales generales del periodo filtrado.
     *
     * @param array<string, mixed> $filtros
     * @return array<string, int|float>
     */
    public function resumen(array $filtros = []): array
    {
        try {
            [$base, $params] = $this->baseSql($filtros);

            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado') THEN 1 ELSE 0 END) AS atendidos,
                        SUM(CASE WHEN estado_actual IN ('Registrado', 'En_Proceso', 'Observado') THEN 1 ELSE 0 END) AS pendientes,
                        SUM(CASE WHEN estado_actual = 'Observado' THEN 1 ELSE 0 END) AS observados,
                        SUM(CASE WHEN estado_actual = 'Archivado' THEN 1 ELSE 0 END) AS archivados,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado')
                                  AND dias_transcurridos <= :sla THEN 1 ELSE 0 END) AS sla_cumplido,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado')
                                  AND dias_transcurridos > :sla THEN 1 ELSE 0 END) AS sla_incumplido,
                        SUM(CASE WHEN estado_actual IN ('Registrado', 'En_Proceso', 'Observado')
                                  AND dias_transcurridos > :sla THEN 1 ELSE 0 END) AS sla_pendiente_vencido,
                        ROUND(AVG(CASE WHEN fecha_atencion IS NOT NULL THEN dias_transcurridos END), 1) AS promedio_atencion
                 FROM ({$base}) AS r"
            );

            $params[':sla'] = self::SLA_DIAS;
            $stmt->execute($params);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total'                => (int) ($fila['total'] ?? 0),
                'atendidos'            => (int) ($fila['atendidos'] ?? 0),
                'pendientes'           => (int) ($fila['pendientes'] ?? 0),
                'observados'           => (int) ($fila['observados'] ?? 0),
                'archivados'           => (int) ($fila['archivados'] ?? 0),
                'sla_cumplido'         => (int) ($fila['sla_cumplido'] ?? 0),
                'sla_incumplido'       => (int) ($fila['sla_incumplido'] ?? 0),
                'sla_pendiente_vencido'=> (int) ($fila['sla_pendiente_vencido'] ?? 0),
                'promedio_atencion'    => (float) ($fila['promedio_atencion'] ?? 0),
                'sla_dias'             => self::SLA_DIAS,
            ];
        } catch (PDOException $e) {
            error_log('Error en Reporte::resumen: ' . $e->getMessage());
            return [
                'total' => 0, 'atendidos' => 0, 'pendientes' => 0, 'observados' => 0,
                'archivados' => 0, 'sla_cumplido' => 0, 'sla_incumplido' => 0,
                'sla_pendiente_vencido' => 0, 'promedio_atencion' => 0.0,
                'sla_dias' => self::SLA_DIAS,
            ];
        }
    }

    /**
     * Volumen por día de registro (para el gráfico de línea).
     *
     * @return array<int, array{dia:string,total:int}>
     */
    public function porDia(array $filtros = []): array
    {
        return $this->agrupar($filtros, 'DATE(fecha_registro)', 'dia');
    }

    /** @return array<int, array{etiqueta:string,total:int}> */
    public function porArea(array $filtros = []): array
    {
        return $this->agrupar($filtros, "COALESCE(area_actual, 'Sin derivar')", 'etiqueta');
    }

    /** @return array<int, array{etiqueta:string,total:int}> */
    public function porTipo(array $filtros = []): array
    {
        return $this->agrupar($filtros, "COALESCE(tipo_documento, 'Sin tipo')", 'etiqueta');
    }

    /** @return array<int, array{etiqueta:string,total:int}> */
    public function porEstado(array $filtros = []): array
    {
        return $this->agrupar($filtros, 'estado_actual', 'etiqueta');
    }

    /** @return array<int, array{etiqueta:string,total:int}> */
    public function porCanal(array $filtros = []): array
    {
        return $this->agrupar($filtros, 'canal_ingreso', 'etiqueta');
    }

    /** @return array<int, array{etiqueta:string,total:int}> */
    public function porPrioridad(array $filtros = []): array
    {
        return $this->agrupar($filtros, 'prioridad', 'etiqueta');
    }

    /**
     * Volumen por mes de registro, en orden cronológico (gráfico de línea).
     *
     * @return array<int, array{etiqueta:string,total:int}>
     */
    public function porMes(array $filtros = []): array
    {
        try {
            [$base, $params] = $this->baseSql($filtros);

            $stmt = $this->db->prepare(
                "SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS etiqueta, COUNT(*) AS total
                 FROM ({$base}) AS r
                 GROUP BY DATE_FORMAT(fecha_registro, '%Y-%m')
                 ORDER BY etiqueta ASC"
            );
            $stmt->execute($params);

            return array_map(static fn(array $f): array => [
                'etiqueta' => (string) ($f['etiqueta'] ?? ''),
                'total'    => (int) ($f['total'] ?? 0),
            ], $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (PDOException $e) {
            error_log('Error en Reporte::porMes: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Indicadores de SLA listos para las tarjetas y el PDF.
     *
     * @return array{dias_sla:int,dentro:int,fuera:int,pendientes:int,promedio_horas:float}
     */
    public function sla(array $filtros = []): array
    {
        $resumen = $this->resumen($filtros);

        return [
            'dias_sla'       => self::SLA_DIAS,
            'dentro'         => (int) ($resumen['sla_cumplido'] ?? 0),
            'fuera'          => (int) ($resumen['sla_incumplido'] ?? 0),
            'pendientes'     => (int) ($resumen['sla_pendiente_vencido'] ?? 0),
            'promedio_horas' => round(((float) ($resumen['promedio_atencion'] ?? 0)) * 24, 1),
        ];
    }

    /**
     * Alias compatible con el controlador/exportaciones.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listado(array $filtros = [], int $limite = 5000): array
    {
        return $this->detalle($filtros, $limite);
    }

    /**
     * Cumplimiento de SLA por área actual del expediente.
     *
     * @return array<int, array{area:string,total:int,atendidos:int,cumplido:int,incumplido:int,pendientes_vencidos:int}>
     */
    public function slaPorArea(array $filtros = []): array
    {
        try {
            [$base, $params] = $this->baseSql($filtros);
            $params[':sla'] = self::SLA_DIAS;

            $stmt = $this->db->prepare(
                "SELECT COALESCE(area_actual, 'Sin derivar') AS area,
                        COUNT(*) AS total,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado') THEN 1 ELSE 0 END) AS atendidos,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado') AND dias_transcurridos <= :sla THEN 1 ELSE 0 END) AS cumplido,
                        SUM(CASE WHEN estado_actual IN ('Atendido', 'Archivado') AND dias_transcurridos > :sla THEN 1 ELSE 0 END) AS incumplido,
                        SUM(CASE WHEN estado_actual IN ('Registrado', 'En_Proceso', 'Observado') AND dias_transcurridos > :sla THEN 1 ELSE 0 END) AS pendientes_vencidos
                 FROM ({$base}) AS r
                 GROUP BY COALESCE(area_actual, 'Sin derivar')
                 ORDER BY total DESC"
            );
            $stmt->execute($params);

            return array_map(static function (array $f): array {
                return [
                    'area'                => (string) $f['area'],
                    'total'               => (int) $f['total'],
                    'atendidos'           => (int) $f['atendidos'],
                    'cumplido'            => (int) $f['cumplido'],
                    'incumplido'          => (int) $f['incumplido'],
                    'pendientes_vencidos' => (int) $f['pendientes_vencidos'],
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (PDOException $e) {
            error_log('Error en Reporte::slaPorArea: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Filas detalladas para la tabla en pantalla y para las exportaciones.
     *
     * @return array<int, array<string, mixed>>
     */
    public function detalle(array $filtros = [], int $limite = 5000): array
    {
        try {
            [$base, $params] = $this->baseSql($filtros);
            $limite = max(1, min(20000, $limite));

            $stmt = $this->db->prepare(
                "SELECT * FROM ({$base}) AS r
                 ORDER BY fecha_registro DESC, id_expediente DESC
                 LIMIT {$limite}"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('Error en Reporte::detalle: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array{etiqueta:string,total:int}>
     */
    private function agrupar(array $filtros, string $expresion, string $alias): array
    {
        try {
            [$base, $params] = $this->baseSql($filtros);

            $stmt = $this->db->prepare(
                "SELECT {$expresion} AS {$alias}, COUNT(*) AS total
                 FROM ({$base}) AS r
                 GROUP BY {$expresion}
                 ORDER BY total DESC"
            );
            $stmt->execute($params);

            return array_map(static function (array $f) use ($alias): array {
                return [
                    'etiqueta' => (string) ($f[$alias] ?? ''),
                    'total'    => (int) ($f['total'] ?? 0),
                ];
            }, $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
        } catch (PDOException $e) {
            error_log('Error en Reporte::agrupar: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Universo filtrado de expedientes con sus datos derivados.
     *
     * @return array{0:string,1:array<string,mixed>}
     */
    private function baseSql(array $filtros): array
    {
        $where = 'WHERE 1 = 1';
        $params = [];

        $desde = trim((string) ($filtros['fecha_desde'] ?? ''));
        if ($desde !== '' && $this->fechaValida($desde)) {
            $where .= ' AND e.fecha_registro >= :fecha_desde';
            $params[':fecha_desde'] = $desde . ' 00:00:00';
        }

        $hasta = trim((string) ($filtros['fecha_hasta'] ?? ''));
        if ($hasta !== '' && $this->fechaValida($hasta)) {
            $where .= ' AND e.fecha_registro <= :fecha_hasta';
            $params[':fecha_hasta'] = $hasta . ' 23:59:59';
        }

        $idArea = (int) ($filtros['id_area'] ?? 0);
        if ($idArea > 0) {
            $where .= ' AND (SELECT d.id_area_destino FROM derivaciones d
                              WHERE d.id_expediente = e.id_expediente
                              ORDER BY d.id_derivacion DESC LIMIT 1) = :id_area';
            $params[':id_area'] = $idArea;
        }

        $idTipo = (int) ($filtros['id_tipo_doc'] ?? 0);
        if ($idTipo > 0) {
            $where .= ' AND (SELECT d.id_tipo_doc FROM documentos d
                              WHERE d.id_expediente = e.id_expediente
                              ORDER BY d.es_principal DESC, d.id_documento ASC LIMIT 1) = :id_tipo';
            $params[':id_tipo'] = $idTipo;
        }

        $estado = trim((string) ($filtros['estado'] ?? ''));
        if (in_array($estado, ['Registrado', 'En_Proceso', 'Observado', 'Atendido', 'Archivado'], true)) {
            $where .= ' AND e.estado_actual = :estado';
            $params[':estado'] = $estado;
        }

        $canal = trim((string) ($filtros['canal'] ?? ''));
        if (in_array($canal, ['Virtual', 'Presencial', 'Interno'], true)) {
            $where .= ' AND e.canal_ingreso = :canal';
            $params[':canal'] = $canal;
        }

        $prioridad = trim((string) ($filtros['prioridad'] ?? ''));
        if (in_array($prioridad, ['Normal', 'Urgente'], true)) {
            $where .= ' AND e.prioridad = :prioridad';
            $params[':prioridad'] = $prioridad;
        }

        $sql = "SELECT e.id_expediente, e.codigo_expediente, e.asunto, e.prioridad,
                       e.canal_ingreso, e.estado_actual, e.fecha_registro, e.fecha_atencion,
                       COALESCE(NULLIF(p.razon_social, ''),
                           TRIM(CONCAT_WS(' ', p.nombres, p.apellido_paterno, p.apellido_materno))) AS remitente_nombre,
                       p.tipo_documento AS remitente_tipo_doc,
                       p.numero_documento AS remitente_documento,
                       (SELECT d.id_area_destino FROM derivaciones d
                         WHERE d.id_expediente = e.id_expediente
                         ORDER BY d.id_derivacion DESC LIMIT 1) AS id_area_actual,
                       (SELECT a.nombre FROM derivaciones d
                         INNER JOIN areas a ON a.id_area = d.id_area_destino
                         WHERE d.id_expediente = e.id_expediente
                         ORDER BY d.id_derivacion DESC LIMIT 1) AS area_actual,
                       (SELECT d.id_tipo_doc FROM documentos d
                         WHERE d.id_expediente = e.id_expediente
                         ORDER BY d.es_principal DESC, d.id_documento ASC LIMIT 1) AS id_tipo_doc,
                       (SELECT td.nombre FROM documentos d
                         INNER JOIN tipos_documento td ON td.id_tipo_doc = d.id_tipo_doc
                         WHERE d.id_expediente = e.id_expediente
                         ORDER BY d.es_principal DESC, d.id_documento ASC LIMIT 1) AS tipo_documento,
                       DATEDIFF(COALESCE(e.fecha_atencion, NOW()), e.fecha_registro) AS dias_transcurridos,
                       CASE
                           WHEN e.estado_actual IN ('Atendido', 'Archivado')
                                AND DATEDIFF(e.fecha_atencion, e.fecha_registro) <= " . self::SLA_DIAS . " THEN 'Dentro de plazo'
                           WHEN e.estado_actual IN ('Atendido', 'Archivado') THEN 'Fuera de plazo'
                           WHEN DATEDIFF(NOW(), e.fecha_registro) > " . self::SLA_DIAS . " THEN 'Pendiente vencido'
                           ELSE 'En plazo'
                       END AS sla
                FROM expedientes e
                INNER JOIN personas p ON p.id_persona = e.id_remitente
                {$where}";

        return [$sql, $params];
    }

    private function fechaValida(string $fecha): bool
    {
        $partes = explode('-', $fecha);
        return count($partes) === 3 && checkdate((int) $partes[1], (int) $partes[2], (int) $partes[0]);
    }
}
