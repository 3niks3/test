<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDefPaymentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('def_payments', function($table)
        {
            $table->increments('def_payment_ID');
            $table->integer('def_user_ID')->unasigned();
            $table->string('name',25);
            $table->integer('account_from');
            $table->integer('account_to');
            $table->timestamps();

            // create keys
            //$table->foreign('def_user_ID')->references('user_ID')->on('users');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('def_payments');
	}

}
