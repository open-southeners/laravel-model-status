<?php

namespace OpenSoutheners\LaravelModelStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\Casts\Status;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapped;
use ReflectionAttribute;
use ReflectionClass;
use Exception;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasStatuses
{
    /**
     * @var class-string<\OpenSoutheners\LaravelModelStatus\ModelStatus>
     */
    protected static $statuses;

    /**
     * @var bool
     */
    protected static $statusesEvents;
    
    /**
     * @var \OpenSoutheners\LaravelModelStatus\ModelStatus
     */
    protected static $defaultStatus;

    /**
     * Boot class trait within model lifecycle.
     * 
     * @throws \Exception 
     * @return void
     */
    public static function bootHasStatuses()
    {
        $reflector = new ReflectionClass(self::class);

        $attributesArr = array_filter(
            $reflector->getAttributes(),
            fn (ReflectionAttribute $attribute) => $attribute->getName() === ModelStatuses::class
        );

        $attribute = reset($attributesArr);

        if (! $attribute) {
            throw new Exception('Model statuses must be setup, but there is none');
        }

        /** @var \OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses $attributeInstance */
        $attributeInstance = $attribute->newInstance();

        static::$statuses = $attributeInstance->enum;
        static::$defaultStatus = $attributeInstance->default;

        if (! isset(static::$statusesEvents)) {
            static::$statusesEvents = $attributeInstance->events;
        }

        if (static::$defaultStatus && static::$statusesEvents) {
            static::creating(fn (self $model) => $model->status = static::$defaultStatus);
        }
    }

    /**
     * Initialize trait within model instanciation.
     * 
     * @return void
     */
    public function initializeHasStatuses()
    {
        $this->mergeFillable(['status']);

        $this->mergeCasts(['status' => Status::class]);
    }

    /**
     * Run the action without triggering any event related to statuses.
     * 
     * @param \Closure $callback
     * @return mixed
     */
    public static function withoutStatusEvents(\Closure $callback)
    {
        static::$statusesEvents = false;

        $result = $callback();

        static::$statusesEvents = true;

        return $result;
    }

    /**
     * Get array of statuses cases from enum.
     * 
     * @return array<\OpenSoutheners\LaravelModelStatus\ModelStatus>
     */
    public function getAllStatuses(): array
    {
        return static::$statuses::cases();
    }

    /**
     * Get model default status.
     * 
     * @return \OpenSoutheners\LaravelModelStatus\ModelStatus|null
     */
    public function defaultStatus()
    {
        return static::$defaultStatus;
    }

    /**
     * Check model current status equals introduced one.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus|mixed $status
     * @return bool
     */
    public function hasStatus($status): bool
    {
        if (! is_object($status)) {
            $status = static::$statuses::tryFrom($status) ?? $status;
        }

        if (is_string($status)) {
            return $this->status->name === $status;
        }

        return $this->status === $status;
    }

    /**
     * Set status from enum instance.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $status
     * @param bool|null $saving
     * @throws \Exception
     * @return self|bool
     */
    public function setStatus($status, bool $saving = false)
    {
        if (! $status instanceof static::$statuses) {
            throw new Exception('Model status is not of type '.static::$statuses);
        }

        $this->status = $status;

        if ($saving) {
            return $this->save();
        }

        return $this;
    }

    /**
     * Set status when given value matches current status.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $current
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $value
     * @param bool|null $saving
     * @throws \Exception 
     * @return self|bool
     */
    public function setStatusWhen($current, $value, bool $saving = false)
    {
        if ($current === $value) {
            throw new \ErrorException('Trying to set status when current is the same', 0, E_NOTICE);
        }

        if ($this->hasStatus($current)) {
            $result = $this->setStatus($value, $saving);

            $result = $saving ? $result : true;

            if (static::$statusesEvents && $result) {
                event(new StatusSwapped($this, $current, $value));
            }

            if ($saving) {
                return $result;
            }
        }

        return $this;
    }

    public function getStatusAttribute()
    {
        return static::$statuses::tryFrom($this->attributes['status'] ?? null);
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value->value ?? $value->name;
    }
    
    /**
     * Model list of available statuses
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function statuses(): Attribute
    {
        return Attribute::make(
            fn () => array_map(fn (ModelStatus $case) => $case->name, $this->getAllStatuses())
        );
    }

    /**
     * Query models by the specified status.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $status
     * @return void
     */
    public function scopeOfStatus(Builder $query, ModelStatus $status)
    {
        $query->where('status', $status->value ?? $status->name);
    }
}
