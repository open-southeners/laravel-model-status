<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use OpenSoutheners\LaravelModelStatus\ModelStatus;

enum PostStatus: int implements ModelStatus
{
    case Draft = 1;
    
    case Published = 2;

    case Hidden = 3;
}
