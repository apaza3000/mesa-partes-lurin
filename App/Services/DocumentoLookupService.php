<?php

namespace App\Services;

class DocumentoLookupService
{

    public function getByDni(string $dni) {


    
    }
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

        $token = $this->variable('PERSONA_LOOKUP_TOKEN');
        if ($token === '') {
            return ['encontrado' => false, 'mensaje' => 'La consulta externa no está configurada. Contacte al administrador.'];
        }

        $urlVariable = $tipoDocumento === 'DNI' ? 'PERSONA_LOOKUP_DNI_URL' : 'PERSONA_LOOKUP_RUC_URL';
        $urlPredeterminada = $tipoDocumento === 'DNI'
            ? 'https://api.decolecta.com/v1/reniec/dni?numero={numero}'
            : 'https://api.decolecta.com/v1/sunat/ruc?numero={numero}';
        $urlConfigurada = $this->variable($urlVariable);
        $url = str_replace('{numero}', rawurlencode($numero), $urlConfigurada ?: $urlPredeterminada);

        $curl = curl_init($url);
        if ($curl === false) {
            return ['encontrado' => false, 'mensaje' => 'El servicio de consulta no está disponible. Intente más tarde.'];
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $token,
                'Referer: https://apis.net.pe/'
            ]
        ]);

        $respuesta = curl_exec($curl);
        $estadoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errorCurl = curl_errno($curl) !== 0;
        curl_close($curl);

        if ($errorCurl || !is_string($respuesta)) {
            return ['encontrado' => false, 'mensaje' => 'El servicio de consulta no está disponible. Intente más tarde.'];
        }

        if ($estadoHttp === 404 || $estadoHttp === 400 || $estadoHttp === 422) {
            return ['encontrado' => false, 'mensaje' => 'No se encontró información para ese documento.'];
        }

        if ($estadoHttp === 401 || $estadoHttp === 403 || $estadoHttp < 200 || $estadoHttp >= 300) {
            return ['encontrado' => false, 'mensaje' => 'El servicio de consulta no está disponible. Intente más tarde.'];
        }

        $datos = json_decode($respuesta, true);
        if (!is_array($datos)) {
            return ['encontrado' => false, 'mensaje' => 'El servicio de consulta devolvió una respuesta inválida.'];
        }

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
                return ['encontrado' => true, 'persona' => $persona];
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
