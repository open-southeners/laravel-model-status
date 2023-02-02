<?php

namespace OpenSoutheners\LaravelModelStatus\Tests;

use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Testing\Fakes\EventFake;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\CommentStatus;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\Post;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\PostStatus;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\Tag;
use PHPUnit\Framework\TestCase;
use Exception;

class HasStatusesTest extends TestCase
{
    protected function setUp(): void
    {
        Container::getInstance()->bind('events', fn () => new EventFake(new Dispatcher()));
    }

    public function testStatusesAttributeReturnAllStatusNames()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->statuses));
        $this->assertTrue(in_array('Draft', $post->statuses));
    }

    public function testGetAllStatusesReturnCasesFromBackedEnum()
    {
        $post = new Post();

        $this->assertTrue(is_array($post->getAllStatuses()));
        $this->assertTrue(in_array(PostStatus::Draft, $post->getAllStatuses()));
    }

    public function testHasStatusReturnTrueWhenValueMatch()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->assertTrue($post->hasStatus(PostStatus::Draft));
        $this->assertTrue($post->hasStatus(1));
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

    public function testStatusAttributeCastingToArray()
    {
        $post = new Post();

        $post->setStatus(PostStatus::Draft);

        $this->assertArrayHasKey('status', $post->toArray());
        $this->assertIsString($post->toArray()['status']);
        $this->assertTrue($post->toArray()['status'] === PostStatus::Draft->name);
    }

    public function testStatusAttributeCastingToJson()
    {
        $post = new Post();

        $post->setStatus(PostStatus::Draft);

        $this->assertIsString(json_decode($post->toJson())->status);
        $this->assertTrue(json_decode($post->toJson())->status === PostStatus::Draft->name);
    }

    public function testSetStatusWhenOnModelThatMatchExactStatus()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        Post::withoutStatusEvents(fn () => $post->setStatusWhen(PostStatus::Draft, PostStatus::Published));

        $this->assertFalse($post->status === PostStatus::Draft);
        $this->assertTrue($post->status === PostStatus::Published);
    }

    public function testSetStatusWhenThrowExceptionWhenSameValuesAreIntroduced()
    {
        $post = new Post();

        $post->status = PostStatus::Draft;

        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage('Trying to set status when current is the same');

        Post::withoutStatusEvents(fn () => $post->setStatusWhen(PostStatus::Draft, PostStatus::Draft));
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

    public function testModelWithoutStatusesAttributeThrowException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Model statuses must be setup, but there is none');

        new Tag;
    }
}
