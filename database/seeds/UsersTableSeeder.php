<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder {

	public function run()
	{
		// Uncomment the below to wipe the table clean before populating
		DB::table('users')->delete();

		$users  = array(
			[
				'id'		=> 1,
				'name'     	=> 'Admin',
				'email'    	=> 'web-wiz@ya.ru',
				'password' 	=> Hash::make('123456'),
				'roles' 	=> 2
			],
			[
				'id'		=> 2,
				'name'     	=> 'Gendalf',
				'email'    	=> 'gendalf@wizards.org',
				'password' 	=> Hash::make('123456'),
				'roles' 	=> 1
			],
			[
				'id'		=> 3,
				'name'     	=> 'Hobbits',
				'email'    	=> 'hobit@shire.net',
				'password' 	=> Hash::make('123456'),
				'roles' 	=> 1
			],
			[
				'id'		=> 4,
				'name'     	=> 'Balrog',
				'email'    	=> 'balrog@moria.io',
				'password' 	=> Hash::make('123456'),
				'roles' 	=> 1
			]
		);
		
		DB::table('users')->insert($users);
	}

}
