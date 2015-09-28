<?php
 
use Illuminate\Database\Seeder;
use App\Message;
 
class MessagesTableSeeder extends Seeder {
 
    public function run()
    {
        // Uncomment the below to wipe the table clean before populating
        DB::table('messages')->delete();
 
    	Message::create(array(
    		'from_id'		=> 3,
        	'to_id'    		=> 2,
        	'message'   	=> 'We have the ring!',
        	'created_at' 	=> time(),
    	)); 
    	Message::create(array(
    		'from_id'		=> 2,
        	'to_id'    		=> 3,
        	'message'   	=> 'Nice job! Put it on and rule the world!',
        	'created_at' 	=> time(),
    	)); 
    	Message::create(array(
    		'from_id'		=> 4,
        	'to_id'    		=> 2,
        	'message'   	=> 'Rok, rok!',
        	'created_at' 	=> time(),
    	)); 
    	Message::create(array(
    		'from_id'		=> 2,
        	'to_id'    		=> 4,
        	'message'   	=> 'You shall not pass!',
        	'created_at' 	=> time(),
    	)); 
    }
 
}
