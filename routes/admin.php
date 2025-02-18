<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LogincheckController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\FirstCategoryController;
use App\Http\Controllers\SecondCategoryController;
use App\Http\Controllers\ThirdCategoryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PincodeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and are assigned
| to the "web" middleware group. Routes for admin panel login.
|
*/
Route::get('/', [LoginController::class, 'login'])->name('login');
Route::get('/load-content/{page}', [ContentController::class, 'loadContent'])->name('load.content');
Route::get('admin/login', [LoginController::class, 'login'])->name('login'); // Add the 'login' route name
Route::get('admin/', [LoginController::class, 'login']);
Route::post('auth_login', [LogincheckController::class, 'login']);
Route::get('/admin/home', [HomeController::class, 'index'])->name('admin.home'); // Name the home route
Route::get('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::post('admin/logout', [LoginController::class, 'logout'])->name('admin.logout');

Route::get('/admin/group/{groupId}', [GroupController::class, 'show'])->name('group-approvals.show');
Route::post('/admin/group/reject/{id}', [GroupController::class, 'rejectGroup'])->name('group-approvals.reject');
Route::post('/admin/group/approve/{id}', [GroupController::class, 'approveGroup'])->name('group-approvals.approve');
Route::post('/admin/group/idle/{id}', [GroupController::class, 'idleGroup'])->name('group-approvals.idle');
Route::post('/admin/group/grouplist', [GroupController::class, 'grouplist'])->name('admin.group.grouplist');
Route::post('/admin/group/approvedgrouplist', [GroupController::class, 'approvedgrouplist'])->name('admin.group.approvedgrouplist');
Route::post('/admin/group/rejectedgrouplist', [GroupController::class, 'rejectedgrouplist'])->name('admin.group.rejectedgrouplist');

Route::post('/admin/firstCateoryList', [FirstCategoryController::class, 'firstCateoryList'])->name('admin.firstCateoryList');
Route::get('/admin/viewFirstCategory/{firstCateoryId}', [FirstCategoryController::class, 'view'])->name('view.first.category');
Route::post('/admin/addFirstCategory', [FirstCategoryController::class, 'store'])->name('add.first.category');
Route::get('/admin/editFirstCategory/{id}', [FirstCategoryController::class, 'edit'])->name('admin.editFirstCategory');
Route::post('/admin/updateFirstCategory/{id}', [FirstCategoryController::class, 'update'])->name('admin.updateFirstCategory');
Route::delete('/admin/deleteFirstCategory/{id}', [FirstCategoryController::class, 'delete'])->name('admin.deleteFirstCategory');

Route::get('/admin/first-category-list', [FirstCategoryController::class, 'list'])->name('admin.firstCategoryList');
Route::post('/admin/secondCategoryList', [SecondCategoryController::class, 'secondCategoryList'])->name('admin.secondCategoryList');
Route::get('/admin/viewSecondCategory/{firstCateoryId}', [SecondCategoryController::class, 'view'])->name('view.second.category');
Route::post('/admin/addSecondCategory', [SecondCategoryController::class, 'store'])->name('add.second.category');
Route::get('/admin/editSecondCategory/{id}', [SecondCategoryController::class, 'edit'])->name('admin.editSecondCategory');
Route::post('/admin/updateSecondCategory/{id}', [SecondCategoryController::class, 'update'])->name('admin.updateSecondCategory');
Route::delete('/admin/deleteSecondCategory/{id}', [SecondCategoryController::class, 'delete'])->name('admin.deleteSecondCategory');
Route::get('/admin/second-category/create', [SecondCategoryController::class, 'create'])->name('admin.second-category.create');


Route::get('/admin/second-category-list', [SecondCategoryController::class, 'list'])->name('admin.secondCategoryLists');
Route::post('/admin/thirdCategoryList', [ThirdCategoryController::class, 'thirdCategoryList'])->name('admin.thirdCategoryList');
Route::get('/admin/viewThirdCategory/{thirdCategoryId}', [ThirdCategoryController::class, 'view'])->name('view.third.category');
Route::post('/admin/addThirdCategory', [ThirdCategoryController::class, 'store'])->name('add.third.category');
Route::get('/admin/editThirdCategory/{id}', [ThirdCategoryController::class, 'edit'])->name('admin.editThirdCategory');
Route::post('/admin/updateThirdCategory/{id}', [ThirdCategoryController::class, 'update'])->name('admin.updateThirdCategory');
Route::delete('/admin/deleteThirdCategory/{id}', [ThirdCategoryController::class, 'delete'])->name('admin.deleteThirdCategory');
Route::get('/admin/third-category/create', [ThirdCategoryController::class, 'create'])->name('admin.third-category.create');

Route::post('/admin/stateList', [StateController::class, 'stateList'])->name('admin.stateList');
Route::get('/admin/viewState/{stateId}', [StateController::class, 'view'])->name('view.state');
Route::post('/admin/addState', [StateController::class, 'store'])->name('add.state');
Route::get('/admin/editState/{id}', [StateController::class, 'edit'])->name('admin.editState');
Route::post('/admin/updateState/{id}', [StateController::class, 'update'])->name('admin.updateState');
Route::delete('/admin/deleteState/{id}', [StateController::class, 'delete'])->name('admin.deleteState');

Route::get('/admin/state-list', [StateController::class, 'list'])->name('admin.state-list');
Route::post('/admin/districtList', [DistrictController::class, 'districtList'])->name('admin.districtList');

Route::get('/admin/viewDistrict/{id}', [DistrictController::class, 'view'])->name('view.district');
Route::post('/admin/addDistrict', [DistrictController::class, 'store'])->name('add.district');
Route::get('/admin/editDistrict/{id}', [DistrictController::class, 'edit'])->name('admin.editDistrict');
Route::post('/admin/updateDistrict/{id}', [DistrictController::class, 'update'])->name('admin.updateDistrict');
Route::delete('/admin/deleteDistrict/{id}', [DistrictController::class, 'delete'])->name('admin.deleteDistrict');
Route::get('/admin/district/create', [DistrictController::class, 'create'])->name('admin.district.create');
Route::post('/import-district', [DistrictController::class, 'importDistrict'])->name('admin.import-district');


Route::get('/admin/district-list', [DistrictController::class, 'list'])->name('admin.district-list');
Route::post('/admin/pincodeList', [PincodeController::class, 'pincodeList'])->name('admin.pincodeList');
Route::get('/admin/viewPincode/{id}', [PincodeController::class, 'view'])->name('view.pincode');
Route::post('/admin/addPincode', [PincodeController::class, 'store'])->name('add.pincode');
Route::get('/admin/editPincode/{id}', [PincodeController::class, 'edit'])->name('admin.editPincode');
Route::post('/admin/updatePincode/{id}', [PincodeController::class, 'update'])->name('admin.updatePincode');
Route::delete('/admin/deletePincode/{id}', [PincodeController::class, 'delete'])->name('admin.deletePincode');
Route::get('/admin/pincode/create', [PincodeController::class, 'create'])->name('admin.pincode.create');
Route::post('/admin/import-pincode', [PincodeController::class, 'importPincode'])->name('admin.import-pincode');

Route::post('/admin/planList', [PlanController::class, 'planList'])->name('admin.planList');
Route::post('/admin/addPlan', [PlanController::class, 'store'])->name('add.plan');
Route::get('/admin/viewPlan/{id}', [PlanController::class, 'view'])->name('view.plan');
Route::get('/admin/editPlan/{id}', [PlanController::class, 'edit'])->name('admin.editPlan');
Route::post('/admin/updatePlan/{id}', [PlanController::class, 'update'])->name('admin.updatePlan');
Route::delete('/admin/deletePlan/{id}', [PlanController::class, 'delete'])->name('admin.deletePlan');

Route::post('/admin/userList', [UserController::class, 'userList'])->name('admin.userList');
Route::get('/admin/viewUser/{id}', [UserController::class, 'view'])->name('view.user');
Route::get('/admin/editUser/{id}', [UserController::class, 'edit'])->name('admin.editUser');
Route::post('/admin/updateUserStatus', [UserController::class, 'updateUserStatus'])->name('admin.updateUserStatus');

Auth::routes();
