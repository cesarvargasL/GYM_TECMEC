<?php
namespace app\components\secure\modules\PlanWidgetComponent\PlanForm\smart;

use Yii;
use yii\base\Widget;
use app\models\Plan;
use app\shared\enums\ClientStatus;

class PlanFormContainer extends Widget
{
    public $id = null;

    public function run()
    {
        if ($this->id) {
            $model = Plan::findOne($this->id);
            if (!$model) {
                Yii::$app->session->setFlash('error', 'Plan no encontrado.');
                return Yii::$app->response->redirect(['plan/index']);
            }
        } else {
            $model = new Plan();
            $model->ESTADO = ClientStatus::ACTIVE->value;
        }

        if ($model->load(Yii::$app->request->post())) {
            
            $isLifetime = Yii::$app->request->post('is_lifetime') === '1';
            
            if ($isLifetime) {
                $model->FECHA_VIGENCIA = null;
            }

            if ($model->validate() && $model->save()) {
                Yii::$app->session->setFlash('success', $this->id ? 'Plan actualizado correctamente.' : 'Plan creado exitosamente.');
                Yii::$app->response->redirect(['plan/index']);
                Yii::$app->end();
                return '';
            } else {
                $errores = implode(", ", \yii\helpers\ArrayHelper::getColumn($model->getErrors(), 0));
                Yii::$app->session->setFlash('error', 'Error: ' . $errores);
            }
        }

        return $this->render('@app/components/secure/modules/PlanWidgetComponent/PlanForm/dumb/PlanFormView', [
            'model' => $model,
        ]);
    }
}