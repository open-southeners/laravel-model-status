<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OpenSoutheners\LaravelModelStatus\Tests\Fixtures\Post;

class CreateCommentsTestTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('status');
            // TODO: Probably not needed...
            // $table->foreignIdFor(Post::class);
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
