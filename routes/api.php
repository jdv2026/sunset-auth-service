<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtAuthenticate;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventLogController;
use App\Http\Controllers\MetaController;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    $title = '403 - Forbidden';
    $description = 'You do not have permission to access this resource.';
    $status = 403;

    return response()->view(
        'errors.common',
        [
            'title' => $title,
            'description' => $description,
            'status' => $status,
        ],
        $status
    );
});

Route::post('web/meta', [MetaController::class, 'meta']);
Route::post('web/admin/login', [AuthController::class, 'adminLogin']);
Route::post('web/guest/login', [AuthController::class, 'guestLogin']);

Route::middleware([JwtAuthenticate::class])->group(function () {
    Route::post('web/admin/logout', [AuthController::class, 'onLogout']);
    Route::post('web/user', [AuthController::class, 'reInitializeApp']);
    Route::post('web/check/type', [AuthController::class, 'checkType']);

    Route::post('web/get/eventlogs', [EventLogController::class, 'getEventLogs']);

    Route::post('web/set/settings', [SettingController::class, 'updateSetting']);

});
