<?php

use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\dashboard\CrmController;
use App\Http\Controllers\dashboard\DashboardController;
use App\Http\Controllers\dashboard\MainMenuController;
use App\Http\Controllers\user\FaqController;
use App\Http\Controllers\dashboard\RoleController;
use App\Http\Controllers\dashboard\PermissionController;
use App\Http\Controllers\payment\StripeController;
use App\Http\Controllers\user\UserReviewController;
use App\Libraries\SmartPhone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['storeApiRequest', 'cors'])->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('send-phone-verification-code', [AuthController::class, 'sendPhoneVerificationCode']);
    Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::get('get-states', [AuthController::class, 'getStates']);
    Route::get('get-counties-dropdown', [MainMenuController::class, 'getCountiesDropdown']);
    Route::get('get-cities-dropdown', [MainMenuController::class, 'getcitiesDropdown']);
    Route::get('get-business_types', [AuthController::class, 'getBusinessTypes']);
    Route::post('add-user-business-types', [AuthController::class, 'addUserBusinessTypes']);
    Route::post('add-user-states', [AuthController::class, 'addUserStates']);
    Route::post('send-sms-testing', function (Request $request) {
        $response = SmartPhone::sendPhoneVerificationMessage($request->to_number, $request->user_name, $request->otp_code, $request->api_request_id);
        return $response;
    });
});

Route::prefix('user')->group(function () {
    Route::get('get-reviews', [UserReviewController::class, 'getReviews']);
    Route::get('get-faqs', [FaqController::class, 'getFaqs']);
});

Route::prefix('dashboard')->middleware(['storeApiRequest', 'cors', 'auth:api'])->group(function () {
    Route::get('get-leads', [MainMenuController::class, 'getLeads']);
    Route::get('get-side-menu-modules', [DashboardController::class, 'getSideMenuModules']);
    Route::resource('roles', RoleController::class);
    Route::post('assign-roles-to-user', [RoleController::class, 'assignRolesToUser']);
    Route::resource('permissions', PermissionController::class);
    Route::post('assign-permissions-to-role', [PermissionController::class, 'assignPermissionsToRole']);
    Route::get('get-crm-data', [CrmController::class, 'getCrmData']);
    Route::post('make-payment', [StripeController::class, 'makePayment']);
    Route::post('create-ticket', [TicketController::class, 'create']);
    Route::get('get-tickets', [TicketController::class, 'index']);

});