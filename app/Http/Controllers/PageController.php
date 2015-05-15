<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PageController extends Controller {

	public function index()
	{
		return view('pages.login');
    }

    public function menu()
    {
        $view = view('pages.main');
        return $view;
    }
}
