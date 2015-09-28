<?php
 
use Illuminate\Database\Seeder;
use App\User;
 
class UsersTableSeeder extends Seeder {
 
    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        DB::table('users')->delete();
 
    	User::create(array(
        	'name'     	=> 'Admin',
        	'email'    	=> 'web-wiz@ya.ru',
        	'password' 	=> Hash::make('awesome'),
        	'roles' 	=> '2'
    	)); 
    }
 
}
