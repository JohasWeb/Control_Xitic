<?php

class OpenAIService
{
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    /**
     * Envía una solicitud a OpenAI.
     * 
     * @param string $systemPrompt Instrucciones para el sistema (personalidad).
     * @param string $userMessage El mensaje o caso del usuario.
     * @param string $apiKey El token de API del cliente.
     * @param string $model El modelo a usar (default: gpt-4o-mini).
     * @return array|false Respuesta decodificada o false si hay error.
     */
    public function analizar($systemPrompt, $userMessage, $apiKey, $model = 'gpt-4o-mini')
    {
        if (empty($apiKey)) {
            return ['error' => 'API Key no configurada'];
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'temperature' => 0.7,
            'max_tokens' => 100000
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Timeout de 30s

        // Desactivar verificación SSL temporalmente si es localhost/dev (opcional, mejor dejar activo en prod)
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'cURL Error: ' . $error];
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            return ['error' => 'OpenAI Error: ' . $decoded['error']['message']];
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            return ['content' => $decoded['choices'][0]['message']['content']];
        }

        return ['error' => 'Respuesta inesperada de OpenAI'];
    }
    /**
     * Analiza una encuesta y devuelve un JSON estructurado.
     */
    public function analizarEncuesta($systemPrompt, $userMessage, $apiKey, $model = 'gpt-4o-mini')
    {
        if (empty($apiKey)) {
            return ['error' => 'API Key no configurada'];
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ];

        // Forzamos la salida JSON en el prompt del sistema también por seguridad
        $systemPrompt .= "\n\nIMPORTANTE: Tu respuesta DEBE ser un JSON válido y nada más.";

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage]
            ],
            'response_format' => ['type' => 'json_object'], // Force JSON mode
            'temperature' => 0.4, // Menor temperatura para mayor consistencia
            'max_tokens' => 2000
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);

        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => 'cURL Error: ' . $error];
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            return ['error' => 'OpenAI Error: ' . $decoded['error']['message']];
        }

        if (isset($decoded['choices'][0]['message']['content'])) {
            $content = $decoded['choices'][0]['message']['content'];
            $json = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return ['data' => $json];
            } else {
                return ['error' => 'La IA no devolvió un JSON válido. Raw: ' . $content];
            }
        }

        return ['error' => 'Respuesta inesperada de OpenAI'];
    }
}
