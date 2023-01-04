<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\HasStatuses;
use OpenSoutheners\LaravelModelStatus\Statusable;

#[ModelStatuses(PostStatus::class, true, PostStatus::Draft)]
class Post extends Model implements Statusable
{
    use HasStatuses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['title', 'content'];
}
