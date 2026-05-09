<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Plan extends ActiveRecord
{
    public static function tableName()
    {
        return 'plan';
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
}