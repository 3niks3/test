<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Auth;
use App\Account;
use App\Transaction;
use Validator;
use Input;

class AccountController extends Controller {

	public function getAccounts($id){
		$accounts = Account::where('account_user_ID', $id)->get();
		return $accounts;
	}

	public function getTransactions($id){
		$transactions = Transaction::where('trans_account_ID_from', $id)->get();
		return $transactions;
	}

	public function account(){
		$accounts = $this->getAccounts(Auth::user()->user_ID);
		return view('pages.account')->with('accounts', $accounts);
	}

	public function transactions(){
		$accounts = $this->getAccounts(Auth::user()->user_ID);

		// paņem tikai 1.konta transakcijas :D , pagaidām..
		$first_account = $accounts->first();

		$transactions = $this->getTransactions($first_account->account_ID);

		return view('pages.transactions')->with([
			'accounts' => $accounts,
			'transactions' => $transactions
		]);
	}

	public function transactionsPost(){

		$v = Validator::make(Input::all(), [
			'trans_account_ID_from' => 'required|numeric',
			'trans_account_number' => 'required|alpha_num|min:4',
			'trans_sum' => 'required|numeric|between:1,1000000',
			'trans_note' => 'required|min:1|max:255',
		]);

		if($v->fails()){
			return redirect()->back()->withErrors($v)->withInput();
		} else {

			$from = e(Input::get('trans_account_ID_from'));
			$to = e(Input::get('trans_account_number'));
			$sum = e(Input::get('trans_sum'));
			$note = e(Input::get('trans_note'));

			$get_to_account = Account::where('account_number', $to);

			if($get_to_account->count() > 0){
				$to = $get_to_account->first()->account_ID;

				$transaction = new Transaction();
				$transaction->trans_account_ID_from = $from;
				$transaction->trans_account_ID_to = $to;
				$transaction->trans_sum = $sum;
				$transaction->trans_note = $note;

				$update_from = Account::find($from);
				$update_from->account_balance = $update_from->account_balance - $sum;

				$update_to = Account::find($to);
				$update_to->account_balance = $update_to->account_balance + $sum;

				if($transaction->save() && $update_from->save() && $update_to->save()){

					return redirect('/transactions')->with('success', 'Maksājums veiksmīgi izpildīts.');
				} else {
					return redirect()->back()->withErrors('Neizdevās saglabāt transakciju.');
				}

			} else {
				return redirect()->back()->withErrors('Saņēmēja konts neeksistē!');
			}

		}

		return redirect()->back()->withErrors('Kaut kas nogāja greizi...');
	}

}
