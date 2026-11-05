<?php

namespace App\Support;

/**
 * Catálogos ENUM y límites de columna copiados 1:1 de db.sql (VAL-05, VAL-03, VAL-04).
 *
 * Las vistas y los validadores deben tomar de aquí sus valores; nunca escribir
 * literales de `tipo_documento` o `estado` dentro de un .php de vista.
 */
final class Catalogos
{
    /* ---------- personas ---------- */
    public const TipoPersona = ['Natural', 'Juridica'];
    public const TipoDocumento = ['DNI', 'CE', 'RUC', 'Pasaporte', 'Otro'];

    /* ---------- usuarios ---------- */
    public const EstadoUsuario = ['Activo', 'Inactivo', 'Suspendido'];

    /* ---------- areas / roles / tipos_documento ---------- */
    public const EstadoSimple = ['Activo', 'Inactivo'];

    /* ---------- expedientes ---------- */
    public const Prioridad = ['Normal', 'Urgente'];
    public const CanalIngreso = ['Virtual', 'Presencial', 'Interno'];
    public const EstadoExpediente = ['Registrado', 'En_Proceso', 'Observado', 'Atendido', 'Archivado'];

    /* ---------- documentos ---------- */
    public const TipoDocumentoRegistro = ['Ingresado', 'Generado', 'Respuesta'];

    /* ---------- derivaciones ---------- */
    public const EstadoDerivacion = ['Pendiente', 'Recibido', 'En_Atencion', 'Atendido', 'Devuelto'];

    /* ---------- observaciones ---------- */
    public const EstadoObservacion = ['Pendiente', 'Subsanada', 'Levantada'];

    /* ---------- bandeja: estado de derivación con etiqueta legible (MEN-04) ----------
     * Fuente única para la vista `mesa-partes/bandeja.php`; así el catálogo no vive
     * en la capa de presentación ni usa un `const` global redeclarable.
     */
    public const DerivacionEtiquetas = [
        'Sin_Derivar' => 'Sin derivar',
        'Pendiente'   => 'Pendiente de recepción',
        'Recibido'    => 'Recibido por el área',
        'En_Atencion' => 'En atención',
        'Atendido'    => 'Atendido',
        'Devuelto'    => 'Devuelto',
    ];

    /**
     * Límite máximo de caracteres por `tabla.columna` según el DDL.
     * Fuente: db.sql. Ante cualquier cambio de esquema, actualizar aquí primero.
     */
    private const Limites = [
        'personas.numero_documento' => 20,
        'personas.nombres' => 100,
        'personas.apellido_paterno' => 100,
        'personas.apellido_materno' => 100,
        'personas.razon_social' => 200,
        'personas.email' => 150,
        'personas.telefono' => 30,
        'personas.direccion' => 255,
        'areas.nombre' => 100,
        'areas.siglas' => 20,
        'roles.nombre' => 50,
        'roles.descripcion' => 255,
        'usuarios.username' => 50,
        'tipos_documento.nombre' => 100,
        'tipos_documento.descripcion' => 255,
        'expedientes.codigo_expediente' => 30,
        'expedientes.asunto' => 500,
        'documentos.numero_documento' => 100,
        'documentos.asunto' => 500,
        'archivos_adjuntos.nombre_original' => 255,
        'archivos_adjuntos.nombre_archivo' => 255,
        'archivos_adjuntos.ruta_archivo' => 500,
        'derivaciones.observaciones' => 1000,
        'historial_expediente.comentario' => 1000,
        'historial_expediente.estado_anterior' => 30,
        'historial_expediente.estado_nuevo' => 30,
        'observaciones.descripcion' => 1000,
        'notificaciones.titulo' => 200,
        'notificaciones.mensaje' => 1000,
        'auditoria.modulo' => 100,
        'auditoria.accion' => 50,
        'auditoria.tabla_afectada' => 100,
        'auditoria.descripcion' => 1000,
        'auditoria.ip' => 45,
        'numeradores.tipo' => 50,
    ];

    /**
     * Longitud máxima admitida. Si la columna no está registrada devuelve
     * `$defecto` (255 por defecto) en lugar de reventar.
     */
    public static function limite(string $tablaColumna, int $defecto = 255): int
    {
        return self::Limites[$tablaColumna] ?? $defecto;
    }

    /**
     * ¿El valor cabe en la columna? Compara en CARACTERES (mb_strlen), no en
     * bytes, porque el core cuenta bytes y utf8mb3 duplica las tildes (VAL-07).
     */
    public static function cabe(string $tablaColumna, ?string $valor, int $defecto = 255): bool
    {
        return mb_strlen((string) $valor, 'UTF-8') <= self::limite($tablaColumna, $defecto);
    }

    /** Recorta en caracteres al límite de la columna. */
    public static function ajustar(string $tablaColumna, ?string $valor, int $defecto = 255): string
    {
        return mb_substr((string) $valor, 0, self::limite($tablaColumna, $defecto), 'UTF-8');
    }

    /**
     * Quita los caracteres de 4 bytes que MySQL rechaza mientras el esquema siga
     * en utf8mb3 (VAL-02). Inofensivo una vez migrada la BD a utf8mb4.
     */
    public static function sinCuatroBytes(?string $valor): string
    {
        return (string) preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', (string) $valor);
    }

    /**
     * Saneamiento de entrada para validadores: sin HTML y sin caracteres de
     * 4 bytes, pero SIN recortar. Así el validador puede rechazar con un
     * mensaje de campo legible en lugar de persistir un valor truncado.
     * (SEC-07, VAL-02; checklist §9 de error.md.)
     */
    public static function limpiar(?string $valor): string
    {
        $limpio = strip_tags((string) $valor);
        $limpio = self::sinCuatroBytes($limpio);

        return trim(preg_replace('/\s+/u', ' ', $limpio) ?? $limpio);
    }

    /**
     * Saneamiento de entrada para campos de texto libres: sin etiquetas HTML,
     * sin caracteres de 4 bytes y sin desbordar la columna (SEC-07, VAL-02).
     */
    public static function sanear(?string $valor, string $tablaColumna, int $defecto = 255): string
    {
        $limpio = strip_tags((string) $valor);
        $limpio = self::sinCuatroBytes($limpio);
        $limpio = trim(preg_replace('/\s+/u', ' ', $limpio) ?? $limpio);

        return self::ajustar($tablaColumna, $limpio, $defecto);
    }

    /** Lista de valores válida para la regla `in` de un catálogo. */
    public static function es(array $catalogo, ?string $valor): bool
    {
        return in_array((string) $valor, $catalogo, true);
    }
}
