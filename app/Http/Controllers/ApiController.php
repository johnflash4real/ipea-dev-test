<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
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
     * @return Response
     */

    public function moduleReminderAssigner(Request $request, InfusionsoftHelper $infusionsoftHelper){

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


        $tagToAdd = $this->getTagForModule($nextModule);








        return response()->json(['success'=>true,'message'=>'ok','user'=>$user,'next'=>$nextModule,'tag'=>$tagToAdd]);
    }


    /**
     * Get start reminder tag for specific module or when no module
     * @param Module $module
     * @return Tag
     */

    private function getTagForModule(Module $module=null){
        //build proper tag slug
        if($module) $tagSlug = "start-{$module->course_key}-module-{$module->position}-reminders";
        else $tagSlug = "module-reminders-completed";

        $this->preloadTags(); //ensure we have tags on our db
        $theTag = Tag::where('slug',$tagSlug)->first();
        return $theTag;
    }



    /**
     * Check db for tags or fetch from api then save in db
     *
     */

    private function preloadTags(){
        if(Tag::all()->count()==0){
            //if no tag in db, load into db from infusionsoft
            $infusionsoftHelper = new InfusionsoftHelper();
            $allTags = $infusionsoftHelper->getAllTags()->all();
            $tagsArray = [];

            foreach ($allTags as $tag)
                $tagsArray[] = [
                    'tag_id'=>$tag->id,
                    'name'=>$tag->name,
                    'slug'=>str_slug($tag->name) //give each tag a slug for easily finding it later
                ];

            Tag::insert($tagsArray);
        }
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
