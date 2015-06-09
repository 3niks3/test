<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function($table)
		{
			$table->increments('trans_ID');
			$table->integer('trans_account_ID_from')->unsigned();
			$table->integer('trans_account_ID_to')->unsigned();
			$table->double('trans_sum', 15, 2);
			$table->timestamp('trans_timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));;
			$table->string('trans_note', 255);
			
			// created_at, updated_at DATETIME
			$table->timestamps();
			
			// create keys
			$table->foreign('trans_account_ID_from')->references('account_ID')->on('accounts');
			$table->foreign('trans_account_ID_to')->references('account_ID')->on('accounts');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('transactions');
	}

}
