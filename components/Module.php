<?php
namespace ytubes\components;

use Yii;
use yii\helpers\Json;
/**
 * Base Class for Modules / Extensions
 */
class Module extends \yii\base\Module
{
    /**
     * @var array the loaded module.json info file
     */
    private $_moduleInfo = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // Set settings component
        $this->set('settings', [
            'class' => SettingsManager::className(),
            'moduleId' => $this->id
        ]);
    }

    /**
     * Returns modules name provided by module.json file
     *
     * @return string Name
     */
    public function getName()
    {
        $info = $this->getModuleInfo();

        if ($info['name']) {
            return $info['name'];
        }

        return $this->id;
    }

    /**
     * Returns modules description provided by module.json file
     *
     * @return string Description
     */
    public function getDescription()
    {
        $info = $this->getModuleInfo();

        if ($info['description']) {
            return $info['description'];
        }

        return "";
    }

    /**
     * Returns modules version number provided by module.json file
     *
     * @return string Version Number
     */
    public function getVersion()
    {
        $info = $this->getModuleInfo();

        if ($info['version']) {
            return $info['version'];
        }

        return "1.0";
    }

    /**
     * Reads module.json which contains basic module information and
     * returns it as array
     *
     * @return array module.json content
     */
    protected function getModuleInfo()
    {
        if ($this->_moduleInfo != null) {
            return $this->_moduleInfo;
        }

        $moduleJson = file_get_contents($this->getBasePath() . DIRECTORY_SEPARATOR . 'module.json');

        return Json::decode($moduleJson);
    }
}