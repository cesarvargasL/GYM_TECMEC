<?php
namespace app\components\secure\modules\AccessControlWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use app\models\Attendance;
use yii\data\Pagination;

class AccessControlContainer extends Widget
{
    public function run()
    {
        $recentAttendances = Attendance::find()
            ->with(['client'])
            ->where(['ES_BORRADO' => 0])
            ->orderBy(['FECHA_DE_INGRESO' => SORT_DESC])
            ->limit(10)
            ->all();

        return $this->render('@app/components/secure/modules/AccessControlWidgetComponent/dumb/AccessControlView', [
            'recentAttendances' => $recentAttendances,
        ]);
    }
}
