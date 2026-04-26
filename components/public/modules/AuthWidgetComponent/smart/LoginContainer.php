<?php
namespace app\components\public\modules\AuthWidgetComponent\smart;

use yii\base\Widget;

class LoginContainer extends Widget
{
    public $model; 

    public function run()
    {
        $this->model->password = '';
        return $this->render('@app/components/public/modules/AuthWidgetComponent/dumb/LoginView', [
            'model' => $this->model,
        ]);
    }
}