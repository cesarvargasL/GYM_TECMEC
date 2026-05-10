<?php
namespace app\components\secure\modules\UserManagementWidgetComponent\ListUser\smart;

use Yii;
use yii\base\Widget;
use app\models\User;
use yii\data\Pagination;

class ListUserContainer extends Widget
{
    public function run()
    {
        $request = Yii::$app->request;
        
        $query = User::find()->where(['ES_BORRADO' => 0]);

        $search = $request->get('search');
        $role = $request->get('role');
        $huella = $request->get('huella');

        if (!empty($search)) {
            $query->andWhere(['or', 
                ['like', 'NOMBRE_COMPLETO', $search], 
                ['like', 'CI', $search],
                ['like', 'CORREO_ELECTRONICO', $search]
            ]);
        }
        
        if (!empty($role)) {
            $query->andWhere(['ROL' => $role]);
        }
        
        if ($huella !== null && $huella !== '') {
            $query->andWhere(['HUELLA' => (int)$huella]);
        }

        $countQuery = clone $query;
        $pages = new Pagination([
            'totalCount' => $countQuery->count(), 
            'pageSize' => 20,
            'params' => array_merge($_GET) 
        ]);

        $users = $query->offset($pages->offset)
                       ->limit($pages->limit)
                       ->all();

        return $this->render('@app/components/secure/modules/UserManagementWidgetComponent/ListUser/dumb/ListUserView', [
            'users' => $users,
            'pages' => $pages,
            'search' => $search,
            'role' => $role,
            'huella' => $huella
        ]);
    }
}