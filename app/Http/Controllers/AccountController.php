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

	// Iegūst visus lietotāja kontus
	public function getAccounts($id){
		$accounts = Account::where('account_user_ID', $id)->get();
		return $accounts;
	}

	// Iegūst visas izejošās un ienākošās konta transakcijas
	public function getTransactions($id){
		$transactions['out'] = Transaction::where('trans_account_ID_from', $id)->orderBy('trans_ID', 'asc')->get();
		$transactions['in'] = Transaction::where('trans_account_ID_to', $id)->orderBy('trans_ID', 'asc')->get();
		return $transactions;
	}

	// Kontu sadaļa
	public function account(){
		$accounts = $this->getAccounts(Auth::user()->user_ID);
		return view('pages.account')->with('accounts', $accounts);
	}

	// Maksājumu un Transakciju sadaļa
	public function transactions(){

		// Iegūst lietotāja kontus
		$accounts = $this->getAccounts(Auth::user()->user_ID);

		// Iegūst visu lietotāja kontu transakcijas
		$transactions['out'] = array();
		$transactions['in'] = array();
		foreach($accounts as $account => $a){
			$transactions['out'][] = $this->getTransactions($a['account_ID'])['out'];
			$transactions['in'][] = $this->getTransactions($a['account_ID'])['in'];
		}

		// Atgriež kontu un transakciju informāciju lapā
		return view('pages.transactions')->with([
			'accounts' => $accounts,
			'transactions' => $transactions
		]);
	}

	// Izpilda maksājumu un izveido jaunu transakciju
	public function transactionsPost(){

		// Ievades pārbaude
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
            $get_from_account = Account::where('account_ID', $from);

			// Pārbauda vai saņēmēja konts vispār eksistē
			if($get_to_account->count() > 0){
				$to = $get_to_account->first()->account_ID;
                $account_balance= $get_from_account->first()->account_balance;

                if($account_balance < $sum)
                {
                    return redirect()->back()->withErrors('Konta bilance ir par mazu');
                }
				// Izveido jaunu transakciju
				$transaction = new Transaction();
				$transaction->trans_account_ID_from = $from;
				$transaction->trans_account_ID_to = $to;
				$transaction->trans_sum = $sum;
				$transaction->trans_note = $note;

				// Atjauno konta bilanci maksātājam
				$update_from = Account::find($from);
				$update_from->account_balance = $update_from->account_balance - abs($sum);

				// Atjauno konta bilanci saņēmējam
				$update_to = Account::find($to);
				$update_to->account_balance = $update_to->account_balance + abs($sum);

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
