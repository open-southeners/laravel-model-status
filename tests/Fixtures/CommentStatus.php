<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use OpenSoutheners\LaravelModelStatus\ModelStatus;

enum CommentStatus: int implements ModelStatus
{
    case Active = 1;

    case Spam = 2;
}
