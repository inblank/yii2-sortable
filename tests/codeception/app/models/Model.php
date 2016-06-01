<?php

namespace app\models;

use inblank\sortable\SortableBehavior;
use yii\db\ActiveRecord;

/**
 * Test model class
 *
 * @property int $id
 * @property int $sort
 * @property string $name
 *
 * @package app\models
 */
class Model extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%model}}';
    }

    function behaviors()
    {
        return [
            'sortable' =>SortableBehavior::className(),
        ];
    }

    public function rules()
    {
        return [
            ['name', 'required'],
            ['name', 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }
}
