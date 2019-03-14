<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModuleReminderAssignerTest extends TestCase
{
    /**
     * Test email required validation
     *
     * @return void
     */
    public function testWillValidateEmailRequired()
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
    public function testWillValidateEmailValid()
    {

        $this->post(route('module_reminder'),['contact_email'=>str_random(10)])
            ->assertStatus(422)
            ->assertJson(['success'=>false]);
    }


}
