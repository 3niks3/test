<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model {

	protected $primaryKey = 'company_ID';
	protected $table = 'companies';

    protected $fillable = ['company_account_ID', 'public_key', 'token1', 'callback'];

}
