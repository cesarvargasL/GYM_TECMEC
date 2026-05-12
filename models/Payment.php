<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Payment extends ActiveRecord
{
    public static function tableName()
    {
        return 'pago';
    }

    public function rules()
    {
        return [
            [['CODIGO_MEMBRESIA', 'CI_CLIENTE', 'MONTO', 'TIPO_PAGO'], 'required'],
            [['MONTO'], 'number'],
            [['FECHA'], 'safe'],
            [['TIPO_PAGO'], 'in', 'range' => ['QR', 'EFECTIVO']],
            [['CODIGO_PAGO', 'NRO_DOCUMENTO'], 'string', 'max' => 100],
            [['CODIGO_MEMBRESIA', 'CI_CLIENTE', 'CI_ADMINISTRADOR'], 'string', 'max' => 50],
            [['ES_BORRADO'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ID_RECIBO' => 'Numero Recibo',
            'CODIGO_MEMBRESIA' => 'Codigo Membresia',
            'CI_ADMINISTRADOR' => 'CI Administrador',
            'CI_CLIENTE' => 'CI Cliente',
            'FECHA' => 'Fecha',
            'MONTO' => 'Monto',
            'CODIGO_PAGO' => 'Codigo Pago',
            'TIPO_PAGO' => 'Tipo Pago',
            'NRO_DOCUMENTO' => 'Numero Documento',
        ];
    }

    public function getMembership()
    {
        return $this->hasOne(Membership::class, ['CODIGO_MEMBRESIA' => 'CODIGO_MEMBRESIA']);
    }

    public function getAdministrator()
    {
        return $this->hasOne(User::class, ['CI' => 'CI_ADMINISTRADOR']);
    }

    public function getClient()
    {
        return $this->hasOne(User::class, ['CI' => 'CI_CLIENTE']);
    }

    public function isProcessedByAdmin()
    {
        return !empty($this->CI_ADMINISTRADOR);
    }

    public function getTipoPagoLabel()
    {
        return $this->TIPO_PAGO === 'QR' ? 'QR' : 'Efectivo';
    }
}
