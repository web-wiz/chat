<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model 
{
	public $timestamps = false;
	
	public function from_user()
	{
		return $this->belongsTo('App\User', 'from_id', 'id');
	}

	public function to_user()
	{
		return $this->belongsTo('App\User', 'to_id', 'id');
	}
}
