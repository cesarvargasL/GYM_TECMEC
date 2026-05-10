<?php
namespace app\components\secure\modules\UserManagementWidgetComponent\UpdateUser\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use app\models\AdminUpdateUserForm;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class UpdateUserContainer extends Widget
{
    public $userId;

    public function run()
    {
        $user = User::findOne(['CI' => $this->userId, 'ES_BORRADO' => 0]);
        if (!$user) {
            throw new NotFoundHttpException('El usuario no existe o fue eliminado.');
        }

        $model = new AdminUpdateUserForm();
        
        $request = Yii::$app->request;

        if ($request->isPost) {
            if ($model->load($request->post())) {
                $model->avatar = UploadedFile::getInstance($model, 'avatar');
                
                if ($model->saveUser($user)) {
                    Yii::$app->session->setFlash('success', 'Usuario actualizado correctamente.');
                    Yii::$app->response->redirect(['user-management/index']);
                    Yii::$app->end();
                    return '';
                } else {
                    $errores = implode("<br>", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                    Yii::$app->session->setFlash('error', $errores);
                }
            }
        } else {
            $model->loadUserData($user);
        }

        return $this->render('@app/components/secure/modules/UserManagementWidgetComponent/UpdateUser/dumb/UpdateUserView', [
            'model' => $model,
            'user' => $user
        ]);
    }
}