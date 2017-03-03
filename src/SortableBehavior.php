<?php
/**
 * Behavior for Yii2 to support sorting in ActiveRecord models
 *
 * @link https://github.com/inblank/yii2-sortable
 * @copyright Copyright (c) 2016 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */
namespace inblank\sortable;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\QueryBuilder;

/**
 * Behavior for Yii2 to support sorting in ActiveRecord models
 *
 * @property ActiveRecord $owner
 */
class SortableBehavior extends Behavior
{
    /**
     * Attribute to store the sort order of records
     * @var string
     */
    public $sortAttribute = 'sort';

    /**
     * The list of attributes used as sorting conditions of the records.
     * null mean no condition and sort all records
     * One attribute can be define as string.
     * @var array|string
     */
    public $conditionAttributes = null;

    /**
     * Maximal sort index
     * @var int
     */
    protected $_max;
    /**
     * Rendered sorting position
     * @var array
     */
    protected $_condition;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /** Before validate event */
    public function beforeValidate()
    {
        if ($this->owner->getIsNewRecord()) {
            // added new record always on top
            $this->owner->{$this->sortAttribute} = $this->owner->find()->andWhere($this->getCondition())->count();
        } else {
            // prevent direct change of sort field
            $this->owner->{$this->sortAttribute} = $this->owner->getOldAttribute($this->sortAttribute);
        }
    }

    /** After update event */
    public function afterUpdate($event)
    {
        $oldCondition = [];
        $currentCondition = [];
        /** @var ActiveRecord $owner */
        $owner = $this->owner;
        foreach ((array)$this->conditionAttributes as $attribute) {
            if (!$owner->hasAttribute($attribute)) {
                continue;
            }
            $oldCondition[$attribute] = $currentCondition[$attribute] = $owner->getAttribute($attribute);
            if (array_key_exists($attribute, $event->changedAttributes)) {
                $oldCondition[$attribute] = $event->changedAttributes[$attribute];
            }
        }
        if (array_diff($currentCondition, $oldCondition) != []) {
            // condition was changed
            /** @var ActiveRecord $changedModel */
            $changedModel = Yii::createObject($owner->className());
            $changedModel->setAttributes($oldCondition, false);
            // recalculate old model range
            $changedModel->recalculateSort();
            // move model to top in new model range
            $owner->updateAttributes([
                $this->sortAttribute => $owner->find()->andWhere($this->getCondition())->count() - 1
            ]);
        }
    }

    /** After delete event */
    public function afterDelete()
    {
        $owner = $this->owner;
        $condition = $this->getCondition();
        $condition[] = ['>', $this->sortAttribute, $owner->{$this->sortAttribute}];
        $owner->updateAllCounters([$this->sortAttribute => -1], $condition);
    }

    /**
     * Move record to the specific sorting position
     * @param int $position new sorting position
     */
    public function moveToPosition($position)
    {
        $this->sortChange($position - $this->owner->{$this->sortAttribute});
    }

    /**
     * Move record to the top of sorting order
     */
    public function moveToTop()
    {
        $this->moveToPosition(PHP_INT_MAX);
    }

    /**
     * Move record to the bottom of sorting order
     */
    public function moveToBottom()
    {
        $this->moveToPosition(0);
    }

    /**
     * Move records
     * @param int $value
     */
    public function sortChange($value)
    {
        if ($value == 0) {
            return;
        }
        $owner = $this->owner;
        $condition = $this->getCondition();
        $newSort = $owner->{$this->sortAttribute} + $value;
        if ($value > 0) {
            // move up
            $max = $this->getMaxSort();
            if ($owner->{$this->sortAttribute} === $max) {
                return;
            }
            if ($newSort >= $max) {
                $newSort = $max;
            }
            $condition[] = [">", $this->sortAttribute, $owner->{$this->sortAttribute}];
            $condition[] = ["<=", $this->sortAttribute, $newSort];
            $counterChanger = -1;
        } else {
            // move down
            if ($owner->{$this->sortAttribute} === 0) {
                return;
            }
            if ($newSort < 0) {
                $newSort = 0;
            }
            $condition[] = ['<', $this->sortAttribute, $owner->{$this->sortAttribute}];
            $condition[] = ['>=', $this->sortAttribute, $newSort];
            $counterChanger = 1;
        }
        $owner->updateAllCounters([$this->sortAttribute => $counterChanger], $condition);
        $owner->updateAttributes([$this->sortAttribute => $newSort]);
    }

    /**
     * Recalculate sorting
     */
    public function recalculateSort()
    {
        $owner = $this->owner;
        $db = $this->owner->getDb();
        $builder = new QueryBuilder($db);

        $orderFields = [$this->sortAttribute => 'asc'];
        foreach ($owner->primaryKey() as $field) {
            if ($field != $this->sortAttribute) {
                $orderFields[$field] = 'asc';
            }
        }
        // recalculate sort
        $query = $builder->update(
            $owner->tableName(),
            [$this->sortAttribute => new Expression('(@sortingCount:=(@sortingCount+1))')],
            $this->getCondition(),
            $params
        ) . ' ' . $builder->buildOrderBy($orderFields);
        $db->createCommand('set @sortingCount=-1;' . $query, $params)->execute();
        // update in current record
        if (!$owner->getIsNewRecord()) {
            $owner->{$this->sortAttribute} = $owner->findOne($owner->getPrimaryKey())->{$this->sortAttribute};
        }
    }

    /**
     * Get maximal sort index
     */
    public function getMaxSort()
    {
        if ($this->_max === null) {
            $this->_max = $this->owner->find()->andWhere($this->getCondition())->count() - 1;
        }
        return $this->_max;
    }

    /**
     * Get WHERE condition for sort change query
     * @return array
     */
    protected function getCondition()
    {
        if ($this->_condition === null) {
            $this->_condition = ['and'];
            foreach ((array)$this->conditionAttributes as $attribute) {
                if ($this->owner->hasAttribute($attribute)) {
                    $this->_condition[] = [$attribute => $this->owner->$attribute];
                }
            }
        }
        return $this->_condition;
    }
}
