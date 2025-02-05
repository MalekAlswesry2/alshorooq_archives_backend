<?php

use App\Http\Controllers\DownloadAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('home');
// });

Route::get('/receipt', function () {
    return view('receipt'); // receipt.blade.php
});
Route::get('/download', [DownloadAppController::class, 'index']);
// Route::get('/download', function () {
//     return view('app'); // receipt.blade.php
// });