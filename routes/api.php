<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIs\AuthController;
use App\Http\Controllers\APIs\UserController;
use App\Http\Controllers\APIs\ManagerController;
use App\Http\Controllers\APIs\EmployeeController;
use App\Http\Controllers\APIs\JobController;
use App\Http\Controllers\APIs\PermissionController;
use App\Http\Controllers\APIs\AttachmentController;
use App\Http\Controllers\APIs\RoleController;
use App\Http\Controllers\APIs\PasswordResetController;
use App\Http\Controllers\APIs\MediaUploadController;
use App\Http\Controllers\APIs\UserActivityLogController;
use App\Http\Controllers\APIs\SettingController;
use App\Http\Controllers\APIs\searchController;
use App\Http\Controllers\APIs\ContactController;

// User login api
Route::post('/login', [AuthController::class, 'login']);

// Define your registration API route
Route::post('/register', [AuthController::class, 'register'])->name('api.register');


Route::post('/forget-password', [PasswordResetController::class, 'forgetPassword']);
Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
    
    // User logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // routes grouping
    Route::group(['prefix' => 'users'], function() { 

        // Add user
        Route::post('/', [UserController::class, 'postUser']);

        // Get all users
        Route::get('/', [UserController::class, 'getUsers']);

        // Get my users
        Route::get('/my', [UserController::class, 'getMyUsers']);
        
        // Get Detail
        Route::get('/{id}', [UserController::class, 'editUser']);

        // Update a user by ID
        Route::put('/{id}', [UserController::class, 'updateUser']);

        // Change password of a user
        Route::put('/change-password/{id}', [UserController::class, 'changePassword']);

        // Delete a user by ID
        Route::delete('/{id}', [UserController::class, 'deleteUser']);

        // revoke a user by ID
        Route::put('/revoke/{id}', [UserController::class, 'revokeUser']);

    });

    Route::group(['prefix' => 'manager'], function() { 

        // Add manager
        Route::post('/', [ManagerController::class, 'postManager']);

        // Get all Managers
        Route::get('/', [ManagerController::class, 'getManagers']);

        // Get my Managers
        Route::get('/my', [ManagerController::class, 'getMyManagers']);
        
        // Get Detail
        Route::get('/{id}', [ManagerController::class, 'editManager']);

        // Update a Manager by ID
        Route::put('/{id}', [ManagerController::class, 'updateManager']);

        // Change password of a Manager
        Route::put('/change-password/{id}', [ManagerController::class, 'changePassword']);

        // Delete a Manager by ID
        Route::delete('/{id}', [ManagerController::class, 'deleteManager']);

        // revoke a Manager by ID
        Route::put('/revoke/{id}', [ManagerController::class, 'revokeManager']);

    });

    Route::group(['prefix' => 'employees'], function() { 

        // Add Employee
        Route::post('/', [EmployeeController::class, 'postEmployee']);

        // Get all Employee
        Route::get('/', [EmployeeController::class, 'getEmployees']);

        // Get my Employees
        Route::get('/my', [EmployeeController::class, 'getMyEmployees']);
        
        // Get Detail
        Route::get('/{id}', [EmployeeController::class, 'editEmployee']);

        // Update a Employee by ID
        Route::put('/{id}', [EmployeeController::class, 'updateEmployee']);

        // Change password of a Employee
        Route::put('/change-password/{id}', [EmployeeController::class, 'changePassword']);

        // Delete a Employee by ID
        Route::delete('/{id}', [EmployeeController::class, 'deleteEmployee']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [EmployeeController::class, 'revokeEmployee']);

    });


    Route::group(['prefix' => 'job'], function() { 

        // Add Employee
        Route::post('/', [JobController::class, 'postJob']);

        // Get all Employee
        Route::get('/', [JobController::class, 'getJobs']);

        // Get my Employees
        Route::get('/my', [JobController::class, 'getMyJobs']);
        
        // Get Detail
        Route::get('/{id}', [JobController::class, 'editJob']);

        // Update a Employee by ID
        Route::put('/{id}', [JobController::class, 'updateJob']);

        // Delete a Employee by ID
        Route::delete('/{id}', [JobController::class, 'deleteJob']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [JobController::class, 'revokeJob']);

    });

    Route::group(['prefix' => 'attachment'], function() { 

        // Add Employee
        Route::post('/', [AttachmentController::class, 'postAttachment']);

        // Get all Employee
        Route::get('/', [AttachmentController::class, 'getAttachments']);

        // Get my Employees
        Route::get('/my', [AttachmentController::class, 'getMyAttachments']);
        
        // Get Detail
        Route::get('/{id}', [AttachmentController::class, 'editAttachment']);

        // Update a Employee by ID
        Route::put('/{id}', [AttachmentController::class, 'updateAttachment']);

        // Delete a Employee by ID
        Route::delete('/{id}', [AttachmentController::class, 'deleteAttachment']);

        // revoke a Employee by ID
        Route::put('/revoke/{id}', [AttachmentController::class, 'revokeAttachment']);

    });


    Route::group(['prefix' => 'activities'], function(){
        
        // Get all user activity logs on the admin side
        Route::get('/adminSideAllLogs/{per_page_count?}', [UserActivityLogController::class, 'adminSideAllLogs']);
        
        // Get all user auth activities logs
        Route::get('/authHistory/{per_page_count?}', [UserActivityLogController::class, 'userAuthHistory']);
    });

    Route::group(['prefix' => 'permissions'], function() {

        // Create Permissions
        Route::post('/', [PermissionController::class, 'postPermission']);

        // Get all permissions
        Route::get('/', [PermissionController::class, 'getPermissions']);

        // Get all table name
        Route::get('/table-list', [PermissionController::class, 'getAllTables']);

        // Update
        Route::put('/{id}', [PermissionController::class, 'updatePermission']);
        
        //Delete Group
        Route::delete('/{group?}', [PermissionController::class, 'deletePermissions']);

        // revoke
        Route::put('/revoke/{group}', [PermissionController::class, 'revokePermissions']);
    });

    Route::group(['prefix' => 'roles'], function() {  
        
        // Create a role
        Route::post('/', [RoleController::class, 'postRole']);

        // Get all roles
        Route::get('/', [RoleController::class, 'getRoles']);

        // Get a specific role with permissions by role ID
        Route::get('/{id}', [RoleController::class, 'editRole']);

        // Update a specific role with permissions by role ID
        Route::put('/{id}', [RoleController::class, 'updateRole']);

        //Delete role with permissions by role ID
        Route::delete('/{id}', [RoleController::class, 'deleteRole']);
        
        // revoke
        Route::put('/revoke/{id}', [RoleController::class, 'revokeRole']);
    });

    Route::group(['prefix' => 'media'], function() {

        // Get all images
        Route::get('/', [MediaUploadController::class, 'getAllFiles']);

        // Get all folder list
        Route::get('/folder-list', [MediaUploadController::class, 'getAllFolders']);

        Route::post('/upload', [MediaUploadController::class, 'uploadImage']);

        //Delete role with permissions by role ID
        Route::delete('/', [MediaUploadController::class, 'deleteFile']);
    });


    Route::group(['prefix' => 'setting'], function() {

        // Update a Employee by ID
        Route::put('/update-profile', [SettingController::class, 'updateProfile']);

        // Get login user profile
        Route::get('/my-profile', [SettingController::class, 'getMyProfile']);

        // Update login user password
        Route::put('/change-password', [SettingController::class, 'myChangePassword']);

    });

    Route::group(['prefix' => 'search'], function() {  
        // Get all record
        Route::get('/', [searchController::class, 'getAll']);
    });

    Route::group(['prefix' => 'contact'], function() {  
        // Get all record
        Route::get('/', [ContactController::class, 'getAll']);
    });
});
