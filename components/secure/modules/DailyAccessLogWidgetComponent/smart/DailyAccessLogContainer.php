<?php
namespace app\components\secure\modules\DailyAccessLogWidgetComponent\smart;

use yii\base\Widget;
use yii\data\ArrayDataProvider;
use app\services\AdminAccessLogService;

class DailyAccessLogContainer extends Widget
{
    private AdminAccessLogService $_accessLogService;

    public function init()
    {
        parent::init();
        $this->_accessLogService = new AdminAccessLogService();
    }

    public function run()
    {
        $rawLogs = $this->_accessLogService->getTodayAccessLogs();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $rawLogs,
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('@app/components/secure/modules/DailyAccessLogWidgetComponent/dumb/DailyAccessLogView', [
            'dataProvider' => $dataProvider,
        ]);
    }
}