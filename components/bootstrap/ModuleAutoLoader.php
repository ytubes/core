<?php
namespace ytubes\components\bootstrap;

use Yii;

/**
 * ModuleAutoLoader automatically register installed modules.
 */
class ModuleAutoLoader implements \yii\base\BootstrapInterface
{
    const CACHE_ID = 'module_configs';

    public function bootstrap($app)
    {
        $modules = Yii::$app->cache->get(self::CACHE_ID);
        if ($modules === false) {
			$all_modules = Yii::$app->getModules();
			$modules = [];

			foreach ($all_modules as $key => $module) {
				if (in_array($key, ['gii', 'debug'])) {
					continue;
				}

	            $moduleDir = Yii::$app->getModule($key)->getBasePath();

	            if (is_dir($moduleDir) && is_file($moduleDir . DIRECTORY_SEPARATOR . 'config.php')) {
	                try {
	                    $modules[$moduleDir] = require($moduleDir . DIRECTORY_SEPARATOR . 'config.php');
	                } catch (\Exception $ex) {
	                }
	            }
			}
            if (!YII_DEBUG) {
                Yii::$app->cache->set(self::CACHE_ID, $modules);
            }
        }

		Yii::$app->moduleManager->registerBulk($modules);
    }
}
