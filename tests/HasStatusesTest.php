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
    public function testStatusesAttributeReturnAllStatusNames()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->statuses));
        $this->assertTrue(in_array('Draft', $post->statuses));
    }

    public function testStatusesAttributeOnUnitEnumReturnAllStatusNames()
    {
        $comment = new Comment();

        $this->assertTrue(is_array($comment->statuses));
        $this->assertTrue(in_array('Active', $comment->statuses));
    }

    public function testGetAllStatusesReturnCasesFromBackedEnum()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->getAllStatuses()));
        $this->assertTrue(in_array(PostStatus::Draft, $post->getAllStatuses()));
    }

    public function testGetAllStatusesReturnCasesFromUnitEnum()
    {
        $comment = new Comment();

        $this->assertTrue(is_array($comment->getAllStatuses()));
        $this->assertTrue(in_array(CommentStatus::Active, $comment->getAllStatuses()));
    }

    public function testHasStatusReturnTrueWhenValueMatch()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->assertTrue($post->hasStatus(PostStatus::Draft));
        $this->assertTrue($post->hasStatus(1));
    }

    public function testHasStatusReturnTrueWhenValueMatchOnUnitEnum()
    {
        $comment = new Comment();

        $comment->status = CommentStatus::Active;

        $this->assertTrue($comment->hasStatus(CommentStatus::Active));
        $this->assertTrue($comment->hasStatus('Active'));
    }

    public function testHasStatusReturnFalseWhenValueDoesNotMatch()
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

enum CommentStatus implements ModelStatus
{
    case Active;

    case Spam;
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

#[ModelStatuses(CommentStatus::class)]
class Comment extends Model implements Statusable
{
    use HasStatuses;
}
