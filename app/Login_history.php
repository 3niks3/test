<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Login_history extends Model {

    protected $primaryKey = 'hist_ID';
    protected $table = 'login_history';
    public $timestamps= false;

    protected $fillable = ['hist_user_ID', 'hist_timestamp', 'hist_IP'];


}
