<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model {

	protected $primaryKey = 'trans_ID';
	protected $table = 'transactions';

	protected $fillable = ['trans_account_ID_from', 'trans_account_ID_to', 'trans_sum', 'trans_timestamp', 'trans_note'];
}
