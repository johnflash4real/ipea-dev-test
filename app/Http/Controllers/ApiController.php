<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Services\TagService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\User;
use \App\Module;
use App\Tag;
use Response;

class ApiController extends Controller
{
    // Todo: Module reminder assigner

    /**
     * Module reminder Assigner
     *
     * Assigns reminders to user profile based on module progress
     *
     * @param Request $request
     * @param InfusionsoftHelper $infusionsoftHelper
     * @param TagService $tagService
     * @return Response
     */

    public function moduleReminderAssigner(Request $request, InfusionsoftHelper $infusionsoftHelper, TagService $tagService){

        //validate contact email parameter
        $request->validate([
            'contact_email'=>'required|email'
        ]);

        //get contact with email or return failure response
        $userEmail = $request->input('contact_email');
        $user = User::where('email',$userEmail)->with('completed_modules')->first();
        if(!$user) return response()->json(['success'=>false,'message'=>'user not found'],404);


        //get user's associated contact from infusion soft
        $contact = $infusionsoftHelper->getContact($userEmail);
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


        //finally add the tag
        $tagToAdd = $tagService->getTagForModule($nextModule);
        $success = $infusionsoftHelper->addTag($contact['Id'],$tagToAdd->tag_id);


        return response()->json([
            'success'=>$success,
            'message'=>$success?"Tag {$tagToAdd->name} added to $userEmail successfully":"Adding Tag {$tagToAdd->name} to $userEmail failed"
        ]);
    }





    /**
     * create fake user utilizing helper function
     * @return Response
     */

    public function createFakeUser(){
        $user = $this->exampleCustomer();
        return $user;
    }

    private function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());


        return $user;
    }
}
