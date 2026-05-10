<?php
namespace app\models;

use Yii;
use yii\base\Model;

class AdminUpdateUserForm extends Model
{
    public $ci;
    public $name;
    public $email;
    public $phone;
    public $newPassword;
    public $avatar;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 20],
            [['newPassword'], 'string', 'min' => 6],
            [['avatar'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'wrongExtension' => 'Invalid format. Please upload only images.'],
        ];
    }

    public function loadUserData(User $user)
    {
        $this->ci = $user->CI;
        $this->name = $user->NOMBRE_COMPLETO;
        $this->email = $user->CORREO_ELECTRONICO;
        $this->phone = $user->TELEFONO;
    }

    public function saveUser(User $user): bool
    {
        if ($this->validate()) {
            $user->NOMBRE_COMPLETO = $this->name;
            $user->CORREO_ELECTRONICO = $this->email;
            $user->TELEFONO = $this->phone;

            if (!empty($this->newPassword)) {
                $user->PASSWORD = Yii::$app->security->generatePasswordHash($this->newPassword);
            }

            if ($this->avatar) {
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
                if (!is_dir($uploadPath)) { @mkdir($uploadPath, 0777, true); }
                $fileName = $user->CI . '_' . time() . '.' . $this->avatar->extension;
                
                if ($this->avatar->saveAs($uploadPath . '/' . $fileName)) {
                    $user->AVATAR = '/uploads/avatars/' . $fileName;
                }
            }

            return $user->save(false);
        }
        return false;
    }
}