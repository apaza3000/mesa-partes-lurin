<?php

namespace App\Models;

use App\Services\ExpedienteService;
use Src\Core\Conexion;

/**
 * OPT-01/OPT-02: el alta del expediente virtual y el numerador viven en
 * App\Services\ExpedienteService, y el catálogo de tipos de documento en
 * App\Models\TipoDocumento. Este modelo conserva solo lecturas propias del
 * portal ciudadano y delega en esas fuentes únicas.
 */
class EntregaModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::getConexion();
    }

    /* ============================================================
     * BUSCAR PERSONA POR DOCUMENTO
     * ============================================================ */
    public function buscarPersonaPorDocumento($tipo, $numero)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM personas 
             WHERE tipo_documento = ? AND numero_documento = ? LIMIT 1"
        );
        $stmt->execute([$tipo, $numero]);
        return $stmt->fetch();
    }

    /* ============================================================
     * REGISTRAR EXPEDIENTE COMPLETO (delega en ExpedienteService — OPT-01)
     * 1 expediente → 1 documento → N adjuntos
     * ============================================================ */
    public function registrarExpediente($datos, $documento, $archivos)
    {
        return (new ExpedienteService())->crearExpedienteVirtual($datos, $documento, $archivos);
    }

    /* ============================================================
     * CATÁLOGO DE TIPOS DE DOCUMENTO (delega en TipoDocumento — OPT-02)
     * ============================================================ */
    public function listarTiposDocumento()
    {
        return (new TipoDocumento())->obtenerActivos();
    }
}
