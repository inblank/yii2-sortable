<?php
/**
 * Behavior for Yii2 to support sorting in ActiveRecord models
 *
 * @link https://github.com/inblank/yii2-sortable
 * @copyright Copyright (c) 2016 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */
namespace inblank\sortable;

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
            $this->owner->{$this->sortAttribute} = $this->owner->find()->andWhere($this->_buildCondition())->count();
        } else {
            // prevent direct change of sort field
            $this->owner->{$this->sortAttribute} = $this->owner->getOldAttribute($this->sortAttribute);
        }
    }

    /** After update event */
    public function afterUpdate(){
        // TODO check that change conditions and recalculate old range, and move to top in new range
    }

    /** After delete event */
    public function afterDelete()
    {
        $owner = $this->owner;
        $condition = $this->_buildCondition();
        $condition[] = ['>', 'sort', $owner->{$this->sortAttribute}];
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
        $condition = $this->_buildCondition();
        $newSort = $owner->{$this->sortAttribute} + $value;
        if ($value > 0) {
            // move up
            $max = $this->owner->find()->andWhere($condition)->count() - 1;
            if ($owner->{$this->sortAttribute} === $max) {
                return;
            }
            if ($newSort >= $max) {
                $newSort = $max;
            }
            $condition[] = [">", 'sort', $owner->{$this->sortAttribute}];
            $condition[] = ["<=", 'sort', $newSort];
            $counterChanger = -1;
        } else {
            // move down
            if ($owner->{$this->sortAttribute} === 0) {
                return;
            }
            if ($newSort < 0) {
                $newSort = 0;
            }
            $condition[] = ['<', 'sort', $owner->{$this->sortAttribute}];
            $condition[] = ['>=', 'sort', $newSort];
            $counterChanger = 1;
        }
        $owner->updateAllCounters([$this->sortAttribute => $counterChanger], $condition);
        $owner->updateAttributes(['sort' => $newSort]);
    }

    /**
     * Recalculate sorting
     */
    public function recalculateSort()
    {
        $owner = $this->owner;
        $db = $this->owner->getDb();
        $builder = new QueryBuilder($db);

        $orderFields = ['sort' => 'asc'];
        foreach ($owner->primaryKey() as $field) {
            if ($field != 'sort') {
                $orderFields[$field] = 'asc';
            }
        }
        // recalculate sort
        $query = $builder->update(
                $owner->tableName(),
                [$this->sortAttribute => new Expression('(@sortingCount:=(@sortingCount+1))')],
                $this->_buildCondition(),
                $params
            ) . ' ' . $builder->buildOrderBy($orderFields);
        $db->createCommand('set @sortingCount=-1;' . $query, $params)->execute();
        // update in current record
        $owner->{$this->sortAttribute} = $owner->findOne($owner->getPrimaryKey())->{$this->sortAttribute};
    }

    /**
     * Build WHERE condition for sort change query
     * @return array
     */
    protected function _buildCondition()
    {
        $condition = ['and'];
        foreach ((array)$this->conditionAttributes as $attribute) {
            if ($this->owner->hasAttribute($attribute)) {
                $condition[] = [$attribute => $this->owner->$attribute];
            }
        }
        return $condition;
    }

}
