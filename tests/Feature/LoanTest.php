<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoanTest extends TestCase
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

    public function test_get_all_loans_for_user_with_token()
    {
        $response = $this->withToken('7|yXUiiSfMPXrjp2Gc6NDEJiUJqNVMR3pOJ5mBUBfQ',
        )->json('get','api/loan/all');

            $response->assertJsonStructure([
            'status',
            'message',
            'data'=>[
                '*' => [
                    'id',
                    'total_loan_amount',
                    'loan_status',
                    'loan_terms'
                ]
            ]

        ]);
    }

    public function test_get_all_loans_for_user_without_token()
    {
        $response = $this->withoutToken()->json('get','api/loan/all');
        $response->assertUnauthorized();
    }

    public function test_create_loan_for_user_with_token_no_error()
    {
        $response = $this->withToken('7|yXUiiSfMPXrjp2Gc6NDEJiUJqNVMR3pOJ5mBUBfQ',
        )->json('post','api/loan/create',[
            'total_loan_amount' => 4000,
            'terms' => 4
        ]);

        $response->assertStatus(200)->assertJsonPath('status',1);

    }

    public function test_create_loan_for_user_without_token()
    {
        $response = $this->withoutToken()->json('post','api/loan/create');
        $response->assertUnauthorized();
    }
    public function test_approve_loans_for_user_without_token()
    {
        $response = $this->withoutToken()->json('post','api/loan/approve-loan');
        $response->assertUnauthorized();
    }
    public function test_repay_loans_for_user_without_token()
    {
        $response = $this->withoutToken()->json('post','api/loan/repay-loan');
        $response->assertUnauthorized();
    }

}
