<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Account extends Model {

	protected $primaryKey = 'account_ID';
	protected $table = 'accounts';

	protected $fillable = ['account_number', 'account_user_ID', 'account_balance'];

}
