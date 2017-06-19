<?php
namespace ytubes\components\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\Controller;
use yii\web\MethodNotAllowedHttpException;

class QueryParamsFilter extends ActionFilter
{
    private $rules = [];
    public $actions = [];

    /**
     * @var callable a callback that will be called if the access should be denied
     */
    public $denyCallback;

    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request = 'request';

    /**
     * Initializes the [[actions]] array by instantiating rule objects from configurations.
     */
    public function init()
    {
        parent::init();

        $this->request = \yii\di\Instance::ensure($this->request, \yii\web\Request::className());
    }

    public function beforeAction($action)
    {
        if (isset($this->actions[$action->id])) {
            $params = $this->actions[$action->id];
        } elseif (isset($this->actions['*'])) {
            $params = $this->actions['*'];
        } else {
            return true;
        }

		$getParams = $this->request->getQueryParams();
		$redirect = false;

        	// Если нашлись лишние GET переменные, удалим их и произведем редирект на только разрешенные текущие.
        $controlArray = array_diff_key($getParams, array_flip($params));

        if (!empty($controlArray)) {
        	foreach ($controlArray as $key => $value) {
        		unset($getParams[$key]);
        	}

        	$redirect = true;
        }

			// Также редиректим страницу с номером 1 в урле.
		if (isset($getParams['page']) && (int) $getParams['page'] <= 1) {
			unset($getParams['page']);

			$redirect = true;
		}

		if ($redirect === true) {
			$this->request->setQueryParams($getParams);

	        if ($this->denyCallback !== null) {
	            call_user_func($this->denyCallback, null, $action);
	        } else {
	            return $action->controller->redirect(array_merge([$action->id], $this->request->getQueryParams()), 301);
	        }
		}

        return true;
    }
}
