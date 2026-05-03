<?php
namespace app\components\secure\modules\UserManagementWidgetComponent\CreateUser\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use app\shared\AppConst;

class CreateUserContainer extends Widget
{
    public function run()
    {
        $model = new User();

        if ($model->load(Yii::$app->request->post())) {
            
            if (empty($model->CI)) {
                Yii::$app->session->setFlash('error', 'El Carnet de Identidad (CI) es obligatorio.');
            } else {
                $model->ID_BIOMETRICO = (int)$model->CI;
                $model->PASSWORD = Yii::$app->security->generatePasswordHash((string)$model->CI);
                $model->USER_NAME = $model->CI; 

                if ($model->ROL === 'ADMINISTRADOR' || $model->ROL === 'SUPER_ADMIN') {
                    $model->TIPO_CLIENTE = 'EXTERNO';
                    $model->ESTADO = 'ACTIVO';
                } elseif ($model->TIPO_CLIENTE === 'EXTERNO') {
                    $model->ESTADO = 'ACTIVO';
                }

                $fotoBase64 = Yii::$app->request->post('foto_webcam');
                if ($fotoBase64) {
                    $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
                    if (!is_dir($uploadPath)) { @mkdir($uploadPath, 0777, true); }

                    if (preg_match('/^data:image\/(\w+);base64,/', $fotoBase64, $type)) {
                        $data = base64_decode(substr($fotoBase64, strpos($fotoBase64, ',') + 1));
                        $type = strtolower($type[1]);
                        $fileName = $model->CI . '.' . $type;
                        
                        if (file_put_contents($uploadPath . '/' . $fileName, $data) !== false) {
                            $model->AVATAR = '/uploads/avatars/' . $fileName;
                        }
                    }
                }

                if ($model->validate()) {
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Usuario creado correctamente.');
                    
                    Yii::$app->response->redirect(['dashboard/index']);
                    Yii::$app->end(); 
                    return AppConst::EMPTY; 
                }
            } else {
                $errores = implode(", ", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                Yii::$app->session->setFlash('error', 'Error: ' . $errores);
            }
            }
        }

        return $this->render('@app/components/secure/modules/UserManagementWidgetComponent/CreateUser/dumb/CreateUserFormView', [
            'model' => $model,
        ]);
    }
}