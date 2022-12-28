<?php

namespace OpenSoutheners\LaravelModelStatus;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapped;
use function OpenSoutheners\LaravelHelpers\Enums\enum_is_backed;
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
        static::$statusesEvents = $attributeInstance->events;
    }

    /**
     * Initialize trait within model instanciation.
     * 
     * @return void
     */
    public function initializeHasStatuses()
    {
        $this->mergeFillable(['status']);
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
     * Check model current status equals introduced.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus|mixed $status
     * @return bool
     */
    public function hasStatus($status): bool
    {
        if (enum_is_backed(static::$statuses) && ! is_object($status)) {
            $status = static::$statuses::tryFrom($status);
        }

        return $this->status === $status;
    }

    /**
     * Set status from enum instance.
     * 
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus $status
     * @param bool|null $saving
     * @throws \Exception 
     * @return $this
     */
    public function setStatus($status, bool $saving = false): self
    {
        if (! $status instanceof static::$statuses) {
            throw new Exception('Model status is not of type '.static::$statuses);
        }

        $this->status = $status;

        if ($saving) {
            $this->save();
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
     * @return $this
     */
    public function setStatusWhen($current, $value, bool $saving = true): self
    {
        if ($this->hasStatus($current)) {
            $this->setStatus($value, $saving);

            if (static::$statusesEvents) {
                event(new StatusSwapped($this, $current, $value));
            }
        }

        return $this;
    }

    /**
     * Model list of available statuses
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn ($status) => $status->name,
            set: fn ($status) => $status->value ?? $status->name
        );
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
    public function scopeOfStatus(Builder $query, $status)
    {
        $query->where('status', $status->value ?? $status->name);
    }
}
