<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('from_id')->unsigned();
			$table->foreign('from_id')->references('id')->on('users')->onDelete('cascade');			
			$table->integer('to_id')->unsigned();
			$table->foreign('to_id')->references('id')->on('users')->onDelete('cascade');
			$table->text('message');
			$table->timestamp('created_at');
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('messages');
	}

}
