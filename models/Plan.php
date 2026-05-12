<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Plan extends ActiveRecord
{
    public static function tableName()
    {
        return 'PLAN';
    }

    public function rules()
    {
        return [
            [['NOMBRE_PLAN', 'TIPO_PLAN', 'TIPO_CLIENTE', 'MONTO'], 'required'],
            [['NOMBRE_PLAN'], 'string', 'max' => 555],
            [['MONTO'], 'number'],
            [['TIPO_PLAN', 'TIPO_CLIENTE', 'ESTADO'], 'string'],
            [['FECHA_VIGENCIA'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ID_PLAN' => 'ID Plan',
            'NOMBRE_PLAN' => 'Nombre del Plan',
            'TIPO_PLAN' => 'Tipo de Plan',
            'TIPO_CLIENTE' => 'Tipo de Cliente',
            'MONTO' => 'Monto',
            'ESTADO' => 'Estado',
            'FECHA_VIGENCIA' => 'Fecha de Vigencia',
        ];
    }

    public function getMemberships()
    {
        return $this->hasMany(Membership::class, ['ID_PLAN' => 'ID_PLAN']);
    }

    public function isActive()
    {
        if ($this->ESTADO !== 'ACTIVO') {
            return false;
        }
        if ($this->FECHA_VIGENCIA === null) {
            return true;
        }
        return $this->FECHA_VIGENCIA >= date('Y-m-d');
    }

    public function getTipoPlanLabel()
    {
        $labels = [
            'MENSUAL' => 'Mensual',
            'MEDIO_MES' => 'Medio Mes',
            'SESSION' => 'Sesion',
        ];
        return $labels[$this->TIPO_PLAN] ?? $this->TIPO_PLAN;
    }

    public function getDaysCount()
    {
        switch ($this->TIPO_PLAN) {
            case 'MENSUAL':
                return 30;
            case 'MEDIO_MES':
                return 15;
            case 'SESSION':
                return 1;
            default:
                return 0;
        }
    }
}
