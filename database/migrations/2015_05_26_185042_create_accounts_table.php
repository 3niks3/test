<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('accounts', function($table)
		{
			$table->increments('account_ID');
			$table->string('account_number', 21);
			$table->integer('account_user_ID')->unsigned();
			$table->double('account_balance', 15, 2);
			
			// created_at, updated_at DATETIME
			$table->timestamps();
			
			// create keys
			$table->foreign('account_user_ID')->references('user_ID')->on('users');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('accounts');
	}

}
