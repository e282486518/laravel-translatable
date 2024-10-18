<?php

namespace e282486518\Translatable;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

trait HasTranslations
{
    protected ?string $translationLocale = null;

    public static function usingLocale(string $locale): self
    {
        return (new self())->setLocale($locale);
    }

    public function useFallbackLocale(): bool
    {
        if (property_exists($this, 'useFallbackLocale')) {
            return $this->useFallbackLocale;
        }

        return true;
    }

    public function getAttributeValue($key): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::getAttributeValue($key);
        }

        return $this->getTranslation($key, $this->getLocale(), $this->useFallbackLocale());
    }

    protected function mutateAttributeForArray($key, $value): mixed
    {
        if (! $this->isTranslatableAttribute($key)) {
            return parent::mutateAttributeForArray($key, $value);
        }

        $translations = $this->getTranslations($key);

        return array_map(fn ($value) => parent::mutateAttributeForArray($key, $value), $translations);
    }

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

    public function translate(string $key, string $locale = '', bool $useFallbackLocale = true): mixed
    {
        return $this->getTranslation($key, $locale, $useFallbackLocale);
    }

    public function getTranslation(string $key, string $locale, bool $useFallbackLocale = true): mixed
    {
        $normalizedLocale = $this->normalizeLocale($key, $locale, $useFallbackLocale);

        $isKeyMissingFromLocale = ($locale !== $normalizedLocale);

        $translations = $this->getTranslations($key);

        $translation = $translations[$normalizedLocale] ?? '';

        $translatableConfig = app(Translatable::class);

        if ($isKeyMissingFromLocale && $translatableConfig->missingKeyCallback) {
            try {
                $callbackReturnValue = (app(Translatable::class)->missingKeyCallback)($this, $key, $locale, $translation, $normalizedLocale);
                if (is_string($callbackReturnValue)) {
                    $translation = $callbackReturnValue;
                }
            } catch (Exception) {
                //prevent the fallback to crash
            }
        }

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $translation);
        }

        if($this->hasAttributeMutator($key)) {
            return $this->mutateAttributeMarkedAttribute($key, $translation);
        }

        return $translation;
    }

    public function getTranslationWithFallback(string $key, string $locale): mixed
    {
        return $this->getTranslation($key, $locale, true);
    }

    public function getTranslationWithoutFallback(string $key, string $locale): mixed
    {
        return $this->getTranslation($key, $locale, false);
    }

    public function getTranslations(string $key = null, array $allowedLocales = null): array
    {
        if ($key !== null) {
            $this->guardAgainstNonTranslatableAttribute($key);

            $_attrs_str = $this->getAttributes()[$key];
            $_attrs_arr = json_decode($_attrs_str ?? '' ?: '{}', true);

            // JSON字符串, 反序列化
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_filter(
                    $_attrs_arr ?: [],
                    fn ($value, $locale) => $this->filterTranslations($value, $locale, $allowedLocales),
                    ARRAY_FILTER_USE_BOTH,
                );
            }
            // 如果是字符串, 构造一个数组 ['zh_CN' => '属性值'], 方便兼容旧数据
            return [config('app.locale') => $_attrs_str];
        }

        return array_reduce($this->getTranslatableAttributes(), function ($result, $item) use ($allowedLocales) {
            $result[$item] = $this->getTranslations($item, $allowedLocales);

            return $result;
        });
    }

    public function setTranslation(string $key, string $locale, $value): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        $translations = $this->getTranslations($key);

        $oldValue = $translations[$locale] ?? '';

        if ($this->hasSetMutator($key)) {
            $method = 'set'.Str::studly($key).'Attribute';

            $this->{$method}($value, $locale);

            $value = $this->attributes[$key];
        } elseif($this->hasAttributeSetMutator($key)) { // handle new attribute mutator
            $this->setAttributeMarkedMutatedAttributeValue($key, $value);

            $value = $this->attributes[$key];
        }

        $translations[$locale] = $value;

        $this->attributes[$key] = json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // event(new TranslationHasBeenSetEvent($this, $key, $locale, $oldValue, $value));

        return $this;
    }

    public function setTranslations(string $key, array $translations): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        if (! empty($translations)) {
            foreach ($translations as $locale => $translation) {
                $this->setTranslation($key, $locale, $translation);
            }
        } else {
            $this->attributes[$key] = $this->asJson([]);
        }

        return $this;
    }

    public function forgetTranslation(string $key, string $locale): self
    {
        $translations = $this->getTranslations($key);

        unset(
            $translations[$locale],
            $this->$key
        );

        $this->setTranslations($key, $translations);

        return $this;
    }

    public function forgetTranslations(string $key, bool $asNull = false): self
    {
        $this->guardAgainstNonTranslatableAttribute($key);

        collect($this->getTranslatedLocales($key))->each(function (string $locale) use ($key) {
            $this->forgetTranslation($key, $locale);
        });

        if ($asNull) {
            $this->attributes[$key] = null;
        }

        return $this;
    }

    public function forgetAllTranslations(string $locale): self
    {
        collect($this->getTranslatableAttributes())->each(function (string $attribute) use ($locale) {
            $this->forgetTranslation($attribute, $locale);
        });

        return $this;
    }

    public function getTranslatedLocales(string $key): array
    {
        return array_keys($this->getTranslations($key));
    }

    public function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->getTranslatableAttributes());
    }

    public function hasTranslation(string $key, string $locale = null): bool
    {
        $locale = $locale ?: $this->getLocale();

        return isset($this->getTranslations($key)[$locale]);
    }

    public function replaceTranslations(string $key, array $translations): self
    {
        foreach ($this->getTranslatedLocales($key) as $locale) {
            $this->forgetTranslation($key, $locale);
        }

        $this->setTranslations($key, $translations);

        return $this;
    }

    protected function guardAgainstNonTranslatableAttribute(string $key): void
    {
//        if (! $this->isTranslatableAttribute($key)) {
//            throw AttributeIsNotTranslatable::make($key, $this);
//        }
    }

    protected function normalizeLocale(string $key, string $locale, bool $useFallbackLocale): string
    {
        $translatedLocales = $this->getTranslatedLocales($key);

        if (in_array($locale, $translatedLocales)) {
            return $locale;
        }

        if (! $useFallbackLocale) {
            return $locale;
        }

        if (method_exists($this, 'getFallbackLocale')) {
            $fallbackLocale = $this->getFallbackLocale();
        }

        $fallbackConfig = app(Translatable::class);

        $fallbackLocale ??= $fallbackConfig->fallbackLocale ?? config('app.fallback_locale');

        if (! is_null($fallbackLocale) && in_array($fallbackLocale, $translatedLocales)) {
            return $fallbackLocale;
        }

        if (! empty($translatedLocales) && $fallbackConfig->fallbackAny) {
            return $translatedLocales[0];
        }

        return $locale;
    }

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



    public function setLocale(string $locale): self
    {
        $this->translationLocale = $locale;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->translationLocale ?: config('app.locale');
    }

    /**
     * ---------------------------------------
     * 多语言字段
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

    public function translations(): Attribute
    {
        return Attribute::get(function () {
            return collect($this->getTranslatableAttributes())
                ->mapWithKeys(function (string $key) {
                    return [$key => $this->getTranslations($key)];
                })
                ->toArray();
        });
    }

    /**
     * ---------------------------------------
     * 获取映射数组, 将 多语言字段 映射成array,
     * 会使用 HasAttributes->castAttribute($key, $value) 执行return $this->fromJson($value);
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
     * Decode the given JSON back into an array or object.
     * 重写模型的 HasAttributes->fromJson() 方法, 用以处理json时,如果非json格式, 那么返回原数据
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





    public function locales(): array
    {
        return array_unique(
            array_reduce($this->getTranslatableAttributes(), function ($result, $item) {
                return array_merge($result, $this->getTranslatedLocales($item));
            }, [])
        );
    }

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

    // ===== sql条件查询: 查询指定列,的语言 ================================

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
