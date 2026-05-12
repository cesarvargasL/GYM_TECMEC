<?php
namespace app\models;

use Yii;
use yii\base\Model;

class MembershipRegistrationForm extends Model
{
    public $clientCi;
    public $planId;
    public $paymentMethod;

    public function rules()
    {
        return [
            [['clientCi', 'planId', 'paymentMethod'], 'required'],
            [['planId'], 'integer'],
            [['paymentMethod'], 'in', 'range' => ['QR', 'EFECTIVO']],
            [['clientCi'], 'string', 'max' => 20],
            [['clientCi'], 'validateClientExists'],
            [['planId'], 'validatePlanExists'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'clientCi' => 'CI del Cliente',
            'planId' => 'Plan',
            'paymentMethod' => 'Metodo de Pago',
        ];
    }

    public function validateClientExists($attribute)
    {
        if (!$this->hasErrors()) {
            $user = User::findOne(['CI' => $this->clientCi, 'ES_BORRADO' => 0]);
            if (!$user) {
                $this->addError($attribute, 'El cliente no existe en el sistema.');
            } elseif ($user->ROL !== 'CLIENTE') {
                $this->addError($attribute, 'El usuario seleccionado no es un cliente.');
            }
        }
    }

    public function validatePlanExists($attribute)
    {
        if (!$this->hasErrors()) {
            $plan = Plan::findOne($this->planId);
            if (!$plan) {
                $this->addError($attribute, 'El plan seleccionado no existe.');
            } elseif (!$plan->isActive()) {
                $this->addError($attribute, 'El plan seleccionado no esta activo.');
            }
        }
    }

    public function register(): ?Membership
    {
        if (!$this->validate()) {
            return null;
        }

        $membershipService = new \app\components\services\MembershipService();
        $adminCi = Yii::$app->user->identity->isAdmin() ? Yii::$app->user->identity->CI : null;

        $membership = $membershipService->createMembership($this->clientCi, (int)$this->planId, $adminCi);
        if (!$membership) {
            return null;
        }

        $paymentGateway = new \app\components\services\PaymentGatewayService();

        if ($this->paymentMethod === 'QR') {
            $payment = $paymentGateway->processQRPayment($membership, $adminCi);
        } else {
            $payment = $paymentGateway->processCashPayment($membership, $adminCi);
        }

        if (!$payment) {
            return null;
        }

        return $membership;
    }
}
