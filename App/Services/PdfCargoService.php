<?php

namespace App\Services;

use TCPDF;

/**
 * Servicio de emisión de documentos formales en PDF (EPICA 3).
 *
 * Genera:
 *  - El Cargo de Recepción Documental con sello digital y código QR (TAREA E03-1).
 *  - La Hoja de Ruta / Trámite físico con casilleros de firma y sello (TAREA E03-2).
 *
 * Usa TCPDF 6.8 (fuentes core14 embebidas + QR nativo), sin dependencias externas.
 */
class PdfCargoService
{
    private const MARGEN = 14;
    private const ANCHO_UTIL = 182; // 210 - 2*14
    private const COLOR_INSTITUCIONAL = [0, 61, 110];   // azul municipal
    private const COLOR_SUAVE = [235, 240, 246];
    private const COLOR_GRAFITO = [90, 96, 104];

    /**
     * URL pública de consulta para el código QR de un expediente.
     * Prioriza APP_URL; si no, deriva el host de la petición actual.
     */
    public static function urlConsulta(string $codigo): string
    {
        $base = '';
        if (function_exists('env')) {
            $base = rtrim((string) (env('APP_URL') ?: ''), '/');
        }
        if ($base === '') {
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'mesapartes.lurin.gob.pe';
            $base   = $scheme . '://' . $host;
        }
        return $base . '/consulta?codigo=' . rawurlencode($codigo);
    }

    /**
     * Envía el binario PDF al navegador con encabezados correctos.
     * Reutilizable desde cualquier controlador (descarga o impresión).
     */
    public static function stream(string $contenido, string $nombre, string $disposicion = 'inline'): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposicion . '; filename="' . str_replace('"', '', $nombre) . '"');
        header('Content-Length: ' . strlen($contenido));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        echo $contenido;
        exit;
    }

    /**
     * Datos normalizados esperados:
     * [
     *   'codigo' => 'EXP-2026-00001',
     *   'canal' => 'Presencial'|'Interno'|'Virtual',
     *   'prioridad' => 'Normal'|'Urgente',
     *   'estado' => 'Registrado',
     *   'asunto' => '...',
     *   'fecha_registro' => 'Y-m-d H:i:s',
     *   'remitente' => ['tipo_documento','numero_documento','nombre','direccion','telefono','email'],
     *   'documentos' => [ ['tipo','numero','asunto','folios'], ... ],
     *   'total_folios' => int,
     *   'derivaciones' => [ ['origen','destino','estado','fecha_recepcion'], ... ],
     *   'consulta_url' => 'https://.../consulta?codigo=EXP-...',
     * ]
     */
    public function generarCargo(array $d): string
    {
        $pdf = $this->nuevoPdf('Cargo de Recepción');
        $pdf->AddPage();

        $y = $this->encabezado($pdf, 'CARGO DE RECEPCIÓN DOCUMENTAL', $d['codigo'] ?? '', $d['canal'] ?? 'Presencial');

        // Bloque remitente + registro (dos columnas)
        $y = $this->datosGenerales($pdf, $d, $y);

        // Tabla de documentos recibidos
        $y = $this->tablaDocumentos($pdf, $d, $y);

        // Trazabilidad / derivación
        $y = $this->trazabilidad($pdf, $d, $y);

        // Cierre: sello digital, firma y QR
        $this->cierreCargo($pdf, $d, $y);

        return $pdf->Output($this->nombreArchivo($d['codigo'] ?? 'cargo', 'cargo'), 'S');
    }

    /**
     * Hoja de Ruta para pegado en la carátula del expediente físico (TAREA E03-2).
     */
    public function generarHojaRuta(array $d): string
    {
        $pdf = $this->nuevoPdf('Hoja de Ruta');
        $pdf->AddPage();

        $y = $this->encabezado($pdf, 'HOJA DE RUTA DEL TRÁMITE', $d['codigo'] ?? '', $d['canal'] ?? 'Presencial');

        // Ficha resumen del expediente
        $y = $this->fichaResumen($pdf, $d, $y);

        // Casilleros de pase entre áreas
        $y = $this->casillerosRuta($pdf, $d, $y);

        // QR de seguimiento
        $this->pieHojaRuta($pdf, $d, $y);

        return $pdf->Output($this->nombreArchivo($d['codigo'] ?? 'ruta', 'hoja-ruta'), 'S');
    }

    /* ============================================================
     * INFRAESTRUCTURA PDF
     * ============================================================ */
    private function nuevoPdf(string $titulo): TCPDF
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        $pdf->SetMargins(self::MARGEN, self::MARGEN, self::MARGEN);
        $pdf->SetAutoPageBreak(true, self::MARGEN);
        $pdf->SetTitle($titulo);
        $pdf->SetSubject('Mesa de Partes — Municipalidad Distrital de Lurín');
        $pdf->SetCreator('Sistema de Trámite Documentario');
        $pdf->SetFont('helvetica', '', 9);
        return $pdf;
    }

    /**
     * Cabecera institucional con escudo/logo y título. Devuelve la coordenada Y libre.
     */
    private function encabezado(TCPDF $pdf, string $titulo, string $codigo, string $canal): float
    {
        $x = self::MARGEN;
        $anchoBand = self::ANCHO_UTIL;
        $altoBand = 24;
        $top = $pdf->GetY();

        // Banda institucional
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetLineWidth(0.6);
        $pdf->Rect($x, $top, $anchoBand, $altoBand, 'D');

        // Logo (ruta absoluta real; se evita Path::base porque crea directorios)
        $logo = dirname(__DIR__, 2) . '/view/assets/img/logo.png';
        if (is_file($logo)) {
            $pdf->Image($logo, $x + 3, $top + 3, 18, 18, 'PNG');
        }

        $pdf->SetXY($x + 24, $top + 4);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(...self::COLOR_INSTITUCIONAL);
        $pdf->Cell($anchoBand - 28, 5, mb_strtoupper('Municipalidad Distrital de Lurín'), 0, 1, 'L');

        // Cell con ln=1 reinicia X al margen izquierdo: realinear bajo el texto del título
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->SetX($x + 24);
        $pdf->Cell($anchoBand - 28, 4, 'Mesa de Partes — Sistema de Trámite Documentario', 0, 1, 'L');
        $pdf->SetX($x + 24);
        $pdf->Cell($anchoBand - 28, 4, 'Plataforma virtual de recepción y distribución documental', 0, 1, 'L');

        // Título a banda completa (debajo de la cabecera)
        $pdf->SetXY($x, $top + $altoBand + 2);
        $pdf->SetFillColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell($anchoBand, 8, $titulo, 0, 1, 'C', true);

        // Código y canal
        $pdf->SetXY($x, $top + $altoBand + 10);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell($anchoBand, 5, 'Código de expediente: ' . $codigo . '   ·   Canal de ingreso: ' . $canal, 0, 1, 'C');

        return $pdf->GetY() + 3;
    }

    /**
     * Filas de etiqueta/valor en dos columnas.
     */
    private function datosGenerales(TCPDF $pdf, array $d, float $y): float
    {
        $r = $d['remitente'] ?? [];
        $fecha = $this->formateaFecha($d['fecha_registro'] ?? '');

        $pdf->SetY($y);
        $this->seccion($pdf, 'Datos del administrado y del registro');

        $mitad = self::ANCHO_UTIL / 2;
        $xIzq  = self::MARGEN;
        $xDer  = self::MARGEN + $mitad;
        $y0    = $pdf->GetY();

        // Columna izquierda (campo() realinea X en cada MultiCell)
        $pdf->SetXY($xIzq, $y0);
        $this->campo($pdf, 'Remitente', $r['nombre'] ?? '—', $mitad, true, $xIzq);
        $this->campo($pdf, 'Documento', trim(($r['tipo_documento'] ?? '') . ': ' . ($r['numero_documento'] ?? '—')), $mitad, false, $xIzq);
        $this->campo($pdf, 'Dirección', $r['direccion'] ?? '—', $mitad, false, $xIzq);
        $yIzq = $pdf->GetY();

        // Columna derecha
        $pdf->SetXY($xDer, $y0);
        $this->campo($pdf, 'Fecha y hora de registro', $fecha, $mitad, true, $xDer);
        $this->campo($pdf, 'Prioridad', $d['prioridad'] ?? 'Normal', $mitad, false, $xDer);
        $this->campo($pdf, 'Estado actual', $d['estado'] ?? 'Registrado', $mitad, false, $xDer);
        $this->campo($pdf, 'Correo / Teléfono', trim(($r['email'] ?? '') . '  ' . (!empty($r['telefono']) ? '· Tel. ' . $r['telefono'] : '')) ?: '—', $mitad, false, $xDer);
        $yDer = $pdf->GetY();

        // Asunto (ancho completo, debajo de la columna más alta)
        $pdf->SetXY($xIzq, max($yIzq, $yDer));
        $this->campo($pdf, 'Asunto', $d['asunto'] ?? '—', self::ANCHO_UTIL, false, $xIzq);

        return $pdf->GetY() + 2;
    }

    private function tablaDocumentos(TCPDF $pdf, array $d, float $y): float
    {
        $pdf->SetY($y);
        $this->seccion($pdf, 'Documentos recibidos');

        $cols = [
            ['Tipo de documento', 45],
            ['N.° documento', 30],
            ['Asunto', 77],
            ['Folios', 30],
        ];

        $pdf->SetX(self::MARGEN);
        $pdf->SetFillColor(...self::COLOR_SUAVE);
        $pdf->SetTextColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetDrawColor(...self::COLOR_GRAFITO);
        $pdf->SetLineWidth(0.2);
        foreach ($cols as [$t, $w]) {
            $pdf->Cell($w, 6, $t, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(30, 30, 30);
        $documentos = $d['documentos'] ?? [];
        if (empty($documentos)) {
            $pdf->SetX(self::MARGEN);
            $pdf->MultiCell(array_sum(array_column($cols, 1)), 6, 'Sin documentos registrados.', 1, 'C');
        } else {
            foreach ($documentos as $doc) {
                $pdf->SetX(self::MARGEN);
                $pdf->Cell(45, 6, $this->recorta((string) ($doc['tipo'] ?? 'Documento'), 32), 1, 0, 'L');
                $pdf->Cell(30, 6, $this->recorta((string) ($doc['numero'] ?? '—'), 20), 1, 0, 'C');
                $pdf->MultiCell(77, 6, $this->recorta((string) ($doc['asunto'] ?? '—'), 70), 1, 'L');
                $pdf->SetX(self::MARGEN + 45 + 30 + 77);
                $pdf->Cell(30, 6, (string) (int) ($doc['folios'] ?? 0), 1, 0, 'C');
            }
        }

        // Fila de total
        $pdf->SetX(self::MARGEN);
        $pdf->SetFillColor(...self::COLOR_SUAVE);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(45 + 30 + 77, 6, 'TOTAL DE FOLIOS RECIBIDOS', 1, 0, 'R', true);
        $pdf->Cell(30, 6, (string) (int) ($d['total_folios'] ?? 0), 1, 1, 'C', true);

        return $pdf->GetY() + 2;
    }

    private function trazabilidad(TCPDF $pdf, array $d, float $y): float
    {
        $pdf->SetY($y);
        $this->seccion($pdf, 'Derivación inicial');

        $pdf->SetX(self::MARGEN);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(30, 30, 30);
        $derivaciones = $d['derivaciones'] ?? [];

        if (empty($derivaciones)) {
            $pdf->SetFillColor(253, 240, 220);
            $pdf->SetDrawColor(200, 150, 60);
            $pdf->MultiCell(self::ANCHO_UTIL, 8, 'El expediente se registró sin derivación inicial; permanece en la bandeja de Mesa de Partes para su asignación.', 1, 'L', true);
        } else {
            foreach ($derivaciones as $der) {
                $recibido = !empty($der['fecha_recepcion'])
                    ? '  —  Recibido: ' . $this->formateaFecha($der['fecha_recepcion'])
                    : '';
                $linea = sprintf(
                    '•  %s  →  %s   (Estado: %s)%s',
                    $der['origen'] ?? 'Mesa de Partes',
                    $der['destino'] ?? '—',
                    $der['estado'] ?? 'Pendiente',
                    $recibido
                );
                $pdf->SetX(self::MARGEN);
                $pdf->MultiCell(self::ANCHO_UTIL, 6, $linea, 0, 'L');
            }
        }

        return $pdf->GetY() + 3;
    }

    private function cierreCargo(TCPDF $pdf, array $d, float $y): void
    {
        $pdf->SetY($y);
        $x = self::MARGEN;

        // QR (izquierda)
        $qrUrl = $d['consulta_url'] ?? ('/consulta?codigo=' . ($d['codigo'] ?? ''));
        $qrW = 32;
        $pdf->write2DBarcode($qrUrl, 'QRCODE,M', $x, $y, $qrW, $qrW, [
            'border' => 'N', 'padding' => 1,
            'fgcolor' => [0, 0, 0], 'bgcolor' => [255, 255, 255],
        ], ['T' => true, 'L' => 'Escanee para seguir su trámite']);

        // Sello digital (centro)
        $selloX = $x + $qrW + 8;
        $selloW = 46;
        $this->selloDigital($pdf, $selloX, $y, $selloW, $qrW, $d);

        // Firma del receptor (derecha)
        $firmaX = $selloX + $selloW + 8;
        $firmaW = self::MARGEN + self::ANCHO_UTIL - $firmaX;
        $pdf->SetDrawColor(...self::COLOR_GRAFITO);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($firmaX, $y + $qrW - 8, $firmaX + $firmaW, $y + $qrW - 8);
        $pdf->SetXY($firmaX, $y + $qrW - 7);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->MultiCell($firmaW, 3.5, 'Firma del receptor — Mesa de Partes', 0, 'C');
        $pdf->SetX($firmaX);
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->MultiCell($firmaW, 3, 'Documento registrado electrónicamente en el sistema', 0, 'C');

        // Nota legal al pie
        $notaY = $y + $qrW + 6;
        $pdf->SetXY($x, $notaY);
        $pdf->SetDrawColor(...self::COLOR_SUAVE);
        $pdf->SetLineWidth(0.2);
        $pdf->Line($x, $notaY, $x + self::ANCHO_UTIL, $notaY);
        $pdf->SetXY($x, $notaY + 2);
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->MultiCell(self::ANCHO_UTIL, 4,
            'Conserve este cargo: acredita la recepción de su documento ante la Municipalidad Distrital de Lurín y le permite '
            . 'consultar el estado del trámite mediante el código QR o el código de expediente. El plazo de atención se computa '
            . 'desde la fecha y hora de registro.', 0, 'J');
    }

    private function selloDigital(TCPDF $pdf, float $x, float $y, float $w, float $h, array $d): void
    {
        $pdf->SetDrawColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetLineWidth(0.4);
        $pdf->Rect($x, $y, $w, $h, 'D');

        $pdf->SetXY($x, $y + 2);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetTextColor(...self::COLOR_INSTITUCIONAL);
        $pdf->MultiCell($w, 3.5, 'SELLO DIGITAL', 0, 'C');

        $pdf->SetXY($x, $y + 8);
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->MultiCell($w, 3.2, 'MUNICIPALIDAD DISTRITAL\nDE LURÍN\n' . $this->formateaFecha($d['fecha_registro'] ?? ''), 0, 'C');
    }

    /* ============================================================
     * HOJA DE RUTA — widgets
     * ============================================================ */
    private function fichaResumen(TCPDF $pdf, array $d, float $y): float
    {
        $pdf->SetY($y);
        $this->seccion($pdf, 'Ficha del expediente');

        $r = $d['remitente'] ?? [];
        $pdf->SetX(self::MARGEN);
        $this->campo($pdf, 'Remitente', ($r['nombre'] ?? '—') . '  (' . ($r['tipo_documento'] ?? '') . ': ' . ($r['numero_documento'] ?? '—') . ')', self::ANCHO_UTIL, true);
        $pdf->SetX(self::MARGEN);
        $this->campo($pdf, 'Asunto', $d['asunto'] ?? '—', self::ANCHO_UTIL);
        $pdf->SetX(self::MARGEN);
        $this->campo($pdf, 'Documentos / Folios', (string) count($d['documentos'] ?? []) . ' documento(s) · ' . (int) ($d['total_folios'] ?? 0) . ' folio(s) · Prioridad: ' . ($d['prioridad'] ?? 'Normal'), self::ANCHO_UTIL);

        return $pdf->GetY() + 3;
    }

    private function casillerosRuta(TCPDF $pdf, array $d, float $y): float
    {
        $pdf->SetY($y);
        $this->seccion($pdf, 'Recorrido entre áreas (firmas y sellos)');

        $cols = [
            ['N.°', 10],
            ['Área que recibe', 52],
            ['Fecha / Hora', 30],
            ['Recibido (firma)', 42],
            ['Sello', 28],
            ['Observaciones', 20],
        ];

        $pdf->SetX(self::MARGEN);
        $pdf->SetFillColor(...self::COLOR_SUAVE);
        $pdf->SetTextColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetDrawColor(...self::COLOR_GRAFITO);
        $pdf->SetLineWidth(0.2);
        foreach ($cols as [$t, $w]) {
            $pdf->Cell($w, 6, $t, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(30, 30, 30);

        // Precarga la derivación inicial como primera ruta conocida
        $filas = [];
        $derivaciones = $d['derivaciones'] ?? [];
        foreach ($derivaciones as $i => $der) {
            $filas[] = [
                'n' => $i + 1,
                'area' => $der['destino'] ?? '—',
                'fecha' => !empty($der['fecha_recepcion']) ? $this->formateaFecha($der['fecha_recepcion']) : ($der['estado'] ?? 'Pendiente'),
            ];
        }
        // Rellenar hasta 8 casilleros físicos
        $n = count($filas);
        for ($k = $n + 1; $k <= 8; $k++) {
            $filas[] = ['n' => $k, 'area' => '', 'fecha' => ''];
        }

        foreach ($filas as $fila) {
            $pdf->SetX(self::MARGEN);
            $alto = 12;
            $x0 = $pdf->GetX();
            $y0 = $pdf->GetY();
            $pdf->Cell(10, $alto, (string) $fila['n'], 1, 0, 'C');
            $pdf->Cell(52, $alto, $this->recorta((string) $fila['area'], 40), 1, 0, 'L');
            $pdf->Cell(30, $alto, $fila['fecha'], 1, 0, 'C');
            $pdf->Cell(42, $alto, '', 1, 0, 'C');   // firma
            $pdf->Cell(28, $alto, '', 1, 0, 'C');   // sello
            $pdf->Cell(20, $alto, '', 1, 0, 'C');   // observaciones
            $pdf->SetXY($x0, $y0 + $alto);
        }

        return $pdf->GetY() + 3;
    }

    private function pieHojaRuta(TCPDF $pdf, array $d, float $y): void
    {
        $qrUrl = $d['consulta_url'] ?? ('/consulta?codigo=' . ($d['codigo'] ?? ''));
        $qrW = 30;
        $pdf->write2DBarcode($qrUrl, 'QRCODE,M', self::MARGEN, $y, $qrW, $qrW, [
            'border' => 'N', 'padding' => 1,
            'fgcolor' => [0, 0, 0], 'bgcolor' => [255, 255, 255],
        ], ['T' => true, 'L' => 'Seguimiento del expediente']);

        $pdf->SetXY(self::MARGEN + $qrW + 8, $y);
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->MultiCell(self::ANCHO_UTIL - $qrW - 8, 4,
            'Hoja de ruta impresa por Mesa de Partes para el control físico del expediente ' . ($d['codigo'] ?? '') . '. '
            . 'Cada área debe firmar y sellar al recibir y al derivar. El incumplimiento del plazo se reporta al responsable del área.', 0, 'J');
    }

    /* ============================================================
     * HELPERS DE DIBUJO Y FORMATO
     * ============================================================ */
    private function seccion(TCPDF $pdf, string $titulo): void
    {
        $pdf->SetX(self::MARGEN);
        $pdf->SetFillColor(...self::COLOR_INSTITUCIONAL);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->Cell(self::ANCHO_UTIL, 6, mb_strtoupper($titulo), 0, 1, 'L', true);
        $pdf->SetTextColor(30, 30, 30);
    }

    /**
     * Etiqueta + valor con fondo suave. Devuelve la Y tras el bloque.
     */
    private function campo(TCPDF $pdf, string $etiqueta, string $valor, float $ancho, bool $negrita = false, ?float $x = null): void
    {
        // MultiCell siempre devuelve X al margen izquierdo: en layouts de columnas
        // o varias líneas hay que realinear antes de cada bloque.
        if ($x !== null) {
            $pdf->SetX($x);
        }
        $pdf->SetFillColor(...self::COLOR_SUAVE);
        $pdf->SetFont('helvetica', '', 6.5);
        $pdf->SetTextColor(...self::COLOR_GRAFITO);
        $pdf->MultiCell($ancho, 3.5, mb_strtoupper($etiqueta), 0, 'L', true);

        if ($x !== null) {
            $pdf->SetX($x);
        }
        $pdf->SetFont('helvetica', $negrita ? 'B' : '', 8.5);
        $pdf->SetTextColor(20, 20, 20);
        $pdf->MultiCell($ancho, 5, $valor, 0, 'L', false);
    }

    private function recorta(string $texto, int $max): string
    {
        $texto = trim($texto);
        if ($texto === '') {
            return '';
        }
        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max - 1) . '…' : $texto;
    }

    private function formateaFecha(string $fecha): string
    {
        if ($fecha === '') {
            return '—';
        }
        $ts = strtotime($fecha);
        return $ts ? date('d/m/Y H:i', $ts) : $fecha;
    }

    private function nombreArchivo(string $codigo, string $prefijo): string
    {
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '', $codigo) ?: 'documento';
        return $prefijo . '-' . $safe . '.pdf';
    }
}
