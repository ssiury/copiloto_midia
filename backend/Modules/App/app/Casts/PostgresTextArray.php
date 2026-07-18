<?php

namespace Modules\App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * Converte entre `array` (PHP) e o literal de array nativo do Postgres
 * (`{"a","b"}`) usado pela coluna `hashtags TEXT[]`.
 */
class PostgresTextArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value, '{}');

        if ($value === '') {
            return [];
        }

        return str_getcsv($value, ',', '"', '\\');
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $items = array_map(
            fn (string $item): string => '"'.str_replace('"', '\\"', $item).'"',
            $value,
        );

        return '{'.implode(',', $items).'}';
    }
}
