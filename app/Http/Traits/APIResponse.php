<?php
namespace App\Http\Traits;

trait APIResponse
{
	public function response($data,$is_error=false,$success = false,$message='')
	{
		$response = [];

		if ($success == false || $is_error){
			$status = 0;
		}else{
			$status = 1;
		}

        // this is dependent on how we want to show errors, currently sending all erorrs in the form of array
		if ($message === ''){
			if ($success){
				$message = [config('constants.messages.success')];
			}elseif($is_error){
				$message = [config('constants.messages.failed')];
			}
		}

		$response['status'] = $status;
		$response['message'] = $message;
		$response['data'] = $data;
		return response()->json($response);
	}
}
