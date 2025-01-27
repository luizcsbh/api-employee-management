<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ImportController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Protegendo rotas de empresas e upload com middleware "role:admin"
    Route::middleware(['role:admin'])->group(function () {
        Route::apiResource('companies', CompanyController::class);
        Route::post('/upload', [ImportController::class, 'importCSV'])->name('upload.importCSV');
        Route::get('/import-status/{id}', [ImportController::class, 'show'])->name('import-status.show');
    });

    // Rotas acessíveis a usuários autenticados
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
});
