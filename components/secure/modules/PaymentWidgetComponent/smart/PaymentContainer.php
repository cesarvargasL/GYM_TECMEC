<?php
namespace app\components\secure\modules\PaymentWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\Plan;

class PaymentContainer extends Widget
{
    public function run()
    {
        $plans = Plan::find()->where(['ESTADO' => 'ACTIVO'])->all();

        $user = Yii::$app->user->identity;
        $isClient = $user && $user->ROL === 'CLIENTE';

        return $this->render('@app/components/secure/modules/PaymentWidgetComponent/dumb/PaymentView', [
            'plans' => $plans,
            'isClient' => $isClient,
        ]);
    }
}
