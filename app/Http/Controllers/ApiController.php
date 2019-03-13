<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;

class ApiController extends Controller
{
    // Todo: Module reminder assigner

    /**
     * Module reminder Assigner => Assigns reminders to user profile based on module progress
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





        return response()->json(['success'=>true,'message'=>'ok','contact'=>$userEmail]);
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
