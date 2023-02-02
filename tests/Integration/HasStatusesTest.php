<?php

namespace OpenSoutheners\LaravelModelStatus\Tests\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapped;
use OpenSoutheners\LaravelModelStatus\Events\StatusSwapping;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\Comment;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\CommentStatus;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\Post;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\PostStatus;
use Orchestra\Testbench\TestCase;

/**
 * @group integration
 */
class HasStatusesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database');
    }

    public function testSetStatusSavesToDatabaseWhenIndicated()
    {
        $post = new Post();

        $post->title = 'Hello world';
        $post->content = 'The typical lorem ipsum...';

        $this->assertTrue($post->setStatus(PostStatus::Draft, true));
        $this->assertFalse($post->isDirty());
        $this->assertTrue($post->status === PostStatus::Draft);

        $this->assertDatabaseHas($post->getTable(), [
            'id' => $post->id,
            'status' => PostStatus::Draft->value,
        ]);
    }

    public function testSetStatusWhenSavesToDatabaseWhenIndicated()
    {
        $post = new Post();

        $post->title = 'Hello world';
        $post->content = 'The typical lorem ipsum...';
        
        $post->setStatus(PostStatus::Draft, true);

        $post->setStatusWhen(PostStatus::Draft, PostStatus::Published, true);

        $this->assertFalse($post->isDirty());
        $this->assertTrue($post->status === PostStatus::Published);

        $this->assertDatabaseHas($post->getTable(), [
            'id' => $post->id,
            'status' => PostStatus::Published->value,
        ]);
    }

    public function testSetStatusWhenDoesNotTriggerAnyEventWhenDisabledByModel()
    {
        $post = new Comment();
        
        $post->content = 'The typical lorem ipsum...';
        
        $post->setStatus(CommentStatus::Active, true);

        Event::fake();
        
        $post->setStatusWhen(CommentStatus::Active, CommentStatus::Spam, true);

        Event::assertNotDispatched(StatusSwapped::class);
    }

    public function testSetStatusWhenDoesNotTriggerAnyEventWhenDisabled()
    {
        $post = new Post();

        $post->title = 'Hello world';
        $post->content = 'The typical lorem ipsum...';
        
        $post->setStatus(PostStatus::Draft, true);

        Event::fake([StatusSwapping::class, StatusSwapped::class]);

        Post::withoutStatusEvents(fn () => $post->setStatusWhen(PostStatus::Draft, PostStatus::Published, true));

        Event::assertNotDispatched(StatusSwapping::class);
        Event::assertNotDispatched(StatusSwapped::class);
    }

    public function testSetStatusWithoutSavingWhenTriggersStatusSwappingEvent()
    {
        $post = new Post();

        $post->title = 'Hello world';
        $post->content = 'The typical lorem ipsum...';
        
        $post->setStatus(PostStatus::Draft, true);

        Event::fake(StatusSwapping::class);
        
        $post->setStatusWhen(PostStatus::Draft, PostStatus::Published, true);

        Event::assertDispatched(StatusSwapping::class, fn (StatusSwapping $event) =>
            get_class($event->model) === Post::class
                && $event->actual === PostStatus::Published
                && $event->previous === PostStatus::Draft
        );
    }

    public function testSetStatusWithSavingWhenTriggersStatusSwappedEvent()
    {
        $post = new Post();

        $post->title = 'Hello world';
        $post->content = 'The typical lorem ipsum...';
        
        $post->setStatus(PostStatus::Draft, true);

        Event::fake(StatusSwapped::class);
        
        $post->setStatusWhen(PostStatus::Draft, PostStatus::Published, true);

        Event::assertDispatched(StatusSwapped::class, fn (StatusSwapped $event) =>
            get_class($event->model) === Post::class
                && $event->actual === PostStatus::Published
                && $event->previous === PostStatus::Draft
        );
    }

    public function testOfStatusScopeReturnModelsWithSpecifiedStatus()
    {
        $firstComment = new Comment(['content' => 'lorem ipsum']);
        $firstComment->setStatus(CommentStatus::Active, true);

        $secondComment = new Comment(['content' => 'lorem ipsum']);
        $secondComment->setStatus(CommentStatus::Active, true);

        $thirdComment = new Comment(['content' => 'hello world']);
        $thirdComment->setStatus(CommentStatus::Spam, true);

        $comments = Comment::query()->ofStatus(CommentStatus::Active)->get();

        $this->assertTrue($comments->count() === 2);
        $this->assertTrue($comments->first()->id == $firstComment->id);
        $this->assertTrue($comments->last()->id == $secondComment->id);
    }

    public function testOfStatusesScopeReturnModelsWithSpecifiedStatuses()
    {
        $firstComment = new Comment(['content' => 'lorem ipsum']);
        $firstComment->setStatus(CommentStatus::Active, true);

        $secondComment = new Comment(['content' => 'lorem ipsum']);
        $secondComment->setStatus(CommentStatus::Active, true);

        $thirdComment = new Comment(['content' => 'hello world']);
        $thirdComment->setStatus(CommentStatus::Spam, true);

        $comments = Comment::query()->ofStatuses([
            CommentStatus::Active,
            CommentStatus::Spam,
        ])->get();

        $this->assertTrue($comments->count() === 3);
        $this->assertTrue($comments->first()->id == $firstComment->id);
        $this->assertTrue($comments->last()->id == $thirdComment->id);
    }

    public function testPostModelSaveSetsDefaultStatus()
    {
        $post = new Post(['title' => 'hello world', 'content' => 'hello']);

        $this->assertTrue($post->defaultStatus() === PostStatus::Draft);

        $post->save();

        $this->assertTrue($post->hasStatus(PostStatus::Draft));
    }
}
