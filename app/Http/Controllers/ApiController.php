<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use App\Services\TagService;
use App\Services\ReminderAssignerService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use \App\User;
use \App\Module;
use Response;

class ApiController extends Controller
{


    protected $tagService; //For everything tags
    protected $infusionSoftHelper;
    protected $reminderAssignerService;


    public function __construct(
        TagService $tagService,
        InfusionsoftHelper $infusionsoftHelper,
        ReminderAssignerService $reminderAssignerService
    )

    {
        $this->tagService = $tagService;
        $this->infusionSoftHelper = $infusionsoftHelper;
        $this->reminderAssignerService = $reminderAssignerService;
    }

    /**
     * Module reminder Assigner
     *
     * Assigns reminders to user profile based on module progress
     *
     * @param Request $request
     * @return Response
     */

    //TODO: refactor to further trim down controller

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
        $contact = $this->infusionSoftHelper->getContact($user->email);

        //get user's next module based on contact data
        $nextModule = $this->reminderAssignerService->getUserNextModule($user,$contact);


        //finally add the tag
        $tagToAdd = $this->tagService->getTagForModule($nextModule);
        $success = $this->infusionSoftHelper->addTag($contact['Id'],$tagToAdd->tag_id);


        return response()->json([
            'success'=>$success,
            'message'=>$success?"Tag '{$tagToAdd->name}' added to $userEmail successfully":"Could not add Tag '{$tagToAdd->name}' to $userEmail"
        ],201);

    }






    //TODO: decide whether to leave or remove this method.

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
