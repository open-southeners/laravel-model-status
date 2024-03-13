<?php

namespace OpenSoutheners\LaravelModelStatus;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\Casts\StatusEnumCaseName;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapped;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapping;
use ReflectionAttribute;
use ReflectionClass;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property-read string[] $statuses
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
     * @return void
     *
     * @throws \Exception
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
            static::creating(function (self $model) {
                $model->status = static::$defaultStatus;
            });
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

        $this->mergeCasts(['status' => StatusEnumCaseName::class]);

        if (static::$statusesEvents) {
            $observableEvents = [];

            foreach ($this->statuses as $status) {
                $observableEvents[] = "swapping{$status}";
                $observableEvents[] = "swapped{$status}";
            }

            $this->addObservableEvents($observableEvents);
        }
    }

    /**
     * Run the action without triggering any event related to statuses.
     *
     * @return mixed
     */
    public static function withoutStatusEvents(Closure $callback)
    {
        static::$statusesEvents = false;

        return tap($callback(), fn () => static::$statusesEvents = true);
    }

    public static function swappedStatus(Closure $callback, ?ModelStatus $status = null)
    {
        static::registerModelEvent("swapped{$status?->name}", $callback);
    }

    public static function swappingStatus(Closure $callback, ?ModelStatus $status = null)
    {
        static::registerModelEvent("swapping{$status?->name}", $callback);
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
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus|mixed  $status
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
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $status
     * @param  bool|null  $saving
     * @return bool
     *
     * @throws \Exception
     */
    public function setStatus($status, bool $saving = false)
    {
        if (! $status instanceof static::$statuses) {
            throw new Exception('Model status is not of type '.static::$statuses);
        }

        $this->status = $status;

        $saving &= $this->fireSwappingStatusModelEvents($previousStatus = $this->getOriginal('status'));

        if ($saving) {
            return tap($this->save(), fn ($saveResult) => $saveResult && $this->fireSwappedStatusModelEvents($previousStatus));
        }

        return true;
    }

    /**
     * Fire swapping status custom Eloquent event.
     */
    protected function fireSwappingStatusModelEvents(?ModelStatus $previousStatus = null): bool
    {
        if (! static::$statusesEvents) {
            return true;
        }

        if ($previousStatus === $this->status) {
            return false;
        }

        $untilResult = $this->fireModelEvent("swapping{$this->status->name}");

        event(new StatusSwapping($this, $this->status, $previousStatus));

        return $untilResult !== false;
    }

    /**
     * Fire status swapped custom Eloquent event.
     */
    protected function fireSwappedStatusModelEvents(?ModelStatus $previousStatus = null): void
    {
        if (static::$statusesEvents && $previousStatus !== $this->status) {
            $this->fireModelEvent("swapped{$this->status->name}", false);

            event(new StatusSwapped($this, $this->status, $previousStatus));
        }
    }

    /**
     * Set status when given value matches current status.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $current
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $value
     * @param  bool|null  $saving
     * @return bool
     *
     * @throws \Exception
     */
    public function setStatusWhen($current, $value, bool $saving = false)
    {
        if ($current === $value) {
            throw new \ErrorException('Trying to set status when current is the same', 0, E_NOTICE);
        }

        if (! $this->hasStatus($current)) {
            return false;
        }

        return $this->setStatus($value, $saving);
    }

    /**
     * Get status enum as attribute.
     *
     * @return \OpenSoutheners\LaravelModelStatus\ModelStatus|null
     */
    public function getStatusAttribute()
    {
        if (! ($this->attributes['status'] ?? null)) {
            return null;
        }

        return static::$statuses::tryFrom($this->attributes['status']);
    }

    /**
     * Set enum as status attribute.
     *
     * @param  \OpenSoutheners\LaravelModelStatus\ModelStatus  $value
     * @return void
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value->value ?? $value->name;
    }

    /**
     * Model list of available statuses
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
     * @return void
     */
    public function scopeOfStatus(Builder $query, ModelStatus $status)
    {
        $query->where($this->qualifyColumn('status'), $status->value ?? $status->name);
    }

    /**
     * Query models by the specified statuses.
     *
     * @param  array<\OpenSoutheners\LaravelModelStatus\ModelStatus>  $statuses
     * @return void
     */
    public function scopeOfStatuses(Builder $query, array $statuses)
    {
        $query->whereIn(
            $this->qualifyColumn('status'),
            array_map(fn (ModelStatus $statusCase) => $statusCase->value ?? $statusCase->name, $statuses)
        );
    }
}
