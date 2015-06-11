<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{


		Schema::create('companies', function($table)
		{
			$table->increments('company_ID');
			$table->integer('company_account_ID')->unsigned();
			$table->string('public_key', 16);
			$table->string('token1', 8);
			$table->string('token2', 8);
			$table->string('callback', 255);
            $table->timestamps();

			// create keys
			$table->foreign('company_account_ID')->references('account_ID')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('companies');
	}

}
