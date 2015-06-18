<?php namespace App\Http\Controllers;

use App\DefPayment;
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

	// Iegūst kontu izrakstu / transakcijas
	public function getAccountSummary($id){
		$t = Transaction::where('trans_account_ID_from', $id)->orWhere('trans_account_ID_to', $id)
				->join('accounts', function ($join){
					$join->on('transactions.trans_account_ID_from', '=' , 'accounts.account_ID')
					   ->orOn('transactions.trans_account_ID_to', '=' , 'accounts.account_ID');
			})
			->orderBy('trans_ID', 'asc')->get();
		return view('pages.summary')->with(['transactions' => $t, 'account' => Account::where('account_ID', $id)->first()]);
	}

	// Iegūst visus lietotāja kontus
	public function getAccounts($id){
		$accounts = Account::where('account_user_ID', $id)->get();
		return $accounts;
	}

    public function SavepaymentPost(){

        $messages = [
            'accountFrom.exists' => 'Norādītais maksātāja konts neeksistē',
            'accountTo.exists' => 'Norādītais saņēmēja konts neeksistē',
        ];

        $v = Validator::make(Input::all(), [
            'name' => 'required|max:25',
            'accountFrom'=>'required|exists:accounts,account_ID',
            'accountTo' => 'required|exists:accounts,account_ID',
        ],$messages);

        if($v->fails()){
            return redirect()->back()->withErrors($v);
        }

        $account_from=e(Input::get('accountFrom'));
        $account_to=e(Input::get('accountTo'));
        $name=e(Input::get('name'));

        if($account_from==$account_to){
            return redirect()->back()->withErrors('Nevar sūtīt naudu uz vienu un to pašu kontu');
        }

        $DefPayment=new DefPayment();
        $DefPayment->name = $name;
        $DefPayment->def_user_ID = Auth::user()->user_ID;
        $DefPayment->account_from =$account_from ;
        $DefPayment->account_to =$account_to ;

        if($DefPayment->save()){
            return redirect('/fastpayments')->with('success', 'Maksājums saglabāts.');
        }else{
            return redirect()->back()->withErrors('Neizdevās saglabāt maksājumu.');
        }

    }
    //atver definēto maksājumu ievades logu id norāda transakciju
    public function Savepayment($id){
        //erroru paziņojumi
        $messages = [
            'id.exists' => 'Norādītais maksājums neeksistē',
        ];
        //validācija pārbauda vai validācija ir pieejama
        $vTransactionID = Validator::make(['id'=>$id], [
            'id' => 'required|exists:transactions,trans_ID',
        ],$messages);

        if($vTransactionID->fails()){
            return redirect()->back()->withErrors($vTransactionID);
        }
        $Payment =Transaction::where('trans_ID',$id)->first();
        $data['From']= $Payment->trans_account_ID_from;
        $data['To']= $Payment->trans_account_ID_to;
        $data['Account_User_ID'] =Account::where('account_ID',$data['From'])->pluck('account_user_ID');
        $data['User_id']=Auth::user()->user_ID;

        //ota validatoraeroru paziņojumi
        $messagesDataValidation = [
            'From.exists' => 'Norādītais maksātāja konts neeksistē',
            'To.exists' => 'Norādītais saņēmēja konts neeksistē',
            'Account_User_ID.same' => 'Jums nav dotas tiesības darboties ar norādīto kontu',
        ];

        //pārbauda iegūto datu atbilstību klientam
        $vTransactionData = Validator::make($data, [
            'From' => 'required|exists:accounts,account_ID|integer',
            'To' => 'required|exists:accounts,account_ID|integer',
            'Account_User_ID' => 'required|same:User_id',
        ],$messagesDataValidation);

        if($vTransactionData->fails()){
            return redirect()->back()->withErrors($vTransactionData);
        }else{

            $data['From_account_number']= Account::where('account_ID',$data['From'])->pluck('account_number');
            $data['To_account_number']= Account::where('account_ID',$data['To'])->pluck('account_number');

            //pārbauda vai data masīvs ir atbilstoši nofrmēts ir 6 masīva ieraksti
            if(count($data)==6){
                return view('pages.Savepayment',['data'=>$data]);
            } else {
                return redirect()->back()->with('error', 'Dati nav pieejami.');
            }
        }
    }

    //Parāda definētos maksājumus
    public function fastpayments(){
        //atrod defineeto maksājumu DB tabulā  visus ierakstus kurus ir veicis lietotājs
        $defPayments=DefPayment::where('def_user_ID',Auth::user()->user_ID);
        $data=null;
        //pārbauda vai ir atrasti kādi ieraksti
        if( $defPayments->count()==0){
            return view('pages.fastpay',['data'=>$data]);
        }

        //datus ievieto pasīvā $data
        foreach($defPayments->get() as $def => $d){
            $data[$def]['id']=$d->def_payment_ID;
            $data[$def]['name']=$d->name;
            $data[$def]['account_from']=Account::where('account_ID',$d->account_from)->pluck('account_number');
            $data[$def]['account_to']=Account::where('account_ID',$d->account_to)->pluck('account_number');
        }

        return view('pages.fastpay',['data'=>$data]);

    }
    public function fastpaymentsPost(){

        $messages = [
            'id.exists' => 'Norādītais definētais maksājums neeksistē',
        ];
        //validācija pārbauda vai validācija ir pieejama
        $v = Validator::make(Input::all(), [
            'def_id' => 'required|exists:def_payments,def_payment_ID',
        ],$messages);

        if($v->fails()){
            return redirect('/fastpayments')->withErrors($v);
        }

        $def_payment_id=e(Input::get('def_id'));

        $defPayments_user_id=DefPayment::where('def_payment_ID',$def_payment_id)->pluck('def_user_ID');

        if($defPayments_user_id !=Auth::user()->user_ID){
            return redirect('/fastpayments')->withErrors('Jums nav tiesības dzēst izvēlēto definēto maksājumu');
        }else{
            DefPayment::where('def_payment_ID',$def_payment_id)->delete();
            return redirect('/fastpayments')->with('success','definētais maksājus ir izdzēsts!');
        }
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

		// Atgriež kontu un transakciju informāciju lapā
		return view('pages.transactions')->with([
			'accounts' => $accounts
		]);
	}

    //atveērs maksājumu logu ar konkrētā definētā maksājuma datiem
    public function transactionsDef($id){

        // Iegūst lietotāja kontus
        $accounts = $this->getAccounts(Auth::user()->user_ID);

        $messages = [
            'id.exists' => 'Norādītais definētais maksājums neeksistē',
        ];

        $v = Validator::make(['id'=>$id], [
            'id' => 'required|exists:def_payments,def_payment_ID',
        ],$messages);
        if($v->fails()){
            return view('pages.transactions')->with([
                'accounts' => $accounts
            ]);
        }else{
            //iegūst pirmo ierakstu ar norādītajiemdatiem
            $Def_payment_data=DefPayment::where('def_payment_ID',$id)->first();

            //pārbauda vaiiegūtais ierakasta dati atbilstlietotājam
            if($Def_payment_data->def_user_ID!=Auth::user()->user_ID){
                return view('pages.transactions')->with([
                    'accounts' => $accounts
                ]);
            }else{
                $data['From']=$Def_payment_data->account_from;
                $data['To']=Account::where('account_ID',$Def_payment_data->account_to)->pluck('account_number');
            }
        }
        // Atgriež kontu un transakciju informāciju lapā
        return view('pages.transactions')->with([
            'accounts' => $accounts,
            'data'=>$data
        ]);
    }

	// Izpilda maksājumu un izveido jaunu transakciju
	public function transactionsPost(){

		// Ievades pārbaude
		$vInput = Validator::make(Input::all(), [
			'trans_account_ID_from' => 'required|integer|exists:accounts,account_ID',
			'trans_account_number' => 'required|alpha_num|min:4',
			'trans_sum' => 'required|numeric|between:0.01,1000000',
			'trans_note' => 'required|min:1|max:255',
		]);

		if($vInput->fails()){
			return redirect()->back()->withErrors($vInput)->withInput();
		} else {

			$from = e(Input::get('trans_account_ID_from'));
			$to = e(Input::get('trans_account_number'));
			$sum = e(Input::get('trans_sum'));
            $sum= floor($sum * 100) / 100;
			$note = e(Input::get('trans_note'));

			$get_to_account = Account::where('account_number', $to);
            $get_from_account = Account::where('account_ID', $from);

               // dd($get_to_account);

			// Pārbauda vai saņēmēja konts vispār eksistē
			if($get_to_account->count() > 0){
				$to = $get_to_account->first()->account_ID;
                $account_balance= $get_from_account->first()->account_balance;
                $from = $get_from_account->first()->account_ID;
                $FromUserID= $get_from_account->first()->account_user_ID;

                //Pārbauda vai lietotāja izvēlētais maksātāja konts pieder lietotājma
                if($FromUserID!=Auth::user()->user_ID){
                    return redirect()->back()->withErrors('Jums nav dotas tiesības rīkoties ar šo kontu!');
                }
                //
                if($account_balance < $sum)
                {
                    return redirect()->back()->withErrors('Konta bilance ir par mazu');
                }
                //Pārbauda vai naudu nesūta uzvienu un to pašu kontu
                if($from==$to){
                    return redirect()->back()->withErrors('Maksājumu veikt uz vienu un to pašu kontu, nav atļauts!');
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
		$data['account_from'] = 'LV1770256547D2D5'; // ŠEIT MAKSĀTĀJS NORĀDA SAVU KONTU (maksātājs ievada)
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

                    Auth::logout();
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
