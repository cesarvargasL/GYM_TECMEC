<?php
namespace app\models;

use Yii;
use yii\base\Model;

class ChangePasswordForm extends Model
{
    public $oldPassword;
    public $newPassword;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['oldPassword', 'newPassword', 'confirmPassword'], 'required'],
            [['newPassword'], 'string', 'min' => 6],
            ['confirmPassword', 'compare', 'compareAttribute' => 'newPassword', 'message' => 'Las contraseñas no coinciden.'],
            ['oldPassword', 'validateOldPassword']
        ];
    }

    public function validateOldPassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = Yii::$app->user->identity;
            if (!$user || !Yii::$app->security->validatePassword($this->oldPassword, $user->PASSWORD)) {
                $this->addError($attribute, 'La contraseña antigua es incorrecta.');
            }
        }
    }

    public function changePassword(): bool
    {
        if ($this->validate()) {
            $user = Yii::$app->user->identity;
            $user->PASSWORD = Yii::$app->security->generatePasswordHash($this->newPassword);
            return $user->save(false);
        }
        return false;
    }
}