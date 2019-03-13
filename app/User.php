<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use phpDocumentor\Reflection\Types\Boolean;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function completed_modules()
    {
        return $this->belongsToMany('App\Module', 'user_completed_modules');
    }


    /**
     * Get the user's next un-completed module for selected course
     *
     * @param $courseKey
     * @return Module|Null
     */
    public function getNextPendingModule($courseKey){

        $pendingModules = \App\Module::where('course_key',$courseKey)->whereDoesntHave('users_completed',function ($query){
            $query->where('id',$this->id);
        })->orderBy('name')->first();

        return $pendingModules;
    }
}
