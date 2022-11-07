<?php

return [
	'messages'=>[
		'success'=>'Request Successful.',
		'failed' => 'Something went wrong',
		'incorrect_user' => 'incorrect email or password.'
	],
	'env_var' => [
		'callback_url' => env('PAYMENT_CALLBACK_URL'),
		'subscription_price_id' => env('SUBSCRIPTION_PRICE_ID'),
		'stripe_client' => env('STRIPE_CLIENT'),
		'success_callback' =>env('SUCCESS_CALLBACK'),
		'cancel_callback' =>env('CANCEL_CALLBACK'),
	]
];
