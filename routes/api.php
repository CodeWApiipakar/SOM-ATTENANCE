<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceCommandController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\IclockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::get('/health', fn() => response()->json(['ok' => true]));       // simple health
// UA300 Pro (ZK ADMS / iclock)
Route::any('/iclock/cdata',      [IclockController::class, 'cdata']);       // UA300 uploads ATTLOG/USER
Route::any('/iclock/getrequest', [IclockController::class, 'getrequest']);  // we just return "OK"
Route::any('/iclock/devicecmd',  [IclockController::class, 'devicecmd']);   // we just return "OK"
Route::any('/iclock/option',     [IclockController::class, 'option']);      // also "OK"
Route::any('/iclock/getoption',  [IclockController::class, 'option']);      // also "OK"

