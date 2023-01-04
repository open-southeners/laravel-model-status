<?php

namespace OpenSoutheners\LaravelModelStatus\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ModelStatuses
{
    /**
     * Construct attribute instance.
     * 
     * @param class-string<\OpenSoutheners\LaravelModelStatus\ModelStatus> $enum
     * @param bool $events
     * @param \OpenSoutheners\LaravelModelStatus\ModelStatus|null $default
     */
    public function __construct(public string $enum, public bool $events = false, public $default = null)
    {
        // 
    }
}
