<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Attendance extends ActiveRecord
{
    public static function tableName()
    {
        return 'ASISTENCIA';
    }

    public function rules()
    {
        return [
            [['CI_CLIENTE', 'ID_BIOMETRICO', 'CODIGO_MEMBRESIA'], 'required'],
            [['ID_BIOMETRICO'], 'integer'],
            [['FECHA_DE_INGRESO'], 'safe'],
            [['CI_CLIENTE', 'CODIGO_MEMBRESIA'], 'string', 'max' => 50],
            [['ES_BORRADO'], 'boolean'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'ID_ASISTENCIA' => 'ID Asistencia',
            'CI_CLIENTE' => 'CI Cliente',
            'ID_BIOMETRICO' => 'ID Biometrico',
            'CODIGO_MEMBRESIA' => 'Codigo Membresia',
            'FECHA_DE_INGRESO' => 'Fecha de Ingreso',
        ];
    }

    public function getClient()
    {
        return $this->hasOne(User::class, ['CI' => 'CI_CLIENTE']);
    }

    public function getMembership()
    {
        return $this->hasOne(Membership::class, ['CODIGO_MEMBRESIA' => 'CODIGO_MEMBRESIA']);
    }
}
