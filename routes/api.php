<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BarangayOfficialController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\NewResidentController;
use App\Http\Controllers\BlotterController;



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
Route::middleware(['AuthUser:1-2-3'])->group(function () {
    Route::get('getUserDetails', [UserController::class, 'getUserDetails']);
    Route::get('viewCivilStatusTypes', [UserController::class, 'viewCivilStatusTypes']);
    Route::get('viewBarangayOfficials', [BarangayOfficialController::class, 'viewBarangayOfficials']);
    Route::get('getDocumentTypes', [DocumentController::class, 'getDocumentTypes']);
    Route::post('createAppointment', [UserController::class, 'createAppointment']);
    Route::get('viewAppointmentList', [AdminController::class, 'viewAppointmentList']);
    Route::get('viewSpecificFile', [AdminController::class, 'viewSpecificFile']);
    Route::get('viewFileList', [AdminController::class, 'viewFileList']);
});
Route::post('noVerificationRegistration', [UserController::class, 'noVerificationRegistration']);
Route::post('testString', [DocumentController::class, 'testString']);
Route::post('manualLogin', [UserController::class, 'manualLogin']);
Route::post('adminLogin', [UserController::class, 'adminLogin']);
Route::post('generateOTP', [UserController::class, 'generateOTP']);
Route::post('otpLogin', [UserController::class, 'otpLogin']);
Route::post('otpChangePassword', [UserController::class, 'otpChangePassword']);

Route::middleware(['AuthUser:3'])->group(function () {
    Route::get('viewAdminableUsers', [AdminController::class, 'viewAdminableUsers']);
    Route::post('assignRole', [AdminController::class, 'assignRole']);
    Route::post('revokeAdminAccess', [AdminController::class, 'revokeAdminAccess']);
    Route::get('viewAssignableRoles', [AdminController::class, 'viewAssignableRoles']);
    Route::get('viewPrivilegedUsers', [AdminController::class, 'viewPrivilegedUsers']);
});
Route::middleware(['AuthUser:2-3'])->group(function () {
    Route::get('viewAllBlotters', [BlotterController::class, 'viewAllBlotters']);
    Route::post('addDocumentType', [DocumentController::class, 'addDocumentType']);
    Route::post('updateDocumentTypes', [DocumentController::class, 'updateDocumentTypes']);
    Route::post('deleteDocumentType', [DocumentController::class, 'deleteDocumentType']);
    Route::post('assignBarangayOfficial', [BarangayOfficialController::class, 'assignBarangayOfficial']);
    //Route::get('viewAssignableToBarangayOfficial', [BarangayOfficialController::class, 'viewAssignableToBarangayOfficial']);
    Route::post('deleteBarangayOfficial', [BarangayOfficialController::class, 'deleteBarangayOfficial']);
    Route::post('changeResidentInformation', [UserController::class, 'changeResidentInformation']);
    Route::post('changeBarangayOfficialDetails', [BarangayOfficialController::class, 'changeBarangayOfficialDetails']);
    Route::post('deleteResidentInformation', [UserController::class, 'deleteResidentInformation']);
    Route::post('assignBarangayOfficial', [BarangayOfficialController::class, 'assignBarangayOfficial']);
    Route::get('viewAllUsers', [UserController::class, 'viewAllUsers']);
    Route::get('dashboardView', [AdminController::class, 'dashboardView']);
    Route::get('viewNewResidentRequests', [NewResidentController::class, 'viewNewResidentRequests']);
    Route::post('approveNewResident', [NewResidentController::class, 'approveNewResident']);
    Route::post('fileBlotterReport', [BlotterController::class, 'fileBlotterReport']);
    Route::post('editBlotterReport', [BlotterController::class, 'editBlotterReport']);
    Route::post('importExcelResidents', [NewResidentController::class, 'importExcelResidents']);

});
    Route::get('testEmail', [UserController::class, 'testEmail']);
    Route::post('uploadIdPicture', [AdminController::class, 'uploadIdPicture']);
    Route::get('generatePdf', [AdminController::class, 'generatePdf']);
    Route::post('applyNewResident', [UserController::class, 'applyNewResident']);
    