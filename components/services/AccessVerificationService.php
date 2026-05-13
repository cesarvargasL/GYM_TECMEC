<?php

namespace app\components\services;

use Yii;
use app\models\User;
use app\models\Membership;
use app\models\Attendance;

class AccessVerificationService
{
    private MembershipService $membershipService;

    public function __construct()
    {
        $this->membershipService = new MembershipService();
    }

    public function verifyAccess(string $ci, int $idBiometrico): array
    {
        $user = User::findOne(['CI' => $ci, 'ES_BORRADO' => 0]);
        if (!$user) {
            return [
                'status' => 'denied',
                'reason' => 'Usuario no registrado',
                'user' => null,
            ];
        }

        $activeMembership = Membership::findActiveByClientCi($ci);
        if (!$activeMembership) {
            return [
                'status' => 'denied',
                'reason' => 'Sin membresia activa',
                'user' => $user,
            ];
        }

        $attendance = new Attendance();
        $attendance->CI_CLIENTE = $ci;
        $attendance->ID_BIOMETRICO = $idBiometrico > 0 ? $idBiometrico : (int)($user->ID_BIOMETRICO ?? 0);
        $attendance->CODIGO_MEMBRESIA = (int)$activeMembership->CODIGO_MEMBRESIA;
        $attendance->FECHA_DE_INGRESO = date('Y-m-d H:i:s');

        if (!$attendance->save(false)) {
            return [
                'status' => 'error',
                'reason' => 'Error al registrar asistencia',
                'user' => $user,
            ];
        }

        $this->membershipService->decrementAvailableDays($activeMembership);

        return [
            'status' => 'granted',
            'reason' => 'Acceso permitido',
            'user' => $user,
            'membership' => $activeMembership,
            'remainingDays' => $activeMembership->getRemainingDays(),
        ];
    }

    public function verifyAccessManual(string $ci): array
    {
        return $this->verifyAccess($ci, 0);
    }
}
