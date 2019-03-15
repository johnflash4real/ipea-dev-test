<?php
/**
 * Created by PhpStorm.
 * User: JFlash
 * Date: 3/15/19
 * Time: 5:02 AM
 */

namespace App\Services;

use App\Module;
use App\User;

class ReminderAssignerService
{


    /**
     * Get user's next pending module
     *
     * @param User $user
     * @param array $contact
     * @return Module | null
     */
    public function getUserNextModule(User $user, array $contact){


        $contactCourses = explode(",",$contact['_Products']);

        //by default user has no pending module till one is found
        $nextModule = null;
        foreach ($contactCourses as $contactCourse ){
            $pendingModule = $user->getNextPendingModule($contactCourse);
            if($pendingModule){
                $nextModule = $pendingModule;
                break; //stop looking any further once a pending module is found
            }
        }

        return $nextModule;
    }

}
