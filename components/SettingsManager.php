<?php
namespace ytubes\components;

use Yii;
use ytubes\components\base\BaseSettingsManager;

class SettingsManager extends BaseSettingsManager
{

	public function getModuleId()
	{
		return $this->moduleId;
	}

}