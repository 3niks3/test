<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DefPayment extends Model {

    protected $primaryKey = 'def_payment_ID';
    protected $table = 'def_payments';

protected $fillable = ['def_user_ID', 'name', 'account_from', 'account_to'];

}
