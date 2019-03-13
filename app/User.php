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
     * Uses position column to determine next course as desired
     *
     * @param $courseKey
     * @return Module|Null
     */
    public function getNextPendingModule($courseKey){

        $lastCompletedPosition = 0;

        $completedCourseModules = $this->completed_modules()->where('course_key',$courseKey);
        if($completedCourseModules->count()>0)
            $lastCompletedPosition = $completedCourseModules->max('position');

        $pendingModules = \App\Module::where('course_key',$courseKey)
            ->where('position','>',$lastCompletedPosition)
            ->whereDoesntHave('users_completed',function ($query){
                $query->where('users.id',$this->id);
            })
            ->orderBy('position')->first();

        return $pendingModules;
    }
}
