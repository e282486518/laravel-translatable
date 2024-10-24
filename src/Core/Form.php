<?php

namespace e282486518\Translatable\Core;

use Closure;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use e282486518\Translatable\Helpers;
use e282486518\Translatable\Core\Form\Field;
use Illuminate\Support\Fluent;

class Form extends \Dcat\Admin\Form
{

    protected static $availableFields = [
        'button'              => Field\Button::class,
        'checkbox'            => Field\Checkbox::class,
        'currency'            => Field\Currency::class,
        'date'                => Field\Date::class,
        'dateRange'           => Field\DateRange::class,
        'datetime'            => Field\Datetime::class,
        'datetimeRange'       => Field\DatetimeRange::class,
        'decimal'             => Field\Decimal::class,
        'display'             => Field\Display::class,
        'divider'             => Field\Divide::class,
        'embeds'              => Field\Embeds::class,
        'editor'              => Field\Editor::class,
        'email'               => Field\Email::class,
        'hidden'              => Field\Hidden::class,
        'id'                  => Field\Id::class,
        'ip'                  => Field\Ip::class,
        'map'                 => Field\Map::class,
        'mobile'              => Field\Mobile::class,
        'month'               => Field\Month::class,
        'multipleSelect'      => Field\MultipleSelect::class,
        'number'              => Field\Number::class,
        'password'            => Field\Password::class,
        'radio'               => Field\Radio::class,
        'rate'                => Field\Rate::class,
        'select'              => Field\Select::class,
        'slider'              => Field\Slider::class,
        'switch'              => Field\SwitchField::class,
        'text'                => Field\Text::class,
        'textarea'            => Field\Textarea::class,
        'time'                => Field\Time::class,
        'timeRange'           => Field\TimeRange::class,
        'url'                 => Field\Url::class,
        'year'                => Field\Year::class,
        'html'                => Field\Html::class,
        'tags'                => Field\Tags::class,
        'icon'                => Field\Icon::class,
        'captcha'             => Field\Captcha::class,
        'listbox'             => Field\Listbox::class,
        'file'                => Field\File::class,
        'image'               => Field\Image::class,
        'multipleFile'        => Field\MultipleFile::class,
        'multipleImage'       => Field\MultipleImage::class,
        'hasMany'             => Field\HasMany::class,
        'tree'                => Field\Tree::class,
        'table'               => Field\Table::class,
        'list'                => Field\ListField::class,
        'timezone'            => Field\Timezone::class,
        'keyValue'            => Field\KeyValue::class,
        'tel'                 => Field\Tel::class,
        'markdown'            => Field\Markdown::class,
        'range'               => Field\Range::class,
        'color'               => Field\Color::class,
        'array'               => Field\ArrayField::class,
        'selectTable'         => Field\SelectTable::class,
        'multipleSelectTable' => Field\MultipleSelectTable::class,
        'autocomplete'        => Field\Autocomplete::class,
    ];


    /**
     * Create a new form instance.
     *
     * @param  Repository|Model|\Illuminate\Database\Eloquent\Builder|string  $model
     * @param  \Closure  $callback
     * @param  Request  $request
     */
    public function __construct($repository = null, ?Closure $callback = null, Request $request = null)
    {
        $this->repository = $repository ? Admin::repository($repository) : null;
        $this->callback = $callback;
        $this->request = $request ?: request();
        $this->builder = new Form\Builder($this);
        $this->isSoftDeletes = $repository ? $this->repository->isSoftDeletes() : false;

        $this->model(new Fluent());
        $this->prepareDialogForm();
        $this->callResolving();
    }

    /**
     * @var string 多语言Form展示方式: tab/line
     */
    protected $localeForm;
    // 设置
    public function setLocaleForm($lab) {
        if (in_array($lab, ['tab', 'line'])) {
            $this->localeForm = $lab;
        }
        return $this;
    }
    // 获取
    public function getLocaleForm() {
        return $this->localeForm ?? config('translatable.locale_form');
    }

    /**
     * 对写入的数据进行前置操作
     * 支持 {"title": {"cn": "1", "en": "2"}, "desc": {"cn": "3", "en": "4"}, "status": 1} 格式
     * 也就是form的 title[cn]=1, title[en]=2, desc[cn]=3, desc[en]=4, status=1
     *
     * @param  array  $updates
     * @return array
     */
    public function prepareUpdate(array $updates): array {
        $prepared = [];

        /** @var Field $field */
        foreach ($this->builder->fields() as $field) {
            $columns = $field->column(); // title[cn]

            [$columns, $lng] = Helpers::getColumnAndlng($columns); // 新增行, 处理掉[]和其内的内容

            // If column not in input array data, then continue.
            if (! Arr::has($updates, $columns) || Arr::has($prepared, $columns)) {
                continue;
            }

            $value = $this->getDataByColumn($updates, $columns);

            $value = $field->prepare($value);

            if (is_array($columns)) {
                foreach ($columns as $name => $column) {
                    Arr::set($prepared, $column, $value[$name]);
                }
            } elseif (is_string($columns)) {
                Arr::set($prepared, $columns, $value);
            }
        }

        return $prepared;
    }

    /**
     * Generate a Field object and add to form builder if Field exists.
     *
     * @param  string  $method
     * @param  array  $arguments
     * @return Field
     */
    public function __call($method, $arguments)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $arguments);
        }

        if ($className = static::findFieldClass($method)) {
            $column = Arr::get($arguments, 0, '');

            $element = new $className($column, array_slice($arguments, 1));

            // 设置字段是否支持多语言
            if ($this->repository()) {
                $_model = $this->repository()->model(); // 模型存在时, 取模型
                if (isset($_model->translatable)) {
                    $_fields = $_model->translatable?:[]; // 取多语言字段列表, 默认[]
                    if (in_array($column, $_fields)) {
                        $element->setTranslatable(true);
                    }
                }
            }


            $this->pushField($element);

            return $element;
        }

        admin_error('Error', "Field type [$method] does not exist.");

        return new Field\Nullable();
    }

    /**
     * 重写 use Concerns\HasRows 方法
     *
     * @param  Closure  $callback
     * @return $this
     */
    public function row(Closure $callback)
    {
        $this->rows[] = new Form\Row($callback, $this);

        return $this;
    }
}
