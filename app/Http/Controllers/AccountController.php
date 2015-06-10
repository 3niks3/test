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

	// šo funkciju var izmantot, lai testetu datus, kuri tiks nošifrēti
	// funkciju praktiski var COPY/PASTE tam, kurš izmantos mūsu sistēmu
	public function hash_openssl(){

		// jābūt precīzi šādā secībā
		$data['account_to'] = 'LV-16405533A4G0UP';
		$data['account_from'] = 'LV-1675967407ANED';
		$data['payment_sum'] = 23;
		$data['payment_id'] = 2355;

		// saliek visus datus vienā virknē
		$string = implode('_', $data);

		// $iv = TOKEN2
		// $pass = TOKEN1
		$iv = 'PSA1A3GV';
		$pass = 'QWED294A';

		// šifrēšanas metode
		$method = 'CAST5-CFB';

		// nošifrē datus
		$encrypt = openssl_encrypt($string, $method, $pass, false, $iv);

		// ārējiem klientiem nepieciešams pievienot $encrypt bankas linkam pašā galā: www.banka.lv/api?payment=$encrypt
		return $encrypt;
	}

	public function api(Request $request){

		if(Auth::check() && $request->has('payment')){

			$accounts = $this->getAccounts(Auth::user()->user_ID);

			$account_has_token = false;

			foreach($accounts as $account => $a){
				if($a->token1 != NULL && $a->token2 != NULL){
					$account_has_token = true;
					$token1 = $a->token1;
					$token2 = $a->token2;
					break;
				}
			}

			if($account_has_token == false){
				return redirect('/')->withErrors('Nevienam no kontiem nav API privilēģijas.');
			}

			$iv = $token2;
			$pass = $token1;
			$method = 'CAST5-CFB'; // HARD CODED IN

			$payment = openssl_decrypt($request->get('payment'), $method, $pass, false, $iv);

			if($payment){
				$data = explode('_', $payment);

				if(sizeof($data) != 4){
					return redirect('/')->withErrors('Maksājuma dati nekorekti..');
				}

				// lai vieglāk saprast, pieliekam nosaukumus, un nodzēšam 0,1,2,3 keyus.
				$data['account_to'] = $data[0];
				$data['account_from'] = $data[1];
				$data['payment_sum'] = (float)$data[2];
				$data['payment_id'] = (int)$data[3];
				unset($data[0]);
				unset($data[1]);
				unset($data[2]);
				unset($data[3]);

				// pārbauda, vai kāds no kontiem neeksistē
				if(Account::where('account_number', $data['account_to'])->count() == 0 || Account::where('account_number', $data['account_from'])->count() == 0){
					return redirect('/')->withErrors('Kontu dati ir nekorekti vai neeksistē.');
				}

				// drošības pēc, vēlreiz pārbauda ir sum un id ir float un integer.
				if(!is_float($data['payment_sum']) || !is_int($data['payment_id'])){
					return redirect('/')->withErrors('Nekorekta summa un/vai maksājuma ID.');
				}

				$account_to = Account::where('account_number', $data['account_to'])->firstOrFail()->account_ID;
				$account_from = Account::where('account_number', $data['account_from'])->firstOrFail()->account_ID;
				$payment_sum = $data['payment_sum'];
				$payment_id = $data['payment_id'];

				// Izveido jaunu transakciju
				$transaction = new Transaction();
				$transaction->trans_account_ID_to = $account_to;
				$transaction->trans_account_ID_from = $account_from;
				$transaction->trans_sum = $payment_sum;
				$transaction->trans_note = 'Rēķins #'.$payment_id;

				// Atjauno konta bilanci maksātājam
				$update_from = Account::find($account_from);
				$update_from->account_balance = $update_from->account_balance - abs($payment_sum);

				// Atjauno konta bilanci saņēmējam
				$update_to = Account::find($account_to);
				$update_to->account_balance = $update_to->account_balance + abs($payment_sum);

				if($transaction->save() && $update_from->save() && $update_to->save()){
					return redirect('/transactions')->with('success', 'Maksājums veiksmīgi izpildīts.');
				} else {
					return redirect('/')->withErrors('Neizdevās saglabāt transakciju.');
				}

			} else {
				return redirect('/')->withErrors('Maksājuma dati nav korekti.');
			}

		} else {
			return redirect('/');
		}


	}

}
