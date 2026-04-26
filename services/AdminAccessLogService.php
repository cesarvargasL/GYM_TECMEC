<?php
namespace app\services;

use Yii;
use app\models\User;

class AdminAccessLogService
{
    private string $_logFilePath;

    public function __construct()
    {
        $this->_logFilePath = Yii::getAlias('@runtime/admin_access_log.json');
    }

    public function registerAccess(User $user): void
    {
        $logData = $this->_readLogs();
        
        $newRecord = [
            'id' => uniqid(),
            'ci' => $user->CI,
            'name' => $user->NOMBRE_COMPLETO,
            'role' => strtolower($user->ROL),
            'date' => date('Y-m-d'),
            'time' => date('H:i'),
            'registeredCount' => 0
        ];

        array_unshift($logData, $newRecord);
        $logData = array_slice($logData, 0, 100);

        file_put_contents($this->_logFilePath, json_encode($logData, JSON_PRETTY_PRINT));
    }

    public function getTodayAccessLogs(): array
    {
        $logs = $this->_readLogs();
        $today = date('Y-m-d');
        
        return array_filter($logs, function($log) use ($today) {
            return $log['date'] === $today;
        });
    }

    private function _readLogs(): array
    {
        if (!file_exists($this->_logFilePath)) {
            return [];
        }
        $content = file_get_contents($this->_logFilePath);
        return json_decode($content, true) ?: [];
    }
}