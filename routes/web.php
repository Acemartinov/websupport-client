<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RedirectController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

Route::get('/', [RedirectController::class, "listAllRedirects"]);

Route::get('/add', function () {
    return view('add');
});

Route::get('/deleted/{id}', [RedirectController::class, 'deleteRecord']);

Route::post('/add', [RedirectController::class, 'addNewRecord']);
