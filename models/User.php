<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'USUARIO';
    }

    public function rules()
    {
        return [
            [['CI', 'NOMBRE_COMPLETO', 'CORREO_ELECTRONICO', 'ROL'], 'required'],
            [['TIPO_CLIENTE', 'ESTADO', 'TELEFONO', 'AVATAR', 'USER_NAME', 'PASSWORD'], 'safe'],
            [['CI'], 'unique', 'message' => 'Este CI ya está registrado en el sistema.'],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['CI' => $id, 'ESTADO' => 'ACTIVO']);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['USER_NAME' => $username, 'ESTADO' => 'ACTIVO']);
    }

    public function getId()
    {
        return $this->CI;
    }

    public function getAuthKey()
    {
        return null;
    }

    public function validateAuthKey($authKey)
    {
        return false;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->PASSWORD);
    }
}