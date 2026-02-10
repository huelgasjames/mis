<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProcessorController;
use App\Http\Controllers\MotherboardController;
use App\Http\Controllers\VideoCardController;
use App\Http\Controllers\DvdRomController;
use App\Http\Controllers\PsuController;
use App\Http\Controllers\RamController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\DepartmentInventoryController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\LaboratoryInventoryController;
use App\Http\Controllers\CategoryController;

Route::apiResource('assets', AssetController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('processors', ProcessorController::class);
Route::apiResource('motherboards', MotherboardController::class);
Route::apiResource('video-cards', VideoCardController::class);
Route::apiResource('dvd-roms', DvdRomController::class);
Route::apiResource('psus', PsuController::class);
Route::apiResource('rams', RamController::class);
Route::apiResource('storage', StorageController::class);
Route::apiResource('department-inventory', DepartmentInventoryController::class);
Route::apiResource('laboratories', LaboratoryController::class);
Route::apiResource('laboratory-inventory', LaboratoryInventoryController::class);
Route::apiResource('categories', CategoryController::class);

// Department inventory specific routes
Route::get('department-inventory/department/{departmentId}', [DepartmentInventoryController::class, 'getByDepartment']);
Route::get('department-inventory/stats', [DepartmentInventoryController::class, 'getStats']);
Route::post('department-inventory/generate-pc-number', [DepartmentInventoryController::class, 'generatePcNumber']);

// Laboratory specific routes
Route::get('laboratories/stats', [LaboratoryController::class, 'getStats']);
Route::get('laboratories/{laboratoryId}/inventory', [LaboratoryController::class, 'getInventory']);
Route::post('laboratories/generate-lab-pc-number', [LaboratoryController::class, 'generateLabPcNumber']);

// Laboratory inventory specific routes
Route::get('laboratory-inventory/under-repair', [LaboratoryInventoryController::class, 'getUnderRepair']);
Route::get('laboratory-inventory/laboratory/{laboratoryId}', [LaboratoryInventoryController::class, 'getByLaboratory']);
Route::get('laboratory-inventory/stats', [LaboratoryInventoryController::class, 'getStats']);
Route::post('laboratory-inventory/{laboratoryInventory}/start-repair', [LaboratoryInventoryController::class, 'startRepair']);
Route::post('laboratory-inventory/{laboratoryInventory}/complete-repair', [LaboratoryInventoryController::class, 'completeRepair']);

// Additional laboratory inventory routes
Route::post('laboratory-inventory/{laboratoryInventory}/deploy', [LaboratoryInventoryController::class, 'deploy']);
Route::post('laboratory-inventory/{laboratoryInventory}/recall', [LaboratoryInventoryController::class, 'recall']);
Route::get('laboratory-inventory/maintenance-schedule', [LaboratoryInventoryController::class, 'getMaintenanceSchedule']);
Route::get('laboratory-inventory/{laboratoryInventory}/repair-history', [LaboratoryInventoryController::class, 'getRepairHistory']);
Route::get('laboratory-inventory/{laboratoryInventory}/maintenance-records', [LaboratoryInventoryController::class, 'getMaintenanceRecords']);

// Category specific routes
Route::get('categories/active', [CategoryController::class, 'getActive']);
Route::get('categories/editable', [CategoryController::class, 'getEditable']);
Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);

Route::post('/login', [AuthController::class, 'login']);