<?php

namespace OpenSoutheners\LaravelModelStatus\Events;

use OpenSoutheners\LaravelModelStatus\ModelStatus;
use OpenSoutheners\LaravelModelStatus\Statusable;

class StatusSwapped
{
    public function __construct(public Statusable $model, public ModelStatus $previous, public ModelStatus $actual)
    {
        // 
    }
}
