<?php

namespace inblank\sortable\tests;

use app\models\Model;
use app\models\Model2;
use app\models\Model3;
use Codeception\Specify;
use yii;
use yii\codeception\TestCase;

class GeneralTest extends TestCase
{
    use Specify;

    /**
     * Check sort sequence
     * @param Model|Model2|Model3 $model
     * @return bool
     */
    public function checkSequence($model)
    {
        $sort = 0;
        $conditionAttributes = [];
        foreach ((array)$model->getBehavior('sortable')->conditionAttributes as $attribute) {
            if ($model->hasAttribute($attribute)) {
                $conditionAttributes[] = $attribute;
            }
        }
        $order = implode(' asc, ', $conditionAttributes);
        if (!empty($order)) {
            $order .= ' asc, ';
        }
        $order .= 'sort asc';
        $previousCondition = array_fill_keys($conditionAttributes, null);
        /** @var Model|Model2|Model3 $model */
        foreach ($model->find()->orderBy($order)->all() as $model) {
            $currentCondition = $model->getAttributes($conditionAttributes);
            if (array_diff_assoc($previousCondition, $currentCondition) != []) {
                $sort = 0;
                $previousCondition = $currentCondition;
            }
            if ($sort !== $model->sort) {
                return false;
            }
            $sort++;
        }
        return true;
    }

    public function testInsert()
    {
        $model = new Model(['name' => 'Testing']);
        $this->specify("we create and save model with auto generated `sort` position", function () use ($model) {
            expect("the model should be saved", $model->save())->true();
            $sortValue = 15;
            expect("the current model `sort` must be equal", $model->sort)->equals($sortValue);
            $dbModel = Model::findOne($model->id);
            expect("the model in base `sort` must be equal", $dbModel->sort)->equals($sortValue);
        });
        $model2 = new Model2(['name' => 'Testing', 'condition' => 1]);
        $this->specify("we create and save model2 with auto generated `sort` position", function () use ($model2) {
            expect("the model should be saved", $model2->save())->true();
            $sortValue = 5;
            expect("the current model `sort` must be equal", $model2->sort)->equals($sortValue);
            $dbModel = Model2::findOne($model2->id);
            expect("the model in base `sort` must be equal", $dbModel->sort)->equals($sortValue);
        });
        $model3 = new Model3(['name' => 'Testing', 'condition' => 2, 'condition2' => 'a']);
        $this->specify("we create and save model3 with auto generated `sort` position", function () use ($model3) {
            expect("the model should be saved", $model3->save())->true();
            $sortValue = 4;
            expect("the current model `sort` must be equal", $model3->sort)->equals($sortValue);
            $dbModel = Model3::findOne($model3->id);
            expect("the model in base `sort` must be equal", $dbModel->sort)->equals($sortValue);
        });
    }

    public function testMoveToTop()
    {
        $model = Model::findOne(5);
        $this->specify("we move model on top", function () use ($model) {
            $model->moveToTop();
            expect("the model should be on top", $model->sort)->equals(14);
            expect("the sort sequence should not be broken", $this->checkSequence($model))->true();
        });
        $model2 = Model2::findOne(7);
        $this->specify("we move model2 on top", function () use ($model2) {
            $model2->moveToTop();
            expect("the model should be on top", $model2->sort)->equals(8);
            expect("the sort sequence should not be broken", $this->checkSequence($model2))->true();
        });
        $model3 = Model3::findOne(7);
        $this->specify("we move model3 on top", function () use ($model3) {
            $model3->moveToTop();
            expect("the model should be on top", $model3->sort)->equals(3);
            expect("the sort sequence should not be broken", $this->checkSequence($model3))->true();
        });
    }

    public function testMoveToBottom()
    {
        $model = Model::findOne(5);
        $this->specify("we move model on bottom", function () use ($model) {
            $model->moveToBottom();
            expect("the model should be on bottom", $model->sort)->equals(0);
            expect("the sort sequence should not be broken", $this->checkSequence($model))->true();
        });
        $model2 = Model2::findOne(7);
        $this->specify("we move model2 on bottom", function () use ($model2) {
            $model2->moveToBottom();
            expect("the model should be on bottom", $model2->sort)->equals(0);
            expect("the sort sequence should not be broken", $this->checkSequence($model2))->true();
        });
        $model3 = Model3::findOne(7);
        $this->specify("we move model3 on bottom", function () use ($model3) {
            $model3->moveToBottom();
            expect("the model should be on bottom", $model3->sort)->equals(0);
            expect("the sort sequence should not be broken", $this->checkSequence($model3))->true();
        });
    }

    public function testMoveToPosition()
    {
        $model = Model::findOne(5);
        $this->specify("we move model to position", function () use ($model) {
            $model->moveToPosition(1);
            expect("the model should be in position", $model->sort)->equals(1);
            expect("the sort sequence should not be broken", $this->checkSequence($model))->true();
        });
        $model2 = Model2::findOne(7);
        $this->specify("we move model2 to position", function () use ($model2) {
            $model2->moveToPosition(3);
            expect("the model should be in position", $model2->sort)->equals(3);
            expect("the sort sequence should not be broken", $this->checkSequence($model2))->true();
        });
        $model3 = Model3::findOne(9);
        $this->specify("we move model3 to position", function () use ($model3) {
            $model3->moveToPosition(1);
            expect("the model should be in position", $model3->sort)->equals(1);
            expect("the sort sequence should not be broken", $this->checkSequence($model3))->true();
        });
    }

    public function testSortChange()
    {
        $model = Model::findOne(5);
        $this->specify("we move model down on 1 position", function () use ($model) {
            $model->sortChange(-1);
            expect("the model should be move down", $model->sort)->equals(3);
            expect("the sort sequence should not be broken", $this->checkSequence($model))->true();
        });
        $this->specify("we move model up on 2 position", function () use ($model) {
            $model->sortChange(2);
            expect("the model should be move up", $model->sort)->equals(5);
            expect("the sort sequence should not be broken", $this->checkSequence($model))->true();
        });
        $model2 = Model2::findOne(11);
        $this->specify("we move model2 down on 1 position", function () use ($model2) {
            $model2->sortChange(-1);
            expect("the model2 should be move down", $model2->sort)->equals(4);
            expect("the sort sequence should not be broken", $this->checkSequence($model2))->true();
        });
        $this->specify("we move model2 up on 2 position", function () use ($model2) {
            $model2->sortChange(2);
            expect("the model2 should be move up", $model2->sort)->equals(6);
            expect("the sort sequence should not be broken", $this->checkSequence($model2))->true();
        });
        $model3 = Model3::findOne(13);
        $this->specify("we move model3 down on 1 position", function () use ($model3) {
            $model3->sortChange(-1);
            expect("the model3 should be move down", $model3->sort)->equals(2);
            expect("the sort sequence should not be broken", $this->checkSequence($model3))->true();
        });
        $this->specify("we move model3 up on 2 position", function () use ($model3) {
            $model3->sortChange(2);
            expect("the model3 should be move up", $model3->sort)->equals(4);
            expect("the sort sequence should not be broken", $this->checkSequence($model3))->true();
        });
    }

    public function testRecalculateSort()
    {
        $model = Model::findOne(1);
        $this->specify("we broken and repair model sorting", function () use ($model) {
            $model->updateAll(['sort' => 0]);
            expect("the sort sequence should be broken", $this->checkSequence($model))->false();
            $model->recalculateSort();
            expect("the sort sequence should be repair", $this->checkSequence($model))->true();
        });
        $model2 = Model2::findOne(8);
        $this->specify("we broken and repair model2 sorting", function () use ($model2) {
            $model2->updateAll(['sort' => 0], ['condition' => $model2->condition]);
            expect("the sort sequence should be broken", $this->checkSequence($model2))->false();
            $model2->recalculateSort();
            expect("the sort sequence should be repair", $this->checkSequence($model2))->true();
        });
        $model3 = Model3::findOne(13);
        $this->specify("we broken and repair model3 sorting", function () use ($model3) {
            $model3->updateAll(['sort' => 0], ['condition' => $model3->condition, 'condition2' => $model3->condition2]);
            expect("the sort sequence should be broken", $this->checkSequence($model3))->false();
            $model3->recalculateSort();
            expect("the sort sequence should be repair", $this->checkSequence($model3))->true();
        });
    }

    public function testSpecial()
    {
        $model = Model::findOne(3);
        $model2 = Model2::findOne(3);
        $model3 = Model3::findOne(3);
        $this->specify("we change sort by 0", function () use ($model, $model2, $model3) {
            $oldSort = $model->sort;
            $model->sortChange(0);
            expect("sort should be not changed", $model->sort)->equals($oldSort);

            $oldSort = $model2->sort;
            $model2->sortChange(0);
            expect("sort should be not changed", $model2->sort)->equals($oldSort);

            $oldSort = $model3->sort;
            $model3->sortChange(0);
            expect("sort should be not changed", $model3->sort)->equals($oldSort);
        });

        $model = Model::findOne(15);
        $model2 = Model2::findOne(15);
        $model3 = Model3::findOne(15);
        $this->specify("we move up maximal", function () use ($model, $model2, $model3) {
            $oldSort = $model->sort;
            $model->sortChange(1);
            expect("sort should be not changed", $model->sort)->equals($oldSort);

            $oldSort = $model2->sort;
            $model2->sortChange(1);
            expect("sort should be not changed", $model2->sort)->equals($oldSort);

            $oldSort = $model3->sort;
            $model3->sortChange(1);
            expect("sort should be not changed", $model3->sort)->equals($oldSort);
        });

        $model = Model::findOne(1);
        $model2 = Model2::findOne(1);
        $model3 = Model3::findOne(1);
        $this->specify("we move down minimal", function () use ($model, $model2, $model3) {
            $oldSort = $model->sort;
            $model->sortChange(-1);
            expect("sort should be not changed", $model->sort)->equals($oldSort);

            $oldSort = $model2->sort;
            $model2->sortChange(-1);
            expect("sort should be not changed", $model2->sort)->equals($oldSort);

            $oldSort = $model3->sort;
            $model3->sortChange(-1);
            expect("sort should be not changed", $model3->sort)->equals($oldSort);
        });

        $model = Model::findOne(2);
        $model2 = Model2::findOne(2);
        $model3 = Model3::findOne(2);
        $this->specify("we move down over 0", function () use ($model, $model2, $model3) {
            $model->sortChange(-10);
            expect("sort will be 0", $model->sort)->equals(0);

            $model2->sortChange(-10);
            expect("sort will be 0", $model2->sort)->equals(0);

            $model3->sortChange(-10);
            expect("sort will be 0", $model3->sort)->equals(0);
        });

        $model = Model::findOne(5);
        $model2 = Model2::findOne(5);
        $model3 = Model3::findOne(5);
        $this->specify("we try to direct change sort", function () use ($model, $model2, $model3) {
            $oldSort = $model->sort;
            $model->sort = 100;
            expect('model must be saved', $model->save())->true();
            expect("sort should be not changed", $model->sort)->equals($oldSort);

            $oldSort = $model2->sort;
            $model2->sort = 100;
            expect('model2 must be saved', $model2->save())->true();
            expect("sort should be not changed", $model2->sort)->equals($oldSort);

            $oldSort = $model3->sort;
            $model3->sort = 100;
            expect('model3 must be saved', $model3->save())->true();
            expect("sort should be not changed", $model3->sort)->equals($oldSort);
        });

        $model = Model::findOne(7);
        $model2 = Model2::findOne(7);
        $model3 = Model3::findOne(7);
        $this->specify("we delete model", function () use ($model, $model2, $model3) {
            expect('model must be deleted', $model->delete())->equals(1);
            expect("the model sort sequence should be not broken", $this->checkSequence($model))->true();

            expect('model2 must be deleted', $model2->delete())->equals(1);
            expect("the model2 sort sequence should be not broken", $this->checkSequence($model2))->true();

            expect('model3 must be deleted', $model3->delete())->equals(1);
            expect("the model3 sort sequence should be not broken", $this->checkSequence($model3))->true();
        });

        $model2 = Model2::findOne(14);
        // set undefined model attribute in condition
        $model2->getBehavior('sortable')->conditionAttributes = ['condition', 'foobar'];
        $this->specify("we change sort with error attribute name in condition", function () use ($model2) {
            $model2->moveToBottom();
            expect('model2 should be chnage sort', $model2->sort)->equals(0);
            expect("the model2 sort sequence should be not broken", $this->checkSequence($model2))->true();
        });
    }

    public function testAfterUpdate()
    {
        $this->specify("we change models", function () {
            $model = Model::findOne(3);
            $oldSort = $model->sort;
            $model->name = "Changed name";
            expect("model must be save", $model->save())->true();
            expect("model sort should be not changed", $model->sort)->equals($oldSort);

            $model2 = Model2::findOne(3);
            $model2->name = "Changed name";
            $model2->condition = 2;
            expect("model2 must be save", $model2->save())->true();
            expect("model2 sort should be changed", $model2->sort)->equals(9);
            expect("model2 sort sequence should be not broken", $this->checkSequence($model2))->true();

            $model2 = Model2::findOne(11);
            // set undefined model attribute in condition
            $model2->getBehavior('sortable')->conditionAttributes = ['condition', 'foobar'];
            $model2->name = "Changed name2";
            $model2->condition = 1;
            expect("model2 must be save", $model2->save())->true();
            expect("model2 sort should be changed", $model2->sort)->equals(4);
            expect("model2 sort sequence should be not broken", $this->checkSequence($model2))->true();

            $model3 = Model3::findOne(8);
            $model3->name = "Changed name";
            $model3->condition2 = 'c';
            expect("model3 must be save", $model3->save())->true();
            expect("model3 sort should be changed", $model3->sort)->equals(5);
            expect("model3 sort sequence should be not broken", $this->checkSequence($model3))->true();

            $model3 = Model3::findOne(6);
            $model3->name = "Changed name2";
            $model3->condition = 3;
            $model3->condition2 = 'a';
            expect("model3(2) must be save", $model3->save())->true();
            expect("model3(2) sort should be changed", $model3->sort)->equals(1);
            expect("model3(2) sort sequence should be not broken", $this->checkSequence($model3))->true();
        });
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}
