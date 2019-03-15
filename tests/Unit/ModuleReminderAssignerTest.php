<?php

namespace Tests\Unit;

use PhpParser\Node\Expr\Array_;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
     * Test if endpoint will assign IPA Module 2 when user has completed IPA Module 1
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m2_after_ipa_m1()
    {

        $completedModuleIds = $this->getModuleIds("ipa",[1]);

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach($completedModuleIds);


        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                ["Email"=> $user->email,
                    "_Products"=>"ipa,iea",
                    "Id"=> 1234]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,112)
            ->andReturn(true);

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


        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                ["Email"=> $user->email,
                "_Products"=>"ipa,iea",
                "Id"=> 1234]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,116)
            ->andReturn(true);

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

        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                ["Email"=> $user->email,
                    "_Products"=>"ipa,iea",
                    "Id"=> 1234]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,120)
            ->andReturn(true);

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


        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                ["Email"=> $user->email,
                    "_Products"=>"ipa,iea",
                    "Id"=> 1234]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,122)
            ->andReturn(true);

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


        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$this->contactFields)
            ->andReturn([
                ["Email"=> $user->email,
                    "_Products"=>"ipa,iea",
                    "Id"=> 1234]
            ]);

        InfusionsoftFacade::shouldReceive('contacts->addToGroup')
            ->once()
            ->with(1234,124)
            ->andReturn(true);

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
            ->with(1234,126)
            ->andReturn(true);

        $this->post(route('module_reminder'),['contact_email'=>$user->email])
            ->assertStatus(201)
            ->assertJson(['success'=>true]);



    }


}
