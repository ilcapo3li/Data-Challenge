<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_createUser_withMandatoryData()
    {
        $user = User::make([
            'name' => 'salla',
            'email' => 'a@b.c',
            'password' => bcrypt('123456')
        ]);

        $this->assertTrue(boolval($user));
    }

    public function test_factory_getUser()
    {
        User::factory()->count(3)->make();
        $user = User::first();
        $this->assertDatabaseHas('users', ['email' => $user->email]);
    }
}
