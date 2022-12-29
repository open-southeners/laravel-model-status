<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use OpenSoutheners\LaravelModelStatus\ModelStatus;

enum CommentStatus implements ModelStatus
{
    case Active;

    case Spam;
}
