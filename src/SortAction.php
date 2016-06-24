<?php
/**
 * Controller's action to sort change in inblank\yii2-sortable behavior
 *
 * @link https://github.com/inblank/yii2-sortable
 * @copyright Copyright (c) 2016 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */
namespace inblank\sortable;

use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\Response;

/**
 * SortAction
 */
class SortAction extends Action
{
    /**
     * Sorting model class name
     * @var string
     */
    public $modelClass;

    /**
     * Redirect URL if request not by ajax
     * @var array
     */
    public $redirectUrl = ['index'];

    /**
     * Sort action
     * @param int $id model identifier
     * @param int|string $position new sort position
     * May be set as:
     *  top - move model to top of sorting
     *  bottom - move model to bottom of sorting
     *  u<number> - up to <number> positions
     *  d<number> - down to <number> positions
     *  -<number> - down to <number> positions
     *  <number> - move tto specific position
     * @return array|Response
     * @throws HttpException
     */
    public function run($id, $position)
    {
        if (empty($this->modelClass) || !class_exists($this->modelClass)) {
            throw new InvalidParamException('Define model class name for action ' . $this->controller->id . '::' . $this->id);
        }
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        /** @var SortableBehavior $model */
        $model = $class::findOne($id);
        if (!$model) {
            throw new HttpException(404, 'Model `' . $this->modelClass . '` to change sort not found');
        }
        switch ($position) {
            case 'top':
                // move model to top
                $model->moveToTop();
                break;
            case 'bottom':
                // move model to bottom
                $model->moveToBottom();
                break;
            default:
                if (strpos('ud-', $position[0]) !== false) {
                    // relative position
                    $position = ($position[0] == 'd' ? '-' : '') . substr($position, 1);
                    if (is_numeric($position)) {
                        $model->sortChange($position);
                    }
                } elseif (is_numeric($position)) {
                    // absolute position
                    $model->moveToPosition($position);
                }
                break;
        }
        return Yii::$app->response->format == Response::FORMAT_JSON ?
            ['status' => 200] :
            $this->controller->redirect($this->redirectUrl);
    }
}
