# Dcat Admin Extension

这是一个`Dcat-Admin`的多语言兼容扩展, 在不改变`dcat-admin`原使用习惯的前提下, 兼容数据库多语言方案.

## 使用方法

**第一步: 安装扩展**

```
composer require e282486518/laravel-translatable
```

或者下载此扩展, 将其复制到 `dcat-admin-extensions` 目录下, 并在后台中启用此扩展.


**第二步: 修改数据库**

将数据库的多语言字段, 设置成 `JSON` 类型, 最好 `MySQL5.7` 以上.


**第三步: 模型修改**

```
use Illuminate\Database\Eloquent\Model;
use e282486518\Translatable\HasTranslations;

class Test extends Model
{
    // 多语言trait
    use HasTranslations;
    
    // 需要多语言支持的字段
    public array $translatable = ['title', 'desc'];
    
    // ...
}
```

**第四步: 配置文件, 语言文件修改**

配置文件, 将 `translatable.php` 复制到 `/config/` 目录中. 并配置.

```
// 设置后台form展示方式, 一种是 tab 模式, 一种是line模式
'locale_form' => 'line', // tab/line

// 设置当前支持哪些语言
'locale_array' => [
    'zh_CN' => '中文',
    'en' => 'English'
],
```

语言文件, 配置 `/lang/` 目录下的模型语言文件, 支持的语言文件最好都设置.


## 截图

DEMO: sql文件
```
CREATE TABLE `yw_test` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` json DEFAULT NULL,
  `desc` json DEFAULT NULL,
  `status` tinyint(2) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

insert  into `yw_test`(`id`,`title`,`desc`,`status`) values 
(1,'{\"en\": \"test title\", \"zh_CN\": \"中文 标题1\"}','{\"en\": \"test desc\", \"zh_CN\": \"中文描述1\"}',1),
(2,'{\"en\": \"test title english\", \"zh_CN\": \"测试中文标题\"}','{\"en\": \"test desc english\", \"zh_CN\": \"测试中文描述\"}',1);

```

DEMO: /app/Models/Test.php
```
<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use e282486518\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasTranslations; //⭐

    use HasDateTimeFormatter;
    protected $table = 'yw_test';
    public $timestamps = false;


    // 需要多语言支持的字段
    public array $translatable = ['title', 'desc']; //⭐

}
```

DEMO: /app/Admin/Controllers/TestController.php
```
<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\YTest;
use Dcat\Admin\Grid;
use e282486518\Translatable\Core\Grid as TGrid; //⭐
use e282486518\Translatable\Core\Show as TShow; //⭐
use e282486518\Translatable\Core\Form as TForm; //⭐
use Dcat\Admin\Http\Controllers\AdminController;

class TestController extends AdminController
{
    protected function grid()
    {
        //App::setLocale('zh_CN');
        return TGrid::make(new YTest(), function (TGrid $grid) { //⭐
            $grid->column('id')->sortable();
            $grid->column('title');
            $grid->column('desc');
            $grid->column('status');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    protected function detail($id)
    {
        return TShow::make($id, new YTest(), function (TShow $show) { //⭐
            $show->field('id');
            $show->field('title');
            $show->field('desc');
            $show->field('status');
        });
    }

    protected function form()
    {
        return TForm::make(new YTest(), function (TForm $form) { //⭐
            $form->display("id");
            $form->text("title")->required();
            $form->text("desc");
            $form->text("status");
        });
    }
}
```

![列表1](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/index-cn.png)
![列表2](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/index-en.png)
![编辑1](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/edit-cn.png)
![编辑2](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/edit-en.png)
![编辑3](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/edit-line.png)
![显示1](https://raw.githubusercontent.com/e282486518/yii2admin/master/preview/show.png)
