<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Services\TagService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\User;
use \App\Module;
use Response;

class ApiController extends Controller
{
    // Todo: Module reminder assigner

    protected $tagService; //For everything tags
    protected $infusionSoftHelper;


    public function __construct(TagService $tagService, InfusionsoftHelper $infusionsoftHelper)
    {
        $this->tagService = $tagService;
        $this->infusionSoftHelper = $infusionsoftHelper;
    }

    /**
     * Module reminder Assigner
     *
     * Assigns reminders to user profile based on module progress
     *
     * @param Request $request
     * @return Response
     */

    public function moduleReminderAssigner(Request $request){

        //validate contact email parameter
        //ideally, i would place this in another class, but lets keep things simple...

        $validator = Validator::make($request->all(), [
            'contact_email'=>'required|email'
        ]);
        if ($validator->fails()) return response()->json(['success'=>false,'message'=>$validator->errors()->get('contact_email')[0]],422);


        //get user with email or return failure response
        $userEmail = $request->input('contact_email');
        $user = User::where('email',$userEmail)->first();
        if(!$user) return response()->json(['success'=>false,'message'=>'user not found'],404);


        //get user's associated contact from infusion soft
        $contact = $this->infusionSoftHelper->getContact($userEmail);
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
        $tagToAdd = $this->tagService->getTagForModule($nextModule);
        $success = $this->infusionSoftHelper->addTag($contact['Id'],$tagToAdd->tag_id);


        return response()->json([
            'success'=>$success,
            'message'=>$success?"Tag '{$tagToAdd->name}' added to $userEmail successfully":"Could not add Tag '{$tagToAdd->name}' to $userEmail"
        ],201);

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

        $uniqid = uniqid();

        $this->infusionSoftHelper->createContact([
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
