<?php
namespace App\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

Route::post('/send-notification', [NotificationController::class, 'sendPushNotification']);
Route::post('/send-multiple-notification', [NotificationController::class, 'sendMultiplePushNotifications']);



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'auth'], function ($router)
{
    Route::post('/register', [AuthController::class, 'register']);
	Route::post('/verify_otp', [AuthController::class, 'verify_otp']);
	Route::post('/resend_otp', [AuthController::class, 'resend_otp']);

	Route::post('/refresh_token', [Authcontroller::class , 'refresh_token'])->name('refresh_token');
});

Route::group(['middleware' => ['jwt.verify']], function ()
{
	Route::get('/user_profile', [AuthController::class, 'userProfile']);    
	Route::post('/update_profile', [AuthController::class, 'update_profile']);
	Route::post('/groupCreation', [AuthController::class, 'groupCreation']);
	Route::post('/groupList', [AuthController::class, 'groupList']);
	Route::post('/groupSearch', [AuthController::class, 'groupSearch']);
	Route::post('/publicGroupSearch', [AuthController::class, 'publicGroupSearch']);
		Route::post('/logout', [AuthController::class, 'logout']);
	Route::get('/groupType', [AuthController::class, 'groupType']);
	Route::get('/firstCategories', [AuthController::class, 'firstCategories']);
	Route::get('/secondCategories/{id}', [AuthController::class, 'secondCategories']);
	Route::get('/thirdCategories/{id}', [AuthController::class, 'thirdCategories']);
	Route::get('/businessGroupPlanList', [AuthController::class, 'businessGroupPlanList']);
	Route::post('/validatePincode', [AuthController::class, 'validatePincode']);
	Route::post('/joinPublicGroup', [AuthController::class, 'joinPublicGroup']);
	Route::post('/groupInfoUpdate', [AuthController::class, 'groupInfoUpdate']);
	Route::post('/groupInfo', [AuthController::class, 'groupInfo']);
	Route::post('/changeGroupUserStatus', [AuthController::class, 'changeGroupUserStatus']);
	Route::post('/groupProfile', [AuthController::class, 'groupProfile']);
	Route::post('/addGroupMessage', [AuthController::class, 'addGroupMessage']);
	Route::post('/viewGroupChat', [AuthController::class, 'viewGroupChat']);
	Route::post('/groupProfilePicUpdate', [AuthController::class, 'groupProfilePicUpdate']);
	Route::post('/groupCoverPicUpdate', [AuthController::class, 'groupCoverPicUpdate']);
	Route::post('/deleteGroupMessage', [AuthController::class, 'deleteGroupMessage']);
	Route::post('/fileUpload', [AuthController::class, 'fileUpload']);
    Route::post('/addGroupBanner', [AuthController::class, 'addGroupBanner']);
	Route::post('/deleteGroupBanner', [AuthController::class, 'deleteGroupBanner']);
	Route::post('/updateGroupBanner', [AuthController::class, 'updateGroupBanner']);
	Route::post('/listGroupMedia', [AuthController::class, 'listGroupMedia']);
	Route::post('/contactSync', [AuthController::class, 'contactSync']);
	Route::post('/addUsersToGroup', [AuthController::class, 'addUsersToGroup']);
	Route::post('/addUsersToGroup', [AuthController::class, 'addUsersToGroup']);
	Route::post('/leaveGroup', [AuthController::class, 'leaveGroup']);
	Route::post('/groupNameUpdate', [AuthController::class, 'groupNameUpdate']);
	Route::post('/forwardMessage', [AuthController::class, 'forwardMessage']);
	Route::post('/downloadFiles', [AuthController::class, 'downloadFiles']);
	Route::post('/removeFiles', [AuthController::class, 'removeFiles']);
	Route::post('/keyCheckExist', [AuthController::class, 'keyCheckExist']);	
	Route::post('/updateFcmToken', [AuthController::class, 'updateFcmToken']);


	
});