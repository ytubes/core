<?php

namespace ytubes\models;

use Yii;

/**
 * This is the model class for table "settings".
 *
 * @property string $module_id
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
            [['module_id', 'name', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'module_id' => 'Module ID',
            'name' => 'Name',
            'value' => 'Value',
        ];
    }
}
