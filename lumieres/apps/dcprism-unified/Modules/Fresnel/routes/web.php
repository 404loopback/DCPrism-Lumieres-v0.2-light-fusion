<?php

use Illuminate\Support\Facades\Route;
use Modules\Fresnel\Http\Controllers\FresnelController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('fresnels', FresnelController::class)->names('fresnel');
});
