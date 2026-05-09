<?php
namespace app\components\secure\modules\PlanWidgetComponent\ListPlan\smart;

use Yii;
use yii\base\Widget;
use app\models\Plan;
use app\shared\enums\ClientStatus;
use app\shared\enums\Roles;

class ListPlanContainer extends Widget
{
    public function run()
    {
        $planes = Plan::find()->all();
        $hoy = date('Y-m-d');

        foreach ($planes as $plan) {
            if ($plan->FECHA_VIGENCIA !== null && $plan->FECHA_VIGENCIA < $hoy && $plan->ESTADO === ClientStatus::ACTIVE->value) {
                $plan->ESTADO = ClientStatus::INACTIVE->value;
                $plan->save(false);
            }
        }

        $isSuperAdmin = Yii::$app->user->identity->ROL === Roles::SUPER_ADMIN->value;

        return $this->render('@app/components/secure/modules/PlanWidgetComponent/ListPlan/dumb/ListPlanView', [
            'planes' => $planes,
            'isSuperAdmin' => $isSuperAdmin
        ]);
    }
}