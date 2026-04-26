<?php
namespace app\components\public\modules\AuthWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\shared\AppConst;
class LoginContainer extends Widget
{
    public $model;
    
    public const USER_PASSWORD_INCORRECT = 'Usuario o contraseña incorrectos.';

    public function run()
    {
        if ($this->model->load(Yii::$app->request->post())) {
            if ($this->model->login()) {
                return Yii::$app->controller->redirect(['dashboard/index']);
            } else {
                Yii::$app->session->setFlash('error', self::USER_PASSWORD_INCORRECT);
            }
        }

        $this->model->password = AppConst::EMPTY;

        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $session->removeAllFlashes();
        return $this->render('@app/components/public/modules/AuthWidgetComponent/dumb/LoginView', [
            'model' => $this->model,
            'flashes' => $flashes
        ]);
    }
}