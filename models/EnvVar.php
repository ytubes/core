<?php

namespace ytubes\models;

use Yii;

/**
 * This is the model class for table "env_vars".
 *
 * @property string $key_id
 * @property string $value
 */
class EnvVar extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'env_vars';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key_id'], 'required'],
            [['key_id', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key_id' => 'Key ID',
            'value' => 'Value',
        ];
    }

	public static function Get($key, $default = null)
	{
		$key = self::find()
			->where(['key_id' => $key])
			->one();

		return ($key instanceof self) ? $key->value : $default;
	}

    public static function Set($key_id, $value)
    {
    	$sql = "
    		INSERT INTO `" . self::tableName() . "` (`key_id`, `value`) VALUES (:key_id, :value)
  			ON DUPLICATE KEY UPDATE `value`=:value";

  		self::getDb()->createCommand($sql)
  			->bindValues([
  				'key_id' => $key_id,
  				'value' => $value,
  			])
  			->execute();
    }
}
