<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use Infusionsoft\FrameworkSupport\Laravel\InfusionsoftFacade;
use Storage;


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
     * Test if endpoint will assign IPA M6 when user has completed IPA Modules 1,2,3 and 5
     *
     * @return  void
     */

    public function tests_will_assign_ipa_m6_after_ipa_m1_m2_m3_m5()
    {

        $user = factory(\App\User::class)->create();
        $user->completed_modules()->attach([1,4,7,13]);

        $fields = [
            'Id',
            'Email',
            'Groups',
            "_Products"
        ];

        InfusionsoftFacade::shouldReceive('setToken')->once()->andReturnNull();

        InfusionsoftFacade::shouldReceive('contacts->findByEmail')
            ->once()->with($user->email,$fields)
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


}
