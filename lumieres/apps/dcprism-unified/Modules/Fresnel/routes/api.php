<?php

use Illuminate\Support\Facades\Route;
use Modules\Fresnel\Http\Controllers\FresnelController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('fresnels', FresnelController::class)->names('fresnel');
});
