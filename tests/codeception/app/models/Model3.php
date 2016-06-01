<?php

namespace app\models;

use inblank\sortable\SortableBehavior;
use yii\db\ActiveRecord;

/**
 * Test model class
 *
 * @property int $id
 * @property int $condition
 * @property string $condition2
 * @property int $sort
 * @property string $name
 *
 * @package app\models
 */
class Model3 extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%model3}}';
    }

    function behaviors()
    {
        return [
            'sortable' => [
                'class' => SortableBehavior::className(),
                'conditionAttributes' => ['condition', 'condition2'],
            ],
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
