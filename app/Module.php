<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    //

    //users who have completed this module
    public function users_completed()
    {
        return $this->belongsToMany('App\User', 'user_completed_modules');
    }
}
