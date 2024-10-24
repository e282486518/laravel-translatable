<?php

namespace e282486518\Translatable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasTranslations
{
    /**
     * @var string|null 当前多语言
     */
    protected ?string $translationLocale = null;

    /**
     * ---------------------------------------
     * 设置多语言
     *
     * @param string $locale
     * @return $this
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function setLocale(string $locale): self
    {
        $this->translationLocale = $locale;

        return $this;
    }

    /**
     * ---------------------------------------
     * 获取多语言
     *
     * @return string
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function getLocale(): string
    {
        return $this->translationLocale ?: config('app.locale');
    }

    // setLocale 的静态方法
    public static function usingLocale(string $locale): self
    {
        return (new self())->setLocale($locale);
    }


    // ====== 以下 \Illuminate\Database\Eloquent\Model->toArray() 调用 =========================================

    /**
     * ---------------------------------------
     * 获取映射数组, 将 多语言字段 映射成array,
     * [重写]会使用 HasAttributes->castAttribute($key, $value) 执行return $this->fromJson($value);
     *
     * @return array
     * @author hlf <phphome@qq.com> 2024/10/17
     * ---------------------------------------
     */
    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            array_fill_keys($this->getTranslatableAttributes(), 'array'),
        );
    }

    /**
     * 解析json
     * [重写]模型的 HasAttributes->fromJson() 方法, 用以处理json时,如果非json格式, 那么返回原数据
     *
     * @param  string  $value
     * @param  bool  $asObject
     * @return mixed
     */
    public function fromJson($value, $asObject = false): mixed {
        $obj = json_decode($value ?? '', ! $asObject);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $obj;
        }
        return $value;
    }

    // ====== 以下 \Illuminate\Database\Eloquent\Model->setAttribute/getAttribute 调用 =========================================

    /**
     * ---------------------------------------
     * 获取一个普通属性（而不是relationship）。
     * [重写] trait HasAttributes 的 getAttributeValue 方法
     *
     * @param $key
     * @return mixed
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function getAttributeValue($key): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        }

        return $this->getTranslation($key, $this->getLocale());
    }

    /**
     * ---------------------------------------
     * 使用属性的赋值器进行数组转换，以获取属性的值。
     * [重写] trait HasAttributes 的 mutateAttributeForArray 方法
     *
     * @param $key
     * @param $value
     * @return mixed
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    protected function mutateAttributeForArray($key, $value): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::mutateAttributeForArray($key, $value);
        }

        $translations = $this->getTranslations($key);

        return array_map(fn ($value) => parent::mutateAttributeForArray($key, $value), $translations);
    }

    /**
     * ---------------------------------------
     * 在模型上设置给定的属性。
     * [重写] trait HasAttributes 的 setAttribute 方法
     *
     * @param $key
     * @param $value
     * @return HasTranslations|mixed
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function setAttribute($key, $value)
    {
        if ($this->isTranslatableAttribute($key) && is_array($value)) {
            return $this->setTranslations($key, $value);
        }

        // Pass arrays and untranslatable attributes to the parent method.
        if (! $this->isTranslatableAttribute($key) || is_array($value)) {
            return parent::setAttribute($key, $value);
        }

        // If the attribute is translatable and not already translated, set a
        // translation for the current app locale.
        return $this->setTranslation($key, $this->getLocale(), $value);
    }



    // ====== 多语言列, JSON =============================================

    /**
     * ---------------------------------------
     * 取当前"列"指定语言的值
     *
     * @param string $key
     * @param string $locale
     * @return mixed
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function getTranslation(string $key, string $locale): mixed
    {
        $normalizedLocale = $this->normalizeLocale($key, $locale); // 取标准的语言:zh_CN/en

        $translations = $this->getTranslations($key); // ['zh_CN'=>'中文', 'en'=>'English']

        $translation = $translations[$normalizedLocale] ?? ''; // 当前语言值: 中文

        // get{$key}Attribute 方法
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        }
        // $attributeMutatorCache 缓存的方法
        if($this->hasAttributeMutator($key)) {
            return $this->mutateAttributeMarkedAttribute($key, $translation);
        }

        return $translation;
    }

    /**
     * ---------------------------------------
     * 获取 "多语言字段", 并将多语言字段解析成数组(单个/多个)
     *
     * @param string|null $key
     * @param array|null $allowedLocales 允许的语言, 主要用于语言过滤
     * @return array
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function getTranslations(string $key = null, array $allowedLocales = null): array
    {
        if ($key !== null) {
            $_attr = $this->getAttributes();
            if (!isset($_attr[$key])) {
                // 模型中无此"列"
                return [];
            }
            // JSON转Array
            $_attrs_arr = json_decode($_attr[$key] ?? '' ?: '{}', true);
            // JSON字符串, 反序列化
            if (json_last_error() === JSON_ERROR_NONE && is_array($_attrs_arr)) {
                // ['zh_CN'=>'中文', 'en'=>'English']
                return array_filter(
                    $_attrs_arr ?: [],
                    fn ($value, $locale) => $this->filterTranslations($value, $locale, $allowedLocales),
                    ARRAY_FILTER_USE_BOTH,
                );
            }
            // 如果是字符串, 构造一个数组 ['zh_CN' => '属性值'], 方便兼容旧数据
            return [config('app.locale') => $_attr[$key]];
        }

        // 多字段反序列化
        return array_reduce($this->getTranslatableAttributes(), function ($result, $item) use ($allowedLocales) {
            $result[$item] = $this->getTranslations($item, $allowedLocales);

            return $result; // ['title'=>['zh_CN'=>'中文', 'en'=>'English'], .....]
        });
    }

    /**
     * ---------------------------------------
     * 设置"列"的语言值
     *
     * @param string $key
     * @param string $locale
     * @param $value
     * @return $this
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function setTranslation(string $key, string $locale, $value): self
    {
        $translations = $this->getTranslations($key);

        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$key];
        } elseif($this->hasAttributeSetMutator($key)) { // handle new attribute mutator
            $this->setAttributeMarkedMutatedAttributeValue($key, $value);

            $value = $this->attributes[$key];
        }

        $translations[$locale] = $value;

        // 设置列的json值
        $this->attributes[$key] = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }

    /**
     * ---------------------------------------
     * 批量设置值
     *
     * @param string $key
     * @param array $translations ['zh_CN'=>'中文', 'en'=>'English']
     * @return $this
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function setTranslations(string $key, array $translations): self
    {
        if (! empty($translations)) {
            foreach ($translations as $locale => $translation) {
                $this->setTranslation($key, $locale, $translation);
            }
        } else {
            $this->attributes[$key] = $this->asJson([]);
        }

        return $this;
    }


    // ====== 以下为辅助方法 ================================================

    /**
     * ---------------------------------------
     * 获取支持多语言的列 ['title', 'desc', ....]
     *
     * @return array
     * @author hlf <phphome@qq.com> 2024/10/17
     * ---------------------------------------
     */
    public function getTranslatableAttributes(): array
    {
        return is_array($this->translatable)
            ? $this->translatable
            : [];
    }

    /**
     * ---------------------------------------
     * 取当前"列"的值, 已经设置了哪些语言
     *
     * @param string $key
     * @return array
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function getTranslatedLocales(string $key): array
    {
        return array_keys($this->getTranslations($key));
    }

    /**
     * ---------------------------------------
     * 判断当前"列"是否支持多语言
     *
     * @param string $key
     * @return bool
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }


    /**
     * ---------------------------------------
     * 规范当前语言
     *
     * @param string $key
     * @param string $locale
     * @return string
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    protected function normalizeLocale(string $key, string $locale): string
    {
        $translatedLocales = $this->getTranslatedLocales($key); // 取当前key的多语言列表['zh_CN', 'en']

        if (in_array($locale, $translatedLocales)) {
            return $locale;
        }

        return $this->getLocale();
    }

    /**
     * ---------------------------------------
     * 过滤$value中的语言字段
     *
     * @param mixed|null $value
     * @param string|null $locale
     * @param array|null $allowedLocales
     * @return bool
     * @author hlf <phphome@qq.com> 2024/10/24
     * ---------------------------------------
     */
    protected function filterTranslations(mixed $value = null, string $locale = null, array $allowedLocales = null): bool
    {
        if ($value === null) {
            return false;
        }

        if ($value === '') {
            return false;
        }

        if ($allowedLocales === null) {
            return true;
        }

        if (! in_array($locale, $allowedLocales)) {
            return false;
        }

        return true;
    }



    // ===== 构造JSON的sql条件查询 ================================

    public function scopeWhereLocale(Builder $query, string $column, string $locale): void
    {
        $query->whereNotNull("{$column}->{$locale}");
    }

    public function scopeWhereLocales(Builder $query, string $column, array $locales): void
    {
        $query->where(function (Builder $query) use ($column, $locales) {
            foreach ($locales as $locale) {
                $query->orWhereNotNull("{$column}->{$locale}");
            }
        });
    }

    public function scopeWhereJsonContainsLocale(Builder $query, string $column, string $locale, mixed $value, string $operand = '='): void
    {
        $query->where("{$column}->{$locale}", $operand, $value);
    }

    public function scopeWhereJsonContainsLocales(Builder $query, string $column, array $locales, mixed $value, string $operand = '='): void
    {
        $query->where(function (Builder $query) use ($column, $locales, $value, $operand) {
            foreach($locales as $locale) {
                $query->orWhere("{$column}->{$locale}", $operand, $value);
            }
        });
    }

    /**
     * @deprecated
     */
    public static function whereLocale(string $column, string $locale): Builder
    {
        return static::query()->whereNotNull("{$column}->{$locale}");
    }

    /**
     * @deprecated
     */
    public static function whereLocales(string $column, array $locales): Builder
    {
        return static::query()->where(function (Builder $query) use ($column, $locales) {
            foreach ($locales as $locale) {
                $query->orWhereNotNull("{$column}->{$locale}");
            }
        });
    }
}
