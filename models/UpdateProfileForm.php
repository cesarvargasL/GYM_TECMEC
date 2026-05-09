<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UpdateProfileForm extends Model
{
    public $email;
    public $phone;
    public $avatar;

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
            [['phone'], 'string', 'max' => 20],
            [['avatar'], 'file', 
                'skipOnEmpty' => true, 
                'extensions' => 'png, jpg, jpeg',
                'wrongExtension' => 'Formato inválido. Por favor sube solo imágenes (png, jpg o jpeg).'
            ],
        ];
    }

    public function saveProfile(User $user): bool
    {
        if ($this->validate()) {
            $user->CORREO_ELECTRONICO = $this->email;
            $user->TELEFONO = $this->phone;

            if ($this->avatar) {
                $uploadPath = Yii::getAlias('@webroot/uploads/avatars');
                if (!is_dir($uploadPath)) { 
                    @mkdir($uploadPath, 0777, true); 
                }
                
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