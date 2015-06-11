<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Auth;
use App\Account;
use App\Transaction;
use App\Company;
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

	/*
	 * Veikala API dati, ko sniedz Banka.
	 * public_key var publiskot.
	 * token1 un token2 jāuzglabā publiski nepieejamā vietā.
	*/
	protected $public_key = 'DH125F5HF1923461';
	protected $token1 = 'QWED294A';
	protected $token2 = 'PSA1A3GV';
	protected $bank_link = 'http://www.bank.dev';

	/*
	 * Ar šo funkciju atgriež maksājumu
	 * Jāņem vērā, ka Request $request ir specifiski Laravel, ja izmanto ārpus,
	 * tad jāizmanto citas iespējas , kā dabūt URL parametrus, piemēram $_GET['payment'], attiecīgi pielabojot kodu 2 rindiņās
	 * Visas pārējās pārbaudes veikala pusē.
	*/
	public function get_callback(Request $request){
		if($request->has('payment')){
			$token1 = $this->token1;
			$token2 = $this->token2;
			$payment = openssl_decrypt($request->get('payment'), 'CAST5-CFB', $token1, false, $token2);

			// Izvelk ārā datus no $payment virknes, ieliek masīvā
			$data = explode('_', $payment);
			$payment_id = abs((int)$data[0]); // Rēķina numurs.
			$public_key = $data[1]; // Publiskā atslēga

			// Pārbauda, vai callback sūtītā publiskā atslēga atbilst veikala publiskajai atslēgai,
			// vai sakrīt simbolu skaits, alpha_num, un rēķina ID nav 0
			if($public_key != $this->public_key || strlen($public_key) != 16 || ctype_alnum($public_key) == false || $payment_id == 0){

				// kļūdas novirza kā paši vēlas
				die('Nekorekti dati.');
			}

			return $payment_id;

		} else {
			die('Nav maksājums.');
		}
	}

	/*
	 * Izveido maksājumu, kuru nošifrē un aizsūta bankai.
	 * Izmanto dotos API datus (publisko atslēgu, tokenus)
	*/
	public function hash_openssl(){
		$public_key = $this->public_key;
		$token1 = $this->token1;
		$token2 = $this->token2;

		/* CLIENT EDIT */
		$data['account_from'] = 'LV791638102382422EHAW'; // ŠEIT MAKSĀTĀJS NORĀDA SAVU KONTU (maksātājs ievada)
		$data['payment_sum'] = 15.00;                 // ŠEIT NORĀDA PRECES CENU (nosaka veikals)
		$data['payment_id'] = 34;                  // ŠEIT NORĀDA RĒĶINA NR. (nosaka veikals)

		$string = implode('_', $data);
		$encrypt = openssl_encrypt($string, 'CAST5-CFB', $token1, false, $token2);

		$url = $this->bank_link.'/api?payment='.$encrypt.'&company='.$public_key;

		return $url;
	}

	public function api(Request $request){

		if(Auth::check() && $request->has('payment') && $request->has('company')){

			$public_key = $request->get('company');

			// pārbauda vai publiskā atslēgas garums ir 16, un pārbauda vai ir alpha_num
			if(strlen($public_key) != 16 || ctype_alnum($public_key) == false){
				return redirect('/')->withErrors('Uzņēmuma maksājuma dati ir nepareizi.');
			}

			// pārbauda vai eksistē uzņēmums ar konkrēto publisko atslēgu
			$count = Company::where('public_key', $public_key)->count();
			if(!$count){
				return redirect('/')->withErrors('Uzņēmuma dati ir nepareizi.');
			}

			$company = Company::where('public_key', $public_key)->first();

			// pārbauda vai uzņēmumam ir piešķirti tokeni
			$account_has_token = false;
			if($company->token1 != NULL && $company->token2 != NULL){
				$account_has_token = true;
				$token1 = $company->token1;
				$token2 = $company->token2;
			}

			if($account_has_token == false){
				return redirect('/')->withErrors('Uzņēmuma kontam nav API privilēģijas.');
			}

			// tiek atšķifrēts maksājums
			$payment = openssl_decrypt($request->get('payment'), 'CAST5-CFB', $token1, false, $token2);

			if($payment){
				$data = explode('_', $payment);

				if(sizeof($data) != 3){
					return redirect('/')->withErrors('Maksājuma dati nekorekti..');
				}

				// lai vieglāk saprast, pieliekam nosaukumus, un nodzēšam 0,1,2,3 keyus.
				$data['account_from'] = $data[0];
				$data['payment_sum'] = (float)$data[1];
				$data['payment_id'] = (int)$data[2];
				unset($data[0]);
				unset($data[1]);
				unset($data[2]);

				// pārbauda, vai sūtītāja konts eksistē
				if(Account::where('account_number', $data['account_from'])->count() == 0){
					return redirect('/')->withErrors('Sūtītāja konta dati ir nekorekti vai neeksistē.');
				}

				// drošības pēc, vēlreiz pārbauda ir sum un id ir float un integer.
				if(!is_float($data['payment_sum']) || !is_int($data['payment_id']) || $data['payment_sum'] == 0){
					return redirect('/')->withErrors('Nekorekta summa un/vai maksājuma ID.');
				}

				//$company_account = Account::where('account_ID', $company->company_account_ID)->first();

				$account_to = $company->company_account_ID;
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

					// sūtam callback atpakaļ uz veikalu, kur norādam maksājumu (payment_id), kurš ir samaksāts.
					// tālāk veikals pats domā, kā atzīmēt viņu pusē, ka maksājums izpildīts.
					// sūtam TIKAI maksājuma id, tas jebkurā gadījumā būs unikāls.
					// izmanto iepriekš nodoto public_key un tokenus.
					$encrypt = openssl_encrypt($data['payment_id'].'_'.$public_key, 'CAST5-CFB', $token1, false, $token2);

					$url = $company->callback.$encrypt;

					return redirect()->to($url);
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
