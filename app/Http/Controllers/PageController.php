<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Login_history;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use Input;
use Hash;
use App\User;
use App\Account;
use Config;
use Auth;
use Session;

class PageController extends Controller {

	public function index()
	{
		return view('pages.main');
    }

    public function menu()
    {
        $view = view('pages.main');
        return $view;
    }

//	public function register()
//	{
//		$view = view('pages.register');
//		return $view;
//	}

	public function login()
	{
		$view = view('pages.login');
		return $view;
	}


//TO BE REMOVED
//	public function registerPost()
//	{
//
//		$v = Validator::make(Input::all(), [
//			'user_login' => 'required|numeric|between:1,999999|min:1|unique:users',
//			'user_password' => 'required|min:6|max:20',
//		]);
//
//		if($v->fails()){
//			return redirect()->back()->withErrors($v)->withInput();
//		} else {
//
//			$user_login = e(Input::get('user_login'));
//			$user_password = e(Input::get('user_password'));
//
//			$user = new User();
//			$user->user_login = $user_login;
//			$user->user_password = Hash::make($user_password);
//			$user->user_type_ID = 1;
//			$user->user_active = 1;
//
//			if($user->save()){
//
//				// Katram lietotājam izveido 2 kontus
//				$account = new Account();
//				$account->account_number = 'LV'.rand(100000000000000, 900000000000000).strtoupper(str_random(4));
//				$account->account_user_ID = $user->user_ID;
//				$account->account_balance = 1000;
//				$account->save();
//
//				$account = new Account();
//				$account->account_number = 'LV'.rand(100000000000000, 900000000000000).strtoupper(str_random(4));
//				$account->account_user_ID = $user->user_ID;
//				$account->account_balance = 1000;
//				$account->save();
//
//				return redirect('/')->with('success', 'Reģistrācija veiksmīga!');
//			}
//		}
//	}


	public function loginPost(Request $request)
	{
		//$user_login = e(Input::get('user_login'));
		//$user_password = e(Input::get('user_password'));
        $user_login= e($request->input('user_login'));
        $user_password= e($request->input('user_password'));


		if (Auth::attempt(['user_login' => $user_login, 'password' => $user_password])) {
            $history = new Login_history();
            $history->hist_user_ID=  Auth::id();
            $history->hist_timestamp= Carbon::now();
            $history->hist_IP = $request->ip();
            $history->save();
			return redirect()->intended('/account');
		} else {
			return redirect('/login')->withErrors('Autorizācija neveiksmīga.');
		}
	}

	public function logout(){
		//Auth::logout();
        Session::flush();

		return redirect('/')->with('success', 'Tu esi veiksmīgi izgājis no sistēmas..');
	}

}
