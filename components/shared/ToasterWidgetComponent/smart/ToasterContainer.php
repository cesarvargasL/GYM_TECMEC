<?php
namespace app\components\shared\ToasterWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\shared\AppConst;

class ToasterContainer extends Widget
{
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        
        if (empty($flashes)) {
            return AppConst::EMPTY;
        }

        $session->removeAllFlashes();

        return $this->render('@app/components/shared/ToasterWidgetComponent/dumb/ToasterView', [
            'flashes' => $flashes,
        ]);
    }
}