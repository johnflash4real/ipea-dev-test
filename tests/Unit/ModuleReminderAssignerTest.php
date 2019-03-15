<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;


use Infusionsoft\FrameworkSupport\Laravel\InfusionsoftFacade;
use App\Module;



class ModuleReminderAssignerTest extends TestCase
{
    //use RefreshDatabase;
    use DatabaseMigrations;

    //setup db for testing
    public function setUp() {
        parent::setUp();

        //$this->runDatabaseMigrations(); //run migrations
        $this->artisan('db:seed');
    }


    //<------------start helpers and re-usable props------->

    //hold contact fields for re-use in InfusionSoftFacade Mocks
    private $contactFields = [
        'Id',
        'Email',
        'Groups',
        "_Products"
    ];



    /**
     * Helper function to get IDs of modules to be attached as completed
     *
     * @param String $courseKey
     * @param array $moduleNos
     * @return array
     */

    private function getModuleIds(String $courseKey, array $moduleNos){
        return Module::where('course_key',$courseKey)->whereIn('position',$moduleNos)->pluck('id')->toArray();
    }

    /**
     * Helper to init mockers
     * @param User $user
     * @param int $expectedTagId
     */
    private function initMockers($user,$expectedTagId){

        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                [
                    "Email"=> $user->email,
                    "_Products"=>"ipa,iea",
                    "Id"=> 1234
                ]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,$expectedTagId)
            ->andReturn(true);
    }

    //<-----------end helpers and props------------->


    //------------actual tests start here----------

    /**
     * Test email required validation
     *
     * @return void
     */
    public function tests_will_validate_email_required()
    {

        $this->post(route('module_reminder'),[''])
            ->assertStatus(422)
            ->assertJson(['success'=>false]);
    }

    /**
     * Test email format validation
     *
     * @return void
     */
    public function tests_will_validate_email_format()
    {

        $this->post(route('module_reminder'),['contact_email'=>str_random(10)])
            ->assertStatus(422)
            ->assertJson(['success'=>false]);
    }

    /**
     * Test if endpoint will assign IPA Module 1 when user has zero completed modules
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m1_when_zero()
    {

        $user = factory(\App\User::class)->create();

        $this->initMockers($user,110);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);

    }

    /**
     * Test if endpoint will assign IPA Module 2 when user has completed IPA Module 1
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m2_after_ipa_m1()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);

        $this->initMockers($user,112);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);

    }


    /**
     * Test if endpoint will assign IPA Module 4 when user has completed IPA Modules 1,2 and 3
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m4_after_ipa_m1_m2_m3()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1,2,3]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,116);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }


    /**
     * Test if endpoint will assign IPA Module 6 when user has completed IPA Modules 1,2,3 and 5
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m6_after_ipa_m1_m2_m3_m5()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1,2,3,5]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);

        $this->initMockers($user,120);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }

    /**
     * Test if endpoint will assign IPA Module 7 when user has completed IPA Modules 1,2,3, 5 and 6
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m7_after_ipa_m1_m2_m3_m5_m6()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1,2,3,5,6]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,122);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }


    /**
     * Test if endpoint will assign IEA Module 1 when user has completed IPA Modules 1,2,3, 5 , 6 and 7
     *
     * @return  void
     */

    public function tests_will_assign_iea_m1_after_ipa_m1_m2_m3_m5_m6_m7()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1,2,3,5,6,7]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,124);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }

    /**
     * Test if endpoint will assign IEA Module 2 when user has completed IEA Module 1 and IPA M1-M7
     *
     * @return  void
     */


    public function tests_will_assign_iea_m2_after_iea_m1_ipa_m1_to_m7()
    {

        $completedModuleIds = array_merge(
            $this->getModuleIds("ipa",[1,2,3,4,6,7]),
            $this->getModuleIds("iea",[1])
        );

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,126);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }

    /**
     * Test if endpoint will assign IEA Module 6 when user has completed IEA Module 1,2,5 and IPA M1-M7
     *
     * @return  void
     */


    public function tests_will_assign_iea_m6_after_iea_m1_m2_m5_ipa_m1_to_m7()
    {

        $completedModuleIds = array_merge(
            $this->getModuleIds("ipa",[1,2,3,4,6,7]),
            $this->getModuleIds("iea",[1,2,5])
        );

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,134);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }

    /**
     * Test if endpoint will assign Modules Reminder Completed when user has completed IEA Module 1,2,5,7 and IPA M1-M7
     *
     * @return  void
     */


    public function tests_will_assign_complete_after_iea_m1_m2_m5_m7_ipa_m1_to_m7()
    {

        $completedModuleIds = array_merge(
            $this->getModuleIds("ipa",[1,2,3,4,6,7]),
            $this->getModuleIds("iea",[1,2,5,7])
        );

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        $this->initMockers($user,154);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }


}
