<?php
namespace app\components\secure\modules\PaymentWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use app\models\Plan;
use app\models\Membership;
use app\models\Payment;
use yii\data\Pagination;

class PaymentContainer extends Widget
{
    public function run()
    {
        $request = Yii::$app->request;
        $search = $request->get('search', '');

        $clientsQuery = User::find()
            ->where(['ROL' => 'CLIENTE', 'ES_BORRADO' => 0]);

        if (!empty($search)) {
            $clientsQuery->andWhere(['or',
                ['like', 'NOMBRE_COMPLETO', $search],
                ['like', 'CI', $search],
            ]);
        }

        $clients = $clientsQuery->limit(50)->all();
        $plans = Plan::find()->where(['ESTADO' => 'ACTIVO'])->all();

        return $this->render('@app/components/secure/modules/PaymentWidgetComponent/dumb/PaymentView', [
            'clients' => $clients,
            'plans' => $plans,
            'search' => $search,
        ]);
    }
}
