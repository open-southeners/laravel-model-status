<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelModelStatus\HasStatuses;
use OpenSoutheners\LaravelModelStatus\Statusable;

class Tag extends Model implements Statusable
{
    use HasStatuses;
}
