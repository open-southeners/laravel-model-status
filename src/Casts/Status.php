<?php

namespace OpenSoutheners\LaravelModelStatus\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Status implements CastsAttributes
{
    /**
     * Cast the given value.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\Statusable $model
     * @param string $key
     * @param mixed $value
     * @param array<string, mixed>  $attributes
     * @return string
     */
    public function get($model, string $key, $value, array $attributes): string
    {
        return $model->status->name;
    }
 
    /**
     * Prepare the given value for storage.
     *
     * @param \OpenSoutheners\LaravelModelStatus\Statusable $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        return [$key => $value];
    }
}
