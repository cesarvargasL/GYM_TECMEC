<?php
namespace app\components\secure\modules\MembershipWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\Membership;
use yii\data\Pagination;

class MembershipContainer extends Widget
{
    public function run()
    {
        $request = Yii::$app->request;
        $search = $request->get('search', '');
        $today = date('Y-m-d');

        $query = Membership::find()
            ->alias('m')
            ->joinWith(['client c', 'plan p'])
            ->where(['m.ES_BORRADO' => 0])
            ->andWhere(['<=', 'm.FECHA_INICIO', $today])
            ->andWhere(['>=', 'm.FECHA_FIN', $today]);

        if (!empty($search)) {
            $query->andWhere(['or',
                ['like', 'c.NOMBRE_COMPLETO', $search],
                ['like', 'm.CI_CLIENTE', $search],
            ]);
        }

        $query->orderBy(['m.FECHA_REGISTRO' => SORT_DESC]);

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 20,
            'params' => array_merge($_GET),
        ]);

        $memberships = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('@app/components/secure/modules/MembershipWidgetComponent/dumb/MembershipView', [
            'memberships' => $memberships,
            'pages' => $pages,
            'search' => $search,
        ]);
    }
}
