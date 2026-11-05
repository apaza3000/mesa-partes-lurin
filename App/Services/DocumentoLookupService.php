<?php

namespace App\Services;

class DocumentoLookupService
{
    /**
     * OPT-04: única firma de consulta externa {encontrado, origen, datos}.
     * El transporte HTTP ya no se duplica aquí: delega en ApiDecolectaService
     * (antes había dos implementaciones paralelas de cURL + parseo).
     */
    public function consultar(string $tipoDocumento, string $numero): array
    {
        $tipoDocumento = strtoupper(trim($tipoDocumento));
        $numero = trim($numero);

        if (!in_array($tipoDocumento, ['DNI', 'RUC'], true)) {
            return ['encontrado' => false, 'mensaje' => 'Seleccione DNI o RUC para realizar la consulta.'];
        }

        $longitud = $tipoDocumento === 'DNI' ? 8 : 11;
        if (!preg_match('/^[0-9]{' . $longitud . '}$/D', $numero)) {
            return ['encontrado' => false, 'mensaje' => 'Ingrese un ' . $tipoDocumento . ' válido.'];
        }

        $token = $this->variable('API_DECOLECTA_TOKEN');
        if ($token === '') {
            return ['encontrado' => false, 'mensaje' => 'La consulta externa no está configurada. Contacte al administrador.'];
        }

        $endpoint = $tipoDocumento === 'DNI' ? '/reniec/dni?numero=' : '/sunat/ruc?numero=';
        $respuesta = (new ApiDecolectaService())->request($endpoint, $numero, $token);

        if (!$respuesta['encontrado']) {
            $http = (int) ($respuesta['http'] ?? 0);
            // Errores de red, autenticación o respuesta corrupta: servicio no disponible.
            if ($http === 0 || $http === 401 || $http === 403 || $http >= 500) {
                return ['encontrado' => false, 'mensaje' => 'El servicio de consulta no está disponible. Intente más tarde.'];
            }
            // 400/404/422 y demás: documento no encontrado.
            return ['encontrado' => false, 'mensaje' => 'No se encontró información para ese documento.'];
        }

        $datos = $respuesta['datos'];

        foreach (['data', 'resultado', 'result'] as $envoltura) {
            if (isset($datos[$envoltura]) && is_array($datos[$envoltura])) {
                $datos = $datos[$envoltura];
                break;
            }
        }

        if (($datos['success'] ?? true) === false || ($datos['status'] ?? true) === false || empty($datos)) {
            return ['encontrado' => false, 'mensaje' => 'No se encontró información para ese documento.'];
        }

        $persona = $tipoDocumento === 'DNI'
            ? [
                'tipo_persona' => 'Natural',
                'nombres' => $this->valor($datos, ['first_name', 'nombres', 'nombre', 'given_names']),
                'apellido_paterno' => $this->valor($datos, ['first_last_name', 'apellidoPaterno', 'apellido_paterno', 'apellido paterno']),
                'apellido_materno' => $this->valor($datos, ['second_last_name', 'apellidoMaterno', 'apellido_materno', 'apellido materno'])
            ]
            : [
                'tipo_persona' => 'Juridica',
                'razon_social' => $this->valor($datos, ['razon_social', 'razonSocial', 'business_name', 'nombre_o_razon_social', 'nombre', 'name']),
                'direccion' => $this->valor($datos, ['direccion', 'direccion_completa', 'domicilio_fiscal', 'address'])
            ];

        $camposEsperados = $tipoDocumento === 'DNI'
            ? ['nombres', 'apellido_paterno', 'apellido_materno']
            : ['razon_social'];
        foreach ($camposEsperados as $campo) {
            if ($persona[$campo] !== '') {
                return ['encontrado' => true, 'origen' => 'decolecta', 'persona' => $persona];
            }
        }

        return ['encontrado' => false, 'mensaje' => 'El servicio no devolvió datos utilizables para ese documento.'];
    }

    private function valor(array $datos, array $claves): string
    {
        foreach ($claves as $clave) {
            if (isset($datos[$clave]) && is_scalar($datos[$clave])) {
                return trim((string) $datos[$clave]);
            }
        }

        return '';
    }

    private function variable(string $nombre, string $predeterminado = ''): string
    {
        $valor = $_ENV[$nombre] ?? $_SERVER[$nombre] ?? getenv($nombre);
        return is_string($valor) ? trim($valor) : $predeterminado;
    }
}
