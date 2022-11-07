<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_user_is_not_able_to_register_email_taken()
    {
        $response = $this->post(
            'api/auth/register',
            [
                'name'=>'test',
                'email'=>'test@mailinator.com',
                'password'=>'password',
            ]
        )->assertStatus(200)->assertJsonStructure([
            'status',
            'message' => [

            ],
            'data'=>[
            ]
        ]);
    }

    public function test_user_is_able_to_register()
    {
        $response = $this->post(
            'api/auth/register',
            [
                'name'=>'test',
                'email'=>"test".Str::random(12)."@mailinator.com",
                'password'=>'password',
            ]
        )->assertStatus(200)->assertJsonStructure([
            'status',
            'message' => [

            ],
            'data'=>[
                "token"
            ]
        ])->decodeResponseJson();
    }

    /**
     * @throws \Throwable
     */
    public function test_user_is_able_to_login()
    {
        $response = $this->post(
            'api/auth/login',
            [
                'name'=>'test',
                'email'=>'test@mailinator.com',
                'password'=>'password',
            ]
        );

        $response->assertStatus(200)->assertJsonStructure(
            [
                'status',
                'message'=>[],
                'data'=>[
                    'token',
                    'user_type'
                ]
            ],
        )->assertJsonPath('data.user_type', 'user');;

    }

    public function test_admin_user_is_able_to_login()
    {
        $response = $this->post(
            'api/auth/login',
            [
                'email'=>'veda16@example.net',
                'password'=>'password',
            ]
        );

        $response->assertStatus(200)->assertJsonStructure(
            [
                'status',
                'message'=>[],
                'data'=>[
                    'token',
                    'user_type'
                ]
            ],
        )->assertJsonPath('data.user_type', 'admin');;

    }

    public function test_user_is_not_able_to_login_validation_fails_password_required()
    {
        $response = $this->post(
            'api/auth/login',
            [
                'email'=>'veda16@example.net',
                'password'=>'',
            ]
        );

        $response->assertStatus(200)->assertJsonStructure(
            [
                'status',
                'message'=>[
                    0 => [
                        'password'
                    ]
                ],
                'data'=>[
                ]
            ],
        )->assertJsonPath('status',0);



    }

    public function test_user_is_not_able_to_login_email_password_does_not_match()
    {
        $response = $this->post(
            'api/auth/login',
            [
                'email'=>'veda16@example.net',
                'password'=>'passworddsfsd',
            ]
        );

        $response->assertStatus(200)->assertJsonStructure(
            [
                'status',
                'message'=>[

                ],
                'data'=>[
                ]
            ],
        )->assertJsonPath('message.0','Email and password does not match with our records.')->assertJsonPath('status',0);
    }

}
