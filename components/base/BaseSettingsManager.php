<?php
namespace ytubes\components\base;
use Yii;
/**
 * Description of BaseSettingsManager
 */
abstract class BaseSettingsManager extends \yii\base\Component
{
    /**
     * @var string module id this settings manager belongs to.
     */
    public $moduleId = null;
    /**
     * @var array|null of loaded settings
     */
    protected $settings = null;
    /**
     * @var string settings model class name
     */
    public $modelClass = 'ytubes\models\Setting';
    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->moduleId === null) {
            throw new \Exception('Could not determine module id');
        }
        $this->loadValues();
        parent::init();
    }

    /**
     * Sets a settings value
     *
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        if ($value === null) {
            return;
        }

        // Update database setting record
        $record = $this->find()
        	->andWhere(['name' => $name])
        	->one();

        if (!$record instanceof $this->modelClass) {
            $record = $this->createRecord();
            $record->name = $name;
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        $record->value = (string) $value;

        if (!$record->save()) {
            throw new \yii\base\Exception("Could not store setting! (" . print_r($record->getErrors(), 1) . ")");
        }

        // Store to runtime
        $this->settings[$name] = $value;
        $this->invalidateCache();
    }

    /**
     * Can be used to set object/arrays as a serialized values.
     *
     *
     * @param string $name
     * @param mixed $value array or object
     */
    public function setJson($name, $value)
    {
        $this->set($name, \yii\helpers\Json::encode($value));
    }

    /**
     * Receives a value which was saved as serialized value.
     *
     * @param string $name
     * @param mixed $default the setting value or null when not exists
     */
    public function getJson($name, $default = null)
    {
        $value = $this->get($name, $default);
        if(is_string($value)) {
            $value = \yii\helpers\Json::decode($value);
        }
        return $value;
    }
    /**
     * Returns value of setting
     *
     * @param string $name the name of setting
     * @return string the setting value or null when not exists
     */
    public function get($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }
    /**
     * Returns the value of setting without any caching
     *
     * @param string $name the name of setting
     * @return string the setting value or null when not exists
     */
    public function getUncached($name, $default = null)
    {
        $record = $this->find()
        	->andWhere(['name' => $name])
        	->asArray()
        	->one();

        return (!$record instanceof $this->modelClass) ? $record['value'] : $default;
    }

    /**
     * Delete one setting row
     *
     * @param string $name
     */
    public function delete($name)
    {
        $record = $this->find()
        	->andWhere(['name' => $name])
        	->one();

        if ($record instanceof $this->modelClass) {
            $record->delete();
        }

        if (isset($this->settings[$name])) {
            unset($this->settings[$name]);
        }

        $this->invalidateCache();
    }

    /**
     * Loads values from database
     */
    protected function loadValues()
    {
        $cached = Yii::$app->cache->get($this->getCacheKey());

        if ($cached === false) {
            $this->settings = [];

            $records = $this->find()
            	->asArray()
            	->all();

            if (!empty($records)) {
                foreach ($records as $record) {
                    $this->settings[$record['name']] = $record['value'];
                }
            }

            Yii::$app->cache->set($this->getCacheKey(), $this->settings);
        } else {
            $this->settings = $cached;
        }
    }

    /**
     * Reloads all values from database
     */
    public function reload()
    {
        $this->invalidateCache();
        $this->loadValues();
    }

    /**
     * Invalidates settings cache
     */
    protected function invalidateCache()
    {
        Yii::$app->cache->delete($this->getCacheKey());
    }

    /**
     * Returns settings managers cache key
     *
     * @return string the cache key
     */
    protected function getCacheKey()
    {
        return 'settings:' . $this->moduleId;
    }

    /**
     * Returns settings active record instance
     */
    protected function createRecord()
    {
        $model = new $this->modelClass;
        $model->module_id = $this->moduleId;
        return $model;
    }

    /**
     * Returns ActiveQuery to find settings
     *
     * @return \yii\db\ActiveQuery
     */
    protected function find()
    {
        $modelClass = $this->modelClass;
        return $modelClass::find()->andWhere(['module_id' => $this->moduleId]);
    }
    /**
     * Deletes all stored settings
     */
    public function deleteAll()
    {
        foreach ($this->find()->all() as $setting) {
            $this->delete($setting->name);
        }
    }

    public function dump()
    {
        echo '<pre>';
        var_dump($this->settings);
        echo '</pre>';
    }
}
