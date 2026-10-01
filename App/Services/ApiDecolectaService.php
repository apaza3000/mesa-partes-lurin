<?php

namespace App\Services;

class ApiDecolectaService
{
    private string $token;
    private string $uri;
    private string $accept;


    public function __construct()
    {
        $this->token = env("API_DECOLECTA_TOKEN", "https://api.decolecta.com/v1/");
        $this->uri = env("API_DECOLECTA_SERVER", "sk_19999.DGFTLn6K2ecgD2b42qVE09SklINSGth4");
        $this->accept = "application/json";
    }

    public function getByDNI(string $dni)
    {

        $url = $this->uri . '/reniec/dni?numero=' . urlencode($dni);

        $respuesta = $this->send($url);

        if (!$respuesta['encontrado']) {
            return $respuesta;
        }

        $data = $respuesta['datos'];

        return [
            'encontrado' => true,
            "data" => [
                'tipo_documento' => 'DNI',
                'tipo_persona' => 'Natural',
                'numero_documento' => $data['document_number'] ?? $dni,
                'nombres' => $data['first_name'] ?? '',
                'apellido_paterno' => $data['first_last_name'] ?? '',
                'apellido_materno' => $data['second_last_name'] ?? '',
                'nombre_completo' => $data['full_name'] ?? '',
            ]
        ];
    }
    public function getByRUC(string $ruc)
    {
        $url = $this->uri . '/sunat/ruc?numero=' . urlencode($ruc);

        $respuesta = $this->send($url);

        if (!$respuesta['encontrado']) {
            return $respuesta;
        }

        $data = $respuesta['datos'];

        return [
            'encontrado' => true,
            "data" => [
                'tipo_documento' => 'RUC',
                'tipo_persona' => 'Juridica',

                'numero_documento' => $data['numero_documento'] ?? $ruc,
                'razon_social' => $data['razon_social'] ?? '',
                'estado' => $data['estado'] ?? '',
                'condicion' => $data['condicion'] ?? '',
                'direccion' => $data['direccion'] ?? '',
            ]
        ];
    }

    private function send(string $url): array
    {
        $curl = curl_init($url);

        if ($curl === false) {
            return [
                'encontrado' => false,
                'mensaje' => 'El servicio de consulta no está disponible.'
            ];
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Accept: ' . $this->accept,
                'Authorization: Bearer ' . $this->token,
                'Referer: https://apis.net.pe/'
            ]
        ]);

        $respuesta = curl_exec($curl);
        $estadoHttp = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $errorCurl = curl_errno($curl);
        $mensajeCurl = curl_error($curl);

        curl_close($curl);

        if ($respuesta === false || $errorCurl !== 0) {
            return [
                'encontrado' => false,
                'mensaje' => 'No se pudo conectar con el servicio de consulta.',
                'error' => $mensajeCurl
            ];
        }

        if ($estadoHttp < 200 || $estadoHttp >= 300) {
            return [
                'encontrado' => false,
                'mensaje' => 'No se encontró información para el documento.',
                'estado_http' => $estadoHttp
            ];
        }

        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            return [
                'encontrado' => false,
                'mensaje' => 'La respuesta del servicio no tiene un formato válido.'
            ];
        }

        return [
            'encontrado' => true,
            'datos' => $datos
        ];
    }
}
