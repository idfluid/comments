<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommentTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comments', function($table)
		{
		    $table->increments('id');
		    $table->string('page', 250)->default(0);
		    $table->string('author', 50);
		    $table->string('author_email', 100);
		    $table->string('author_url', 200);
		    $table->string('author_ip', 20);
		    $table->timestamp('date')->default(DB::raw('CURRENT_TIMESTAMP'));
		    $table->text('comment');
		    $table->tinyInteger('status')->default(1);
		    $table->string('agent', 250);
		    $table->integer('parent')->default(0);
		    $table->integer('user_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
