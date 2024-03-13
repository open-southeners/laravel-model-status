<?php

namespace OpenSoutheners\LaravelModelStatus\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class StatusEnumCaseName implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\Statusable  $model
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     */
    public function get($model, string $key, $value, array $attributes): string
    {
        return ($model->status ?? $model->defaultStatus())?->name;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\Statusable  $model
     * @param  string  $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        return [$key => $value];
    }
}
