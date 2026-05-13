<?php
namespace app\components\secure\modules\HistoryWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\Attendance;
use yii\data\Pagination;

class HistoryContainer extends Widget
{
    public function run()
    {
        $request = Yii::$app->request;
        $today = date('Y-m-d');
        
        $dateFrom = $request->get('date_from', $today);
        $dateTo = $request->get('date_to', $today);
        $search = $request->get('search', '');

        $query = Attendance::find()
            ->alias('a')
            ->joinWith(['client c', 'membership m', 'membership.plan p', 'membership.payments pay'])
            ->where(['a.ES_BORRADO' => 0])
            ->andWhere(['between', 'a.FECHA_DE_INGRESO', $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        if (!empty($search)) {
            $query->andWhere(['or',
                ['like', 'c.NOMBRE_COMPLETO', $search],
                ['like', 'a.CI_CLIENTE', $search],
            ]);
        }

        $query->orderBy(['a.FECHA_DE_INGRESO' => SORT_DESC]);

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(),
            'pageSize' => 20,
            'params' => array_merge($_GET),
        ]);

        $attendances = $query
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return $this->render('@app/components/secure/modules/HistoryWidgetComponent/dumb/HistoryView', [
            'attendances' => $attendances,
            'pages' => $pages,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'search' => $search,
        ]);
    }
}
