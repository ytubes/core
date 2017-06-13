<?php

namespace ytubes\models;

use Yii;

/**
 * This is the model class for table "settings".
 *
 * @property string $module
 * @property string $name
 * @property string $value
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'name'], 'required'],
            [['module_id', 'name'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Module',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }
}
