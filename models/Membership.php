<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use app\shared\enums\ClientStatus;

class Membership extends ActiveRecord
{
    public static function tableName()
    {
        return 'MEMBRESIA';
    }

    public function rules()
    {
        return [
            [['CODIGO_MEMBRESIA', 'CI_CLIENTE', 'ID_PLAN', 'FECHA_INICIO', 'FECHA_FIN', 'DIAS_ASIGNADOS', 'DIAS_DISPONIBLES'], 'required'],
            [['DIAS_ASIGNADOS', 'DIAS_DISPONIBLES'], 'integer'],
            [['FECHA_INICIO', 'FECHA_FIN'], 'date'],
            [['ES_BORRADO'], 'boolean'],
            [['CODIGO_MEMBRESIA'], 'string', 'max' => 50],
            [['CI_CLIENTE'], 'string', 'max' => 20],
            [['CODIGO_MEMBRESIA'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'CODIGO_MEMBRESIA' => 'Codigo Membresia',
            'CI_CLIENTE' => 'CI Cliente',
            'ID_PLAN' => 'Plan',
            'FECHA_INICIO' => 'Fecha Inicio',
            'FECHA_FIN' => 'Fecha Fin',
            'DIAS_ASIGNADOS' => 'Dias Asignados',
            'DIAS_DISPONIBLES' => 'Dias Disponibles',
            'FECHA_REGISTRO' => 'Fecha Registro',
        ];
    }

    public function getClient()
    {
        return $this->hasOne(User::class, ['CI' => 'CI_CLIENTE']);
    }

    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['ID_PLAN' => 'ID_PLAN']);
    }

    public function getPayments()
    {
        return $this->hasMany(Payment::class, ['CODIGO_MEMBRESIA' => 'CODIGO_MEMBRESIA']);
    }

    public function getAttendances()
    {
        return $this->hasMany(Attendance::class, ['CODIGO_MEMBRESIA' => 'CODIGO_MEMBRESIA']);
    }

    public function isActive()
    {
        if ($this->ES_BORRADO) {
            return false;
        }
        $today = date('Y-m-d');
        return $today >= $this->FECHA_INICIO && $today <= $this->FECHA_FIN;
    }

    public function getRemainingDays()
    {
        if (!$this->isActive()) {
            return 0;
        }
        $today = new \DateTime();
        $endDate = new \DateTime($this->FECHA_FIN);
        $diff = $today->diff($endDate);
        return max(0, $diff->days);
    }

    public static function findActiveByClientCi($ci)
    {
        $today = date('Y-m-d');
        return static::find()
            ->where([
                'CI_CLIENTE' => $ci,
                'ES_BORRADO' => 0,
            ])
            ->andWhere(['<=', 'FECHA_INICIO', $today])
            ->andWhere(['>=', 'FECHA_FIN', $today])
            ->orderBy(['FECHA_FIN' => SORT_DESC])
            ->one();
    }
}
