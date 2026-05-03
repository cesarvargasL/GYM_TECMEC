<?php
namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private ?User $_user = null;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute);
            }
        }
    }

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if ($user !== null) {
                return Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
            }
        }
        return false;
    }

    public function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }
        return $this->_user;
    }
}