<?php

namespace OpenSoutheners\LaravelModelStatus\Tests;

use Illuminate\Database\Eloquent\Model;
use OpenSoutheners\LaravelModelStatus\Attributes\ModelStatuses;
use OpenSoutheners\LaravelModelStatus\HasStatuses;
use OpenSoutheners\LaravelModelStatus\ModelStatus;
use OpenSoutheners\LaravelModelStatus\Statusable;
use PHPUnit\Framework\TestCase;
use Exception;

class HasStatusesTest extends TestCase
{
    public function testStatusesAttributeFromModelInstanceReturnAllStatusNames()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->statuses));
        $this->assertTrue(in_array('Draft', $post->statuses));
    }

    public function testGetAllStatusesFromModelInstanceReturnAllStatusCasesFromEnum()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->getAllStatuses()));
        $this->assertTrue(in_array(PostStatus::Draft, $post->getAllStatuses()));
    }

    public function testHasStatusReturnsTrueWhenValueMatch()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->assertTrue($post->hasStatus(PostStatus::Draft));
        $this->assertTrue($post->hasStatus(1));
    }

    public function testHasStatusReturnsFalseWhenValueDoesNotMatch()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->assertFalse($post->hasStatus(PostStatus::Published));
        $this->assertFalse($post->hasStatus(2));
    }

    public function testSetStatusOnModelInstance()
    {
        $post = new Post();

        $post->setStatus(PostStatus::Draft);

        $this->assertTrue($post->status === PostStatus::Draft);
    }

    public function testSetStatusWhenOnModelThatMatchExactStatus()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $post->setStatusWhen(PostStatus::Draft, PostStatus::Published, false);

        $this->assertFalse($post->status === PostStatus::Draft);
        $this->assertTrue($post->status === PostStatus::Published);
    }

    public function testHasStatusReturnFalseWhenDifferentEnumGiven()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->assertFalse($post->hasStatus(CommentStatus::Active));
    }

    public function testSetStatusThrowExceptionWhenDifferentEnumGiven()
    {
        $post = new Post();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model status is not of type '.PostStatus::class);

        $post->setStatus(CommentStatus::Active);
    }
}

enum CommentStatus: int implements ModelStatus
{
    case Active = 1;

    case Spam = 2;
}

enum PostStatus: int implements ModelStatus
{
    case Draft = 1;
    
    case Published = 2;

    case Hidden = 3;
}

#[ModelStatuses(PostStatus::class)]
class Post extends Model implements Statusable
{
    use HasStatuses;
}
