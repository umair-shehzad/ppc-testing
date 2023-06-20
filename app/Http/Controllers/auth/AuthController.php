<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\AddUserBusinessTypesRequest;
use App\Http\Requests\auth\AddUserStatesRequest;
use App\Http\Requests\auth\ForgotPasswordRequest;
use App\Http\Requests\auth\LoginRequest;
use App\Http\Requests\auth\RegisterRequest;
use App\Http\Requests\auth\ResetPasswordRequest;
use App\Http\Requests\auth\SavePhoneRequest;
use App\Http\Requests\auth\VerifyEmailRequest;
use App\Http\Requests\auth\VerifyPhoneRequest;
use App\Libraries\Twilio;
use App\Mail\PasswordReset;
use App\Mail\VerifyEmail;
use App\Models\BusinessType;
use App\Models\State;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => $request->password
            ]);
            $email_verification_token = Str::random(100);
            $user->email_verification_token = $email_verification_token;
            $user->save();
            $email_verification_url = config("constants.FRONTEND_BASE_URL") . '/verify-email/' . $email_verification_token . '/' . $request->email;
            $data = [
                'first_name' =>  $user->first_name,
                'last_name' =>  $user->last_name,
                'email_verification_url' =>  $email_verification_url
            ];
            Mail::to($request->email)->send(new VerifyEmail($data));
            storeApiResponse($request->api_request_id, $user, 201, $user->id);
            return response()->success($user, 201);
        } catch (\Exception $e) {
            saveErrorLogs('register', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                storeApiResponse($request->api_request_id, ['message' => 'auth token created!'], 200, $user->id);
                return response()->success($response, 200);
            } else {
                return response()->error("Credentials mismatched!", 422);
            }
        } catch (\Exception $e) {
            saveErrorLogs('login', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $token = Str::random(100);
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);
            $reset_password_url = config("constants.FRONTEND_BASE_URL") . '/reset-password/' . $token . '/' . $request->email;
            $data = [
                'first_name' =>  $user->first_name,
                'last_name' =>  $user->last_name,
                'action_url' =>  $reset_password_url
            ];
            Mail::to($request->email)->send(new PasswordReset($data));
            $response = ['message' => 'An email to reset your password has been sent!'];
            storeApiResponse($request->api_request_id, $response, 200, $user->id);
            return response()->success($response, 200);
        } catch (\Exception $e) {
            saveErrorLogs('forgotPassword', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $record = DB::table('password_reset_tokens')->where([
                ['email', '=', $request->email],
                ['token', '=', $request->token]
            ])->first();
            if ($record) {
                // Check if the password reset token is older than 24 hours
                $created_at = Carbon::createFromTimeString($record->created_at);
                $now = Carbon::now();
                $hours_diff = $created_at->diffInRealHours($now);
                if ($hours_diff >= 24) {
                    $error = 'Your password reset token has been expired!';
                    storeApiResponse($request->api_request_id, ['message' => $error], 400, $user->id);
                    return response()->error($error, 400);
                }
                $is_updated = $user->update(['password' => $request->password]);
                if ($is_updated) {
                    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                    $response = ['message' => 'Your password has been reset!'];
                    storeApiResponse($request->api_request_id, $response, 200, $user->id);
                    return response()->success($response, 200);
                } else {
                    $error = 'Something went wrong while updating the password!';
                    storeApiResponse($request->api_request_id, ['message' => $error], 500, $user->id);
                    return response()->error($error, 500);
                }
            } else {
                $error = 'No such record found. Please try again!';
                storeApiResponse($request->api_request_id, ['message' => $error], 404, $user->id);
                return response()->error($error, 404);
            }
        } catch (\Exception $e) {
            saveErrorLogs('resetPassword', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function sendPhoneVerificationCode(SavePhoneRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->firstOrFail();
            $opt_code = rand(100000, 999999);
            $user->phone_verification_code = $opt_code;
            $user->phone_verification_code_generated_at = now();
            $user->phone = $request->phone;
            $user->save();
            $twilio_response = Twilio::sendPhoneVerificationMessage($request->phone, $opt_code, $request->api_request_id);
            if ($twilio_response->successful()) {
                $response = ['message' => 'We have sent you a verification code on your phone!'];
                storeApiResponse($request->api_request_id, $response, 200, $user->id);
                return response()->success($response, 200);
            }
            $response = 'Something bad happend while sending verification code on your phone!';
            storeApiResponse($request->api_request_id, ['message' => $response], 500, $user->id);
            return response()->error($response, 500);
        } catch (\Exception $e) {
            saveErrorLogs('savePhone', $e->getMessage(), $e->getLine());
            $error = 'Something went wrong while sending verfication code!';
            storeApiResponse($request->api_request_id, ['message' => $error], 500, $user->id);
            return response()->error($e->getMessage(), 500);
        }
    }

    public function verifyPhone(VerifyPhoneRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $verification_code = $user->phone_verification_code;
            $generated_at = Carbon::parse($user->phone_verification_code_generated_at);

            if ($verification_code == $request->phone_verification_code) {
                // Check if verification code is expired
                $expiry_time = $generated_at->addMinutes(2);
                if ($expiry_time->isPast()) {
                    $error = 'Your OTP has been expired!';
                    storeApiResponse($request->api_request_id, ['message' => $error], 422, $user->id);
                    return response()->error($error, 422);
                }
                $user->phone_verified_at = now();
                $user->phone_verification_code = null;
                $user->phone_verification_code_generated_at = null;
                $user->save();
                $response = ['message' => 'Your phone number has been verified!'];
                storeApiResponse($request->api_request_id, $response, 200, $user->id);
                return response()->success($response, 200);
            }
            $error = 'You have entered wrong verification code!';
            storeApiResponse($request->api_request_id, ['message' => $error], 422, $user->id);
            return response()->error($error, 422);
        } catch (\Exception $e) {
            saveErrorLogs('resetPassword', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            if ($user->email_verified_at) {
                $error = ['message' => 'Your email has been verified!'];
                storeApiResponse($request->api_request_id, ['message' => $error], 403, $user->id);
                return response()->error($error, 403);
            }
            $email_verification_token = $user->email_verification_token;
            $generated_at = Carbon::parse($user->created_at);
            if ($email_verification_token == $request->email_verification_token) {
                // Check if token is expired
                $expiry_time = $generated_at->addHours(24);
                if ($expiry_time < Carbon::now()) {
                    $error = 'Your verification token has been expired!';
                    storeApiResponse($request->api_request_id, $error, 400, $user->id);
                    return response()->error($error, 400);
                }
                $user->email_verified_at = now();
                $user->email_verification_token = null;
                $user->save();
                $response = ['message' => 'Your email has been verified!'];
                storeApiResponse($request->api_request_id, $response, 200, $user->id);
                return response()->success($response, 200);
            } else {
                $error = 'Your email verification token is not correct!';
                storeApiResponse($request->api_request_id, $error, 400, $user->id);
                return response()->error($error, 400);
            }
        } catch (\Exception $e) {
            saveErrorLogs('verifyEmail', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function getBusinessTypes(HttpRequest $request)
    {
        try {
            $business_types = BusinessType::get();
            if ($business_types->isNotEmpty()) {
                storeApiResponse($request->api_request_id, ['message' => 'business types fetched!'], 200, null);
                return response()->success($business_types, 200);
            }
            storeApiResponse($request->api_request_id, ['message' => 'business types not found!'], 404, null);
            return response()->error('business types not found!', 404);
        } catch (\Exception $e) {
            saveErrorLogs('getBusinessTypes', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function getStates(HttpRequest $request)
    {
        try {
            $states = State::get();
            if ($states->isNotEmpty()) {
                storeApiResponse($request->api_request_id, ['message' => 'states fetched!'], 200, null);
                return response()->success($states, 200);
            }
            storeApiResponse($request->api_request_id, ['message' => 'states not found!'], 404, null);
            return response()->error('states not found!', 404);
        } catch (\Exception $e) {
            saveErrorLogs('getStates', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function addUserBusinessTypes(AddUserBusinessTypesRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $business_types = $request->business_types;
            $user->businessTypes()->sync($business_types);
            $response = ['message' => 'Your business types has been added!'];
            storeApiResponse($request->api_request_id, $response, 200, $user->id);
            return response()->success($response, 200);
        } catch (\Exception $e) {
            saveErrorLogs('addUserBusinessType', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }

    public function addUserStates(AddUserStatesRequest $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            $state_ids = $request->state_ids;
            $user->states()->sync($state_ids);
            $response = ['message' => 'Your states has been added!'];
            storeApiResponse($request->api_request_id, $response, 200, $user->id);
            return response()->success($response, 200);
        } catch (\Exception $e) {
            saveErrorLogs('addUserStates', $e->getMessage(), $e->getLine());
            return response()->error($e->getMessage(), 500);
        }
    }
}
