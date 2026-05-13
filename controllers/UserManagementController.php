<?php
declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use app\shared\enums\Roles;
use yii\web\ErrorAction;

class UserManagementController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            $rol = Yii::$app->user->identity->ROL;
                            return $rol === Roles::SUPER_ADMIN->value || $rol === Roles::ADMINISTRATOR->value;
                        },
                    ],
                ],
            ],
        ];
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
                'view' => '@app/views/modules/auth/error',
            ],
        ];
    }

    public function actionCreate()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/CreateUser/index');
    }

    public function actionIndex()
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/ListUser/index');
    }

    public function actionSoftDelete($id)
    {
        $user = \app\models\User::findOne(['CI' => $id, 'ES_BORRADO' => 0]);
        if ($user) {
            $user->ES_BORRADO = 1;
            $user->save(false);
            Yii::$app->session->setFlash('success', 'Usuario eliminado correctamente.');
        }
        return $this->redirect(['index']);
    }

    public function actionHardDelete($id)
    {
        $user = \app\models\User::findOne(['CI' => $id]);
        if ($user) {
            $user->delete();
            Yii::$app->session->setFlash('success', 'Usuario purgado de la base de datos.');
        }
        return $this->redirect(['index']);
    }

    public function actionUpdate($id)
    {
        $this->layout = 'modules/dashboard/index';
        return $this->render('//modules/UserManagement/UpdateUser/index', [
            'id' => $id,
        ]);
    }

    public function actionCheckCi()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $ci = Yii::$app->request->get('ci', '');
        $exists = \app\models\User::find()->where(['CI' => $ci, 'ES_BORRADO' => 0])->exists();
        return ['exists' => $exists];
    }

    public function actionApiCreate()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $request = Yii::$app->request;
        $ci = $request->post('User')['CI'] ?? $request->post('ci', '');
        $nombre = $request->post('User')['NOMBRE_COMPLETO'] ?? $request->post('nombre', '');
        $telefono = $request->post('User')['TELEFONO'] ?? $request->post('telefono', '');
        $correo = $request->post('User')['CORREO_ELECTRONICO'] ?? $request->post('correo', '');
        $estado = $request->post('User')['ESTADO'] ?? $request->post('estado', 'ACTIVO');
        $tipoCliente = $request->post('User')['TIPO_CLIENTE'] ?? $request->post('tipo_cliente', 'EXTERNO');
        $rol = $request->post('User')['ROL'] ?? $request->post('rol', 'CLIENTE');
        $fotoBase64 = $request->post('foto_webcam', '');

        if (empty($ci)) {
            return ['status' => 'error', 'message' => 'CI requerido'];
        }

        $existing = \app\models\User::find()->where(['CI' => $ci, 'ES_BORRADO' => 0])->one();
        if ($existing) {
            return ['status' => 'error', 'message' => 'El CI ya existe en el sistema'];
        }

        $user = new \app\models\User();
        $user->CI = $ci;
        $user->ID_BIOMETRICO = (int)$ci;
        $user->NOMBRE_COMPLETO = $nombre;
        $user->TELEFONO = $telefono;
        $user->CORREO_ELECTRONICO = $correo;
        $user->ESTADO = $estado;
        $user->TIPO_CLIENTE = $tipoCliente;
        $user->ROL = $rol;
        $user->USER_NAME = $ci;
        $user->PASSWORD = Yii::$app->security->generatePasswordHash((string)$ci);
        $user->ES_BORRADO = 0;

        if ($fotoBase64 && preg_match('/^data:image\/(\w+);base64,/', $fotoBase64, $type)) {
            $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
            if (!is_dir($uploadPath)) { @mkdir($uploadPath, 0777, true); }
            $data = base64_decode(substr($fotoBase64, strpos($fotoBase64, ',') + 1));
            $type = strtolower($type[1]);
            $fileName = $ci . '.' . $type;
            if (file_put_contents($uploadPath . '/' . $fileName, $data) !== false) {
                $user->AVATAR = '/uploads/avatars/' . $fileName;
            }
        }

        if ($user->validate() && $user->save(false)) {
            return ['status' => 'success', 'ci' => $user->CI];
        }

        $errors = $user->getErrors();
        $errorMsg = implode(', ', array_map(function($e) { return implode(', ', $e); }, $errors));
        return ['status' => 'error', 'message' => $errorMsg ?: 'Error al crear usuario'];
    }
}
