<?php
namespace app\components\services;

use Yii;
use app\models\User;

class BiometricService
{
    private string $flaskApiUrl;

    public function __construct()
    {
        $this->flaskApiUrl = Yii::$app->params['flask_api_url'] ?? 'http://localhost:5000';
    }

    public function startEnrollment(string $ci, string $nombre): array
    {
        $url = $this->flaskApiUrl . '/api/registrar';
        $payload = [
            'uid' => (int)$ci,
            'user_id' => (string)$ci,
            'nombre' => $nombre,
        ];

        return $this->sendPostRequest($url, $payload);
    }

    public function checkEnrollmentStatus(): array
    {
        $url = $this->flaskApiUrl . '/api/estado-enrolamiento';
        return $this->sendGetRequest($url);
    }

    public function syncUsers(): array
    {
        $url = $this->flaskApiUrl . '/api/sincronizar';
        return $this->sendPostRequest($url, []);
    }

    public function getStatus(): array
    {
        $url = $this->flaskApiUrl . '/api/status';
        return $this->sendGetRequest($url);
    }

    public function markFingerprintEnrolled(string $ci): bool
    {
        $user = User::findOne(['CI' => $ci]);
        if (!$user) {
            return false;
        }

        $user->HUELLA = 1;
        return $user->save(false);
    }

    private function sendPostRequest(string $url, array $data): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'message' => "cURL Error: {$error}"];
        }

        if ($httpCode !== 200) {
            return ['status' => 'error', 'message' => "HTTP Error {$httpCode}: {$response}"];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Invalid response'];
    }

    private function sendGetRequest(string $url): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'error', 'message' => "cURL Error: {$error}"];
        }

        if ($httpCode !== 200) {
            return ['status' => 'error', 'message' => "HTTP Error {$httpCode}"];
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => 'error', 'message' => 'Invalid response'];
    }
}
