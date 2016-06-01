<?php

namespace app\models;

use inblank\sortable\Sortable;
use yii\db\ActiveRecord;

/**
 * Test model class
 *
 * @property int $id
 * @property int $condition
 * @property int $sort
 * @property string $name
 *
 * @package app\models
 */
class Model2 extends ActiveRecord
{

    public static function tableName()
    {
        return '{{%model2}}';
    }

    function behaviors()
    {
        return [
            'sortable' => [
                'class' => Sortable::className(),
                'conditionAttributes' => 'condition',
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
