<?php
namespace app\components\secure\modules\UserManagementWidgetComponent\CreateUser\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use app\models\Plan;
use app\shared\AppConst;
use app\shared\enums\Roles;
use app\shared\enums\ClientType;
use app\shared\enums\ClientStatus;

class CreateUserContainer extends Widget
{
    public bool $isPublicContext = false;

    public function run()
    {
        $model = new User();
        $plans = Plan::find()->where(['ESTADO' => 'ACTIVO'])->all();

        $currentUserRole = $this->isPublicContext ? null : Yii::$app->user->identity->ROL;

        if ($model->load(Yii::$app->request->post())) {
            
            if ($this->isPublicContext) {
                $model->ROL = Roles::CLIENT->value; 
            } else {
                if ($currentUserRole === Roles::ADMINISTRATOR->value && $model->ROL !== Roles::CLIENT->value) {
                    Yii::$app->session->setFlash('error', 'Violacion de Seguridad: No tienes permisos para crear este tipo de usuario.');
                    Yii::$app->response->redirect(['user-management/create']);
                    Yii::$app->end();
                    return AppConst::EMPTY;
                }
            }
            
            if (empty($model->CI)) {
                Yii::$app->session->setFlash('error', 'El Carnet de Identidad (CI) es obligatorio.');
            } else {
                $model->ID_BIOMETRICO = (int)$model->CI;
                $model->PASSWORD = Yii::$app->security->generatePasswordHash((string)$model->CI);
                $model->USER_NAME = $model->CI; 

                if ($model->ROL === Roles::ADMINISTRATOR->value || $model->ROL === Roles::SUPER_ADMIN->value) {
                    $model->TIPO_CLIENTE = ClientType::EXTERNAL->value;
                    $model->ESTADO = ClientStatus::ACTIVE->value;
                } elseif ($model->TIPO_CLIENTE === ClientType::EXTERNAL->value) {
                    $model->ESTADO = ClientStatus::ACTIVE->value;
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
                        
                        if ($this->isPublicContext) {
                            Yii::$app->session->setFlash('success', 'Registro exitoso. Por favor, inicia sesion.');
                            Yii::$app->response->redirect(['login/login']);
                        } else {
                            Yii::$app->session->setFlash('success', 'Usuario creado correctamente.');
                            Yii::$app->response->redirect(['dashboard/index']);
                        }
                        
                        Yii::$app->end(); 
                        return AppConst::EMPTY; 
                    }
                } else {
                    $errores = implode(", ", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                    Yii::$app->session->setFlash('error', 'Error: ' . $errores);
                }
            }
        }

        $availableRoles = [];
        if (!$this->isPublicContext) {
            if ($currentUserRole === Roles::SUPER_ADMIN->value) {
                $availableRoles = [
                    Roles::CLIENT->value => 'Cliente',
                    Roles::ADMINISTRATOR->value => 'Administrador',
                    Roles::SUPER_ADMIN->value => 'Super Admin'
                ];
            } else {
                $availableRoles = [ Roles::CLIENT->value => 'Cliente' ];
            }
        }

        return $this->render('@app/components/secure/modules/UserManagementWidgetComponent/CreateUser/dumb/CreateUserFormView', [
            'model' => $model,
            'availableRoles' => $availableRoles,
            'isPublicContext' => $this->isPublicContext,
            'plans' => $plans,
        ]);
    }
}
