<?php


use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User as User;
use App\Account as Account;
use \App\Company as Company;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

         $this->call('TypeSeeder');
		 $this->call('UserTableSeeder');
        $this->call('CompaniesSeeder');
	}

}



class UserTableSeeder extends Seeder
{
    public function run()
    {

        DB::table('users')->delete();
        DB::table('accounts')->delete();
        $this->create_user("123456","123456", 2);
        $this->create_user("654321","654321", 2);

    }

    private function create_user($pass="123456",$name="123456", $type)
    {
        $user = new User();
        $user->user_login = $name;
        $user->user_password = Hash::make($pass);
        $user->user_type_ID = $type;
        $user->user_active = 1;


        if($user->save())
        {
            for($i =0;$i<2;$i++)
            {
                $account = new Account();
                $account->account_number = 'LV'.rand(100000000000000, 900000000000000).strtoupper(str_random(4));
                $account->account_user_ID = $user->user_ID;
                $account->account_balance = 1000;
                $account->save();
            }
        }
    }
}

class TypeSeeder extends Seeder
{

    public function run()
    {
        DB::table('types')->delete();
        DB::insert("INSERT INTO types (type) VALUES (?)",['Admin']);
        DB::insert("INSERT INTO types (type) VALUES (?)",['User']);
    }
}

class CompaniesSeeder extends Seeder
{
    public function run()
    {
        DB::table('companies')->delete();
        $company = new Company();
        $company->company_account_ID = 1;
        $company->public_key= "1234567890123456";
        $company->token1="123456";
        $company->token2="123456";
        $company->callback="www.linktoshop.dev/callback?payment=";
        $company->save();
    }
}