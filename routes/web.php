<?php

use App\Http\Controllers\BranchController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ErrorAdjustmentController;
use App\Http\Controllers\IclockController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManualPunchController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TimeTableController;
use App\Http\Controllers\UsersController;
use App\Models\Department;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

// Guest-only routes (show login, submit login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login'); // must be named 'login'
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// ==============================
// 🔐 Auth-only routes (Dashboard + Modules)
// ==============================
Route::middleware('auth')->group(function () {
    // Logout (POST only)
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');


    // user
    Route::get('/getUsers', [UsersController::class, 'getUsers'])->name('getUsers.getUsers');
    Route::post('/user', [UsersController::class, 'register'])->name('user.register');
    Route::put('/user/{id}', [UsersController::class, 'update'])->name('user.update');
    Route::post('/user/update-expire/{id}', [UsersController::class, 'updateExpireDate'])->name('user.update-expire');
    Route::post('/user/change-password/{id}', [UsersController::class, 'changeUserPassword'])->name('user.change-password');
    Route::get('/user/{id}', [UsersController::class, 'show'])->name('user.show');
    Route::delete('/user/{id}', [UsersController::class, 'delete'])->name('user.delete');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Employee & HR modules
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::get('/getEmployees', [EmployeeController::class, 'getEmployees'])->name('employees.getEmployees');
    Route::post('/employees', [EmployeeController::class, 'register'])->name('employees.register');
    Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::delete('/employees/{id}', [EmployeeController::class, 'delete'])->name('employees.delete');
    Route::get('/employeeData', [EmployeeController::class, 'manageEmployeeDataIndex'])->name('ManageEmployeeData');

    //branches
    Route::get('/branches', [BranchController::class, 'index'])->name('branches');
    Route::get('/getBranches', [BranchController::class, 'getBranches'])->name('branches.getBranches');
    Route::get('/branchesByCompany/{id}', [BranchController::class, 'getBranchBycompany'])->name('branches.branchesByCompany');
    Route::get('/branches/{id}', [BranchController::class, 'show'])->name('branches.show');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    Route::put('/branches/{id}', [BranchController::class, 'update'])->name('branches.update');
    Route::delete('/branches/{id}', [BranchController::class, 'delete'])->name('branches.delete');


    // Department
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments');
    Route::get('/getdepartments', [DepartmentController::class, 'getDepartments'])->name('department.getDepartments');
    Route::get('/departmentByCompany/{id}', [DepartmentController::class, 'getdepartmentBycompany'])->name('branches.departmentByCompany');
    Route::get('/department/{id}', [DepartmentController::class, 'show'])->name('department.show');
    Route::post('/department', [DepartmentController::class, 'store'])->name('department.store');
    Route::put('/department/{id}', [DepartmentController::class, 'update'])->name('department.update');
    Route::delete('/department/{id}', [DepartmentController::class, 'delete'])->name('department.delete');

    // Section
    Route::get('/sections', [SectionController::class, 'index'])->name('sections');
    Route::get('/getSections', [SectionController::class, 'getSections'])->name('sections.getSections');
    Route::get('/getsectionByDepartment/{id}', [SectionController::class, 'getgetsectionByDepartment'])->name('sections.getsectionByDepartment');
    Route::get('/section/{id}', [SectionController::class, 'show'])->name('section.show');
    Route::post('/section', [SectionController::class, 'store'])->name('section.store');
    Route::put('/section/{id}', [SectionController::class, 'update'])->name('section.update');
    Route::delete('/section/{id}', [SectionController::class, 'delete'])->name('section.delete');

    // Devices & Shifts
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices');
    Route::get('/shift', [ShiftController::class, 'index'])->name('shift');

    // Attendance-related
    Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
    Route::get('/timetable', [TimeTableController::class, 'index'])->name('timetable');
    Route::get('/manual-punch', [ManualPunchController::class, 'index'])->name('manual-punch');
    Route::get('/error-adjustment', [ErrorAdjustmentController::class, 'index'])->name('error-adjustment');

    // Leave & Overtime
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves');
    Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime');

    // Users & Companies
    Route::get('/users', [UsersController::class, 'index'])->name('users');

    //company end points
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.get');
    Route::get('/getCompanies', [CompanyController::class, 'getCompanies'])->name('companies.get');
    Route::get('/companies/{id}', [CompanyController::class, 'show'])->name('companies.show');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{id}', [CompanyController::class, 'delete'])->name('companies.delete');

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/generation-report', [ReportsController::class, 'generationReportIndex'])->name('reports.generation');
        Route::get('/summary-report', [ReportsController::class, 'summaryReportIndex'])->name('reports.summary');
        Route::get('/manage-report', [ReportsController::class, 'manageReportIndex'])->name('reports.manage');
    });

    Route::get('/admin/punches', [IclockController::class, 'listPunches']);   // limit=50
    Route::get('/admin/employees', [IclockController::class, 'listEmployees']); // limit=50
    Route::get('/admin/devices', [IclockController::class, 'listDevices']); // limit=50

    // --- Admin command endpoints (no DB tables; file-queue) ---
    Route::post('/admin/iclock/{sn}/reboot',        [IclockController::class, 'cmdReboot'])->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/admin/iclock/{sn}/clear-attlog',  [IclockController::class, 'cmdClearAttlog'])->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/admin/iclock/{sn}/clear-all',     [IclockController::class, 'cmdClearAll'])->withoutMiddleware([VerifyCsrfToken::class]);
    Route::post('/admin/iclock/{sn}/pull-all',      [IclockController::class, 'cmdPullAll'])->withoutMiddleware([VerifyCsrfToken::class]);  // ATTLOG USER
    Route::post('/admin/iclock/{sn}/sync-user',     [IclockController::class, 'cmdSyncUser'])->withoutMiddleware([VerifyCsrfToken::class]); // push one user
});

Route::any('/iclock/cdata',      [IclockController::class, 'cdata'])->withoutMiddleware([VerifyCsrfToken::class]);       // UA300 uploads ATTLOG/USER
Route::any('/iclock/getrequest', [IclockController::class, 'getrequest'])->withoutMiddleware([VerifyCsrfToken::class]);  // we just return "OK"
Route::any('/iclock/devicecmd',  [IclockController::class, 'devicecmd'])->withoutMiddleware([VerifyCsrfToken::class]);  // we just return "OK"
Route::any('/iclock/option',     [IclockController::class, 'getoption'])->withoutMiddleware([VerifyCsrfToken::class]);      // also "OK"
Route::any('/iclock/getoption',  [IclockController::class, 'getoption'])->withoutMiddleware([VerifyCsrfToken::class]);      // also "OK"
