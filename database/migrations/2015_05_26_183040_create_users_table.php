<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
			$table->increments('user_ID');
			$table->string('user_login', 6);
			$table->string('user_password', 60);
			$table->integer('user_type_ID')->unsigned();
			$table->boolean('user_active')->default(true);
			
			// created_at, updated_at DATETIME
			$table->timestamps();
			
			// create keys
			$table->foreign('user_type_ID')->references('type_ID')->on('types');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('users');
	}

}
