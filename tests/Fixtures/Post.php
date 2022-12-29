<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\HasStatuses;
use OpenSoutheners\LaravelModelStatus\Statusable;

#[ModelStatuses(PostStatus::class, true)]
class Post extends Model implements Statusable
{
    use HasStatuses;
}
