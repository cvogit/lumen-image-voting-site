<?php

namespace App\Http\Controllers;

use App\User;
use App\UserVerification;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Create a new controller instance.
	 *
	 * @return User
	 */
	public function create(array $data)
	{
		return User::create([
			'email'     => $data['email'],
			'password'  => $data['password'],
		]);
	}

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function register(Request $request)
	{
		$data = $request->all();

		$validation = $this->validator($data);
		if($validation->fails())
			return response()->json(["message" => $validation->errors()], 500);

		$user = $this->create($data);

		// Generate verification code
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$code = '';
		$max = strlen($alphabet) - 1;

		for ($i = 0; $i < 32; $i++) {
		    $code .= $alphabet[mt_rand(0, $max)];
		}
		
		UserVerification::create([
			'user_id' => $user->id,
			'code' 		=> $code
			]);

		// Send verification email
		$message = 'Welcome to DisoDat'."\r\n".'Click on the link to activate your account'."\r\n".env('CLIENT_ADDRESS').'verification/'.$code;
		$email = $request->email;
		Mail::raw($message, function($msg) use ($email) { 
      $msg->to($email);
      $msg->subject('DisoDat verification');
    });

    // Send email to admin notifyng new user registration
    $adminEmail = env('ADMIN_ADDRESS');
    Mail::raw('New user signed up', function($msg) use ($adminEmail) { 
      $msg->to($adminEmail);
      $msg->subject('DisoDat sign up');
    });

		return response()->json(['message' => "Registration successful. Please check your email to activate the account."], 200);
	}

	/**
	 * Validate user inputs
	 * @param array
	 * @return boolean
	 */
	public function validator(array $data)
	{
		return Validator::make($data, [
			'email'     => 'required|string|email|max:255|unique:users',
			'password'  => 'required|string|min:6|confirmed',
		]);
	}

	public function test(Request $request) {
		return "test";
	}
}
