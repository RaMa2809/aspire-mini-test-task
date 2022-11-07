<?php
namespace App\Http\Controllers\Api;

use App\Http\Traits\APIResponse;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use APIResponse;
    /**
     * Create User
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(),
                [
                    'name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required'
                ]);

            if($validateUser->fails()){
                return $this->response([],true,false,[$validateUser->errors()]);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
                'role' => 'user'
            ]);

            return $this->response(['token' => $user->createToken("API TOKEN")->plainTextToken],false,true,['User Created Successfully']);

        } catch (\Throwable $th) {
            return $this->response([],true,false,[$th->getMessage()]);
        }
    }

    /**
     * Login The User
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]);

            if($validateUser->fails()){
                return $this->response([],true,false,[$validateUser->errors()]);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return $this->response([],true,false,['Email and password does not match with our records.']);
            }

            $user = User::where('email', $request->email)->first();

            return $this->response(['user_type'=>$user->role,'token' => $user->createToken("API TOKEN")->plainTextToken],false,true,['User logged in Successfully']);

        } catch (\Throwable $th) {
            return $this->response([],true,false,[$th->getMessage()]);
        }
    }
}
