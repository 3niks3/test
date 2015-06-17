<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('login_history', function($table)
		{
			$table->increments('hist_ID');
			$table->integer('hist_user_ID')->unsigned();
			$table->timestamp('hist_timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->string('hist_IP');

			
			// create keys
			$table->foreign('hist_user_ID')->references('user_ID')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('login_history');
	}

}
