<?php
namespace app\components\secure\modules\SettingsWidgetComponent\smart;

use Yii;
use yii\base\Widget;
use app\models\UpdateProfileForm;
use app\models\ChangePasswordForm;
use yii\web\UploadedFile;

class SettingsContainer extends Widget
{
    public function run()
    {
        $user = Yii::$app->user->identity;
        
        $profileForm = new UpdateProfileForm([
            'email' => $user->CORREO_ELECTRONICO,
            'phone' => $user->TELEFONO
        ]);
        
        $passwordForm = new ChangePasswordForm();

        $request = Yii::$app->request;
        $activeTab = 'view-main';

        if ($request->isPost) {
            
            if ($profileForm->load($request->post())) {
                $profileForm->avatar = UploadedFile::getInstance($profileForm, 'avatar');

                if ($profileForm->saveProfile($user)) {
                    Yii::$app->session->setFlash('success', 'Perfil y foto actualizados correctamente.');
                    Yii::$app->response->redirect(['settings/index']);
                    Yii::$app->end();
                    return '';
                } else {
                    $this->setErrorsFlash($profileForm);
                    $activeTab = 'view-profile'; 
                }
            } elseif ($passwordForm->load($request->post())) {
                if ($passwordForm->changePassword()) {
                    Yii::$app->session->setFlash('success', 'Contraseña actualizada con éxito.');
                    Yii::$app->response->redirect(['settings/index']);
                    Yii::$app->end();
                    return '';
                } else {
                    $this->setErrorsFlash($passwordForm);
                    $activeTab = 'view-password'; 
                }
            }
        }

        return $this->render('@app/components/secure/modules/SettingsWidgetComponent/dumb/SettingsView', [
            'user' => $user,
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
            'activeTab' => $activeTab
        ]);
    }

    private function setErrorsFlash($model)
    {
        $errors = implode("<br>", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
        Yii::$app->session->setFlash('error', $errors);
    }
}