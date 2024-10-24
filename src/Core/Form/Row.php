<?php

namespace e282486518\Translatable\Core\Form;

use Dcat\Admin\Form;
use Dcat\Admin\Widgets\Form as WidgetForm;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Collection;

/**
 * Class Row.
 *
 * @method Field\Text text($column, $label = '')
 * @method Field\Checkbox checkbox($column, $label = '')
 * @method Field\Radio radio($column, $label = '')
 * @method Field\Select select($column, $label = '')
 * @method Field\MultipleSelect multipleSelect($column, $label = '')
 * @method Field\Textarea textarea($column, $label = '')
 * @method Field\Hidden hidden($column, $label = '')
 * @method Field\Id id($column, $label = '')
 * @method Field\Ip ip($column, $label = '')
 * @method Field\Url url($column, $label = '')
 * @method Field\Email email($column, $label = '')
 * @method Field\Mobile mobile($column, $label = '')
 * @method Field\Slider slider($column, $label = '')
 * @method Field\Map map($latitude, $longitude, $label = '')
 * @method Field\Editor editor($column, $label = '')
 * @method Field\Date date($column, $label = '')
 * @method Field\Datetime datetime($column, $label = '')
 * @method Field\Time time($column, $label = '')
 * @method Field\Year year($column, $label = '')
 * @method Field\Month month($column, $label = '')
 * @method Field\DateRange dateRange($start, $end, $label = '')
 * @method Field\DateTimeRange datetimeRange($start, $end, $label = '')
 * @method Field\TimeRange timeRange($start, $end, $label = '')
 * @method Field\Number number($column, $label = '')
 * @method Field\Currency currency($column, $label = '')
 * @method Field\SwitchField switch ($column, $label = '')
 * @method Field\Display display($column, $label = '')
 * @method Field\Rate rate($column, $label = '')
 * @method Field\Divide divider()
 * @method Field\Password password($column, $label = '')
 * @method Field\Decimal decimal($column, $label = '')
 * @method Field\Html html($html, $label = '')
 * @method Field\Tags tags($column, $label = '')
 * @method Field\Icon icon($column, $label = '')
 * @method Field\Embeds embeds($column, $label = '')
 * @method Field\Captcha captcha()
 * @method Field\Listbox listbox($column, $label = '')
 * @method Field\File file($column, $label = '')
 * @method Field\Image image($column, $label = '')
 * @method Field\MultipleFile multipleFile($column, $label = '')
 * @method Field\MultipleImage multipleImage($column, $label = '')
 * @method Field\HasMany hasMany($column, $labelOrCallback, $callback = null)
 * @method Field\Tree tree($column, $label = '')
 * @method Field\Table table($column, $labelOrCallback, $callback = null)
 * @method Field\ListField list($column, $label = '')
 * @method Field\Timezone timezone($column, $label = '')
 * @method Field\KeyValue keyValue($column, $label = '')
 * @method Field\Tel tel($column, $label = '')
 * @method Field\Markdown markdown($column, $label = '')
 * @method Field\Range range($start, $end, $label = '')
 */
class Row extends \Dcat\Admin\Form\Row
{

    /**
     * Render the row.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        return view('translatable::form.row', ['fields' => $this->fields]);
    }

}
