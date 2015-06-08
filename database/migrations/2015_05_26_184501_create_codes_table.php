<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('codes', function($table)
		{
			$table->increments('code_ID');
			$table->integer('code_user_ID')->unsigned();
			$table->integer('code_number');
			$table->string('code_code', 6);
			
			// created_at, updated_at DATETIME
			$table->timestamps();
			
			// create keys
			$table->foreign('code_user_ID')->references('user_ID')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('codes');
	}

}
