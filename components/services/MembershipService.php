<?php
namespace app\components\services;

use Yii;
use app\models\Membership;
use app\models\Payment;
use app\models\Plan;
use app\models\User;

class MembershipService
{
    public function createMembership(string $clientCi, int $planId, string $adminCi = null): ?Membership
    {
        $plan = Plan::findOne($planId);
        if (!$plan) {
            return null;
        }

        $daysCount = $plan->getDaysCount();
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime("+{$daysCount} days"));

        $membership = new Membership();
        $membership->CI_CLIENTE = $clientCi;
        $membership->ID_PLAN = $planId;
        $membership->FECHA_INICIO = $startDate;
        $membership->FECHA_FIN = $endDate;
        $membership->DIAS_ASIGNADOS = $daysCount;
        $membership->DIAS_DISPONIBLES = $daysCount;

        if (!$membership->save(false)) {
            return null;
        }

        return $membership;
    }

    public function decrementAvailableDays(Membership $membership): bool
    {
        if ($membership->DIAS_DISPONIBLES > 0) {
            $membership->DIAS_DISPONIBLES -= 1;
            return $membership->save(false);
        }
        return false;
    }

    public function getActiveMembershipsForClient(string $clientCi): array
    {
        $today = date('Y-m-d');
        return Membership::find()
            ->where([
                'CI_CLIENTE' => $clientCi,
                'ES_BORRADO' => 0,
            ])
            ->andWhere(['<=', 'FECHA_INICIO', $today])
            ->andWhere(['>=', 'FECHA_FIN', $today])
            ->with(['plan'])
            ->orderBy(['FECHA_INICIO' => SORT_DESC])
            ->all();
    }

    public function getAllAttendanceDatesForClient(string $clientCi, int $membershipCode): array
    {
        $attendances = \app\models\Attendance::find()
            ->where([
                'CI_CLIENTE' => $clientCi,
                'CODIGO_MEMBRESIA' => $membershipCode,
                'ES_BORRADO' => 0,
            ])
            ->orderBy(['FECHA_DE_INGRESO' => SORT_ASC])
            ->all();

        $dates = [];
        foreach ($attendances as $attendance) {
            $dates[] = date('Y-m-d', strtotime($attendance->FECHA_DE_INGRESO));
        }
        return $dates;
    }
}
