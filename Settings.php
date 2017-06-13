<?php
namespace ytubes;

use Yii;

use ytubes\models\Setting;

/*
/* The base class that you use to retrieve the settings from the database
*/
class Settings implements \yii\base\BootstrapInterface
{

    private static $params = [];

    public function __construct() {
        //$this->db = Yii::$app->db;
    }

    /**
    * Bootstrap method to be called during application bootstrap stage.
    * Loads all the settings into the self::$params array
    * @param Application $app the application currently running
    */
    public function bootstrap($app) {

        // Get settings from database
        $settings = Setting::find()
        	->all();

        // Now let's load the settings into the global params array
        foreach ($settings as $val) {
           	self::$params[$val['module_id']][$val['name']] = $val['value'];
        }

    }

    public static function Get($name, $moduleId = 'base')
    {
    	return self::$params[$moduleId][$name];
    }

    public static function Set($name, $value, $moduleId = 'base')
    {
        $param = Setting::find()
        	->where(['module_id' => $moduleId, 'name' => $name])
        	->one();

        if (!$param instanceof Setting) {
        	$param = new Setting();
        }

        $param->module = $moduleId;
        $param->name = $name;
        $param->value = $value;

        if ($param->save(true)) {
        	self::$params[$moduleId][$name] = $value;
        }
    }
}
