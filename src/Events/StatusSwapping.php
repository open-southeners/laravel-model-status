<?php

namespace OpenSoutheners\LaravelModelStatus\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use OpenSoutheners\LaravelModelStatus\ModelStatus;
use OpenSoutheners\LaravelModelStatus\Statusable;

class StatusSwapping
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Statusable $model, public ModelStatus $actual, public ?ModelStatus $previous = null)
    {
        //
    }
}
