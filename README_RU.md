# Yii2 поведение inblank/yii2-sortable для управления сортировкой ActiveRecord моделей

[![Build Status](https://img.shields.io/travis/inblank/yii2-sortable/master.svg?style=flat-square)](https://travis-ci.org/inblank/yii2-sortable)
[![Packagist Version](https://img.shields.io/packagist/v/inblank/yii2-sortable.svg?style=flat-square)](https://packagist.org/packages/inblank/yii2-sortable)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/inblank/yii2-sortable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/inblank/yii2-sortable/?branch=master)
[![Code Quality](https://img.shields.io/scrutinizer/g/inblank/yii2-sortable/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/inblank/yii2-sortable/?branch=master)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](https://raw.githubusercontent.com/inblank/yii2-sortable/master/LICENSE)

> The **[English version](https://github.com/inblank/yii2-sortable/blob/master/README.md)** of this document available [here](https://github.com/inblank/yii2-sortable/blob/master/README.md).

Поведение `yii2-sortable` для [Yii2](http://www.yiiframework.com/) позволяет управлять сортировкой ActiveRecord моделей. 

## Установка

Рекомендуется устанавливать поведение через [composer](http://getcomposer.org/download/).

Перейдите в папку проекта и выполните в консоли команду:

```bash
$ composer require inblank/yii2-sortable
```

или добавьте:

```json
"inblank/yii2-sortable": "*"
```

в раздел `require` конфигурационного файла `composer.json`.

## Настройка

Для использования поведения просто подключите его к модели ActiveRecord как 
указано в [документации Yii2](https://github.com/yiisoft/yii2/blob/master/docs/guide-ru/concept-behaviors.md)
 
```php
use inblank\sortable\SortableBehavior;

/**
 * ...
 */
class Model extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'sortable'=>[
                'class' => SortableBehavior::className(),
                // 'sortAttribute' => 'sort',
                // 'conditionAttributes'=>[]
            ]
        ];
    }
}
```

По умолчанию для хранения информации о сортировке используется атрибут `sort` модели. Вы можете использовать 
другой атрибут, указав его имя в значении `sortAttribute` при подключении к модели.

```php
public function behaviors()
{
    return [
        'sortable'=>[
            'class' => SortableBehavior::className(),
            'sortAttribute' => 'my_order',
        ]
    ];
}
```

Так же можно задать условие в пределах которого производится сортировка. Для этого укажите список атрибутов значения 
 которых учитывать для определения области сортировки в значении `conditionAttributes` при подключении к модели.

```php
public function behaviors()
{
    return [
        'sortable'=>[
            'class' => SortableBehavior::className(),
            'conditionAttributes' => ['condition', 'condition2'],
        ]
    ];
}
```

 Если в условии один атрибут, его можно задать строкой.

```php
public function behaviors()
{
    return [
        'sortable'=>[
            'class' => SortableBehavior::className(),
            'conditionAttributes' => 'condition',
        ]
    ];
}
```

Если условие не задано, то сортировка будут производится по всем моделям.
  
## Использование

Поведение предоставляет несколько методов для управления сортировкой модели.
Использование методов не требует вызова после себя метода `save`, так как значение
  сортировки будет сохранено самим методом.

> Примечание: Отсчет сортировки начинается с 0

### Перемещение в позицию
Для перемещения модели в определенную позицию, в пределах условия сортировки, 
используется метод `moveToPosition()`

```php
$model = Model::findOne(100);

// move in sort position 42 
$model->moveToPosition(42);
```

### Перемещение в начало
Для перемещения модели в начало списка моделей, в пределах условия сортировки, 
используется метод `moveToTop()`

```php
$model = Model::findOne(100);

// move to top
$model->moveToTop();
```

### Перемещение в конец
Для перемещения модели в конец списка моделей, в пределах условия сортировки, 
используется метод `moveToBottom(0)`

```php
$model = Model::findOne(100);

// move to bottom
$model->moveToBottom();
```

### Изменение положения относительно текущего
Для изменения положения модели относительно текущего положения , в пределах условия сортировки, 
используется метод `sortChange()`

```php
$model = Model::findOne(100);

// to move up 
$model->sortChange(2);


$model = Model::findOne(15);

// to move down 
$model->sortChange(-5);
```

### recalculateSort()
Иногда возникает необходимость пересчитать сортировку, которая могла быть нарушена в силу 
каких-либо обстоятельств. Для этого можно воспользоваться методом `recalculateSort()`

Метод `recalculateSort()` пересчитает значение сортировки для моделей попадающих в условие
 сортировки текущей модели.

```php
$model = Model::findOne(45);

// recaculate sort values 
$model->recalculateSort();
```
