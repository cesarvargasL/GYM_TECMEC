<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\shared\enums\ClientStatus;

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
            [['TIPO_CLIENTE', 'ESTADO', 'TELEFONO', 'AVATAR', 'USER_NAME', 'PASSWORD', 'HUELLA', 'ES_BORRADO'], 'safe'],
            [['CI'], 'unique', 'message' => 'Este CI ya esta registrado en el sistema.'],
            [['CORREO_ELECTRONICO'], 'email', 'message' => 'Por favor, ingrese un correo valido.'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'CI' => 'Carnet de Identidad',
            'ID_BIOMETRICO' => 'ID Biometrico',
            'NOMBRE_COMPLETO' => 'Nombre Completo',
            'TELEFONO' => 'Telefono',
            'AVATAR' => 'Avatar',
            'CORREO_ELECTRONICO' => 'Correo Electronico',
            'ESTADO' => 'Estado',
            'TIPO_CLIENTE' => 'Tipo Cliente',
            'ROL' => 'Rol',
            'USER_NAME' => 'Nombre de Usuario',
            'PASSWORD' => 'Contrasena',
            'HUELLA' => 'Huella',
            'ES_BORRADO' => 'Eliminado',
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne(['CI' => $id, 'ESTADO' => ClientStatus::ACTIVE->value, 'ES_BORRADO' => 0]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['USER_NAME' => $username, 'ESTADO' => ClientStatus::ACTIVE->value, 'ES_BORRADO' => 0]);
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

    public function getMemberships()
    {
        return $this->hasMany(Membership::class, ['CI_CLIENTE' => 'CI']);
    }

    public function getActiveMembership()
    {
        return $this->hasOne(Membership::class, ['CI_CLIENTE' => 'CI'])
            ->where(['ES_BORRADO' => 0])
            ->andWhere(['<=', 'FECHA_INICIO', date('Y-m-d')])
            ->andWhere(['>=', 'FECHA_FIN', date('Y-m-d')]);
    }

    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['CI_CLIENTE' => 'CI']);
    }

    public function getProcessedPayments()
    {
        return $this->hasMany(Payment::class, ['CI_ADMINISTRADOR' => 'CI']);
    }

    public function getAttendances()
    {
        return $this->hasMany(Attendance::class, ['CI_CLIENTE' => 'CI']);
    }

    public function isAdmin()
    {
        return in_array($this->ROL, ['ADMINISTRADOR', 'SUPER_ADMIN'], true);
    }

    public function isClient()
    {
        return $this->ROL === 'CLIENTE';
    }
}
