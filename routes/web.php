<?php

use App\Http\Controllers\TransportPdfController;
use App\Http\Controllers\SubsistencePdfController;
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

// Auth::routes();
Route::get('/', 'Auth\LoginController@showLoginForm');
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {


    Route::get('/dashboard', 'HomeController@index')->name('dashboard');

    Route::prefix('notifications')->group(function () {
        Route::get('/', 'NotificationController@index')->name('notifications');
        Route::get('/check', 'NotificationController@check')->name('notifications/check');

        // old rplus -> new rplus redirects ----------------
        Route::get('/mytasks', function () {
            return redirect(route('requisition/delegation'), 301);
        });
        // -------------------------------------------------
    });

    Route::prefix('message')->group(function () {
        Route::post('/send', 'MessageController@send')->name('message/send');
        Route::post('/answer', 'MessageController@answer')->name('message/answer');
    });

    route::prefix('userSetting')->group(function () {
        Route::get('/', 'UserSettingController@index')->name('userSetting');
        Route::get('/profile', 'UserSettingController@profile')->name('userSetting/profile');
        Route::get('/signature', 'UserSettingController@signature')->name('userSetting/signature');
        Route::post('/signature', 'UserSettingController@saveSignature');
        Route::delete('/signature', 'UserSettingController@deleteSignature');
    });

    Route::prefix('preference')->group(function () {
        Route::resource('/vendors', 'Preferences\VendorController');
        Route::get('/vendors/{id}/comments', 'Preferences\VendorController@comments')->name('vendors.comments');
        //grade
        Route::resource('/grades', 'Preferences\GradeController');
        //drivers
        Route::resource('/drivers', 'Preferences\DriverController');
        //vehicle
        Route::resource('/vehicles', 'Preferences\VehicleController');
        //priority
        Route::resource('/priority', 'Preferences\PriorityController');
    });

    Route::prefix('settings')->group(function () {
        //companies
        Route::resource('/companies', 'Settings\CompanyController');
        Route::get('/company/{company_id}/units', 'Settings\CompanyController@getUnits')->name('companyUnits');
        //units
        Route::resource('/units', 'Settings\UnitController');
        Route::get('/units/{id}/users', 'Settings\UnitController@users')->name('unit.users');
        Route::get('/units/{id}/adduser', 'Settings\UnitController@addUser')->name('unit.addUser');
        Route::post('/units/{id}/attachuser', 'Settings\UnitController@attachUser')->name('unit.attachUser');
        Route::delete('/units/{unit_id}/delete/{user_id}/{role_id}', 'Settings\UnitController@deleteUser')->name('unit.deleteUser');

        //vehicleTypes
        Route::resource('/vehicleTypes', 'Settings\VehicleTypeController');

        //campus
        Route::resource('/campuses', 'Settings\CampusController');

        //users
        Route::resource('/users', 'Settings\UserController');
        Route::get('/users/{id}/unitroles', 'Settings\UserController@userUnit')->name('user.unitRoles');
        Route::get('/users/{id}/activate', 'Settings\UserController@activate')->name('user.activate');
        Route::get('/users/{id}/deactivate', 'Settings\UserController@deactivate')->name('user.deactivate');
        Route::get('/users/{id}/addrole', 'Settings\UserController@addRole')->name('user.addRole');
        Route::post('/users/{id}/attachrole', 'Settings\UserController@attachRole')->name('user.attachRole');
        Route::delete('/users/{user_id}/deleterole/{unit_id}/{role_id}', 'Settings\UserController@deleteRole')->name('user.deleteRole');
        Route::get('/users/{user_id}/changedefault/{unit_id}', 'Settings\UserController@changeDefault')->name('user.changeDefault');

        //import | upload users from excel file
        Route::get('/import', 'Settings\UserController@importUsers')->name('user.import');
        Route::post('/upload', 'Settings\UserController@import')->name('user.upload');

        //roles
        Route::resource('/roles', 'Settings\RoleController');
        Route::get('/roles/{id}/users', 'Settings\RoleController@users')->name('roles.user');
        //authflow
        Route::get('/authflow/{company_id}/modules', 'Settings\AuthFlowController@index')->name('authflow');
        Route::get('/authflow/{company_id}/{module_id}', 'Settings\AuthFlowController@flowDetails')->name('authflow.details');
        Route::post('/authflow/{company_id}/{module_id}', 'Settings\AuthFlowController@update');
        Route::post('/authflow/summary', 'Settings\AuthFlowController@getSummary')->name('authflow.summary');
        //permission
        Route::get('/rolePermission/{role_id}/', 'Settings\RolePermissionController@index')->name('rolePermission');
        Route::post('/rolePermission/{role_id}/', 'Settings\RolePermissionController@update');
    });

    Route::get('/changelog', function () {
        return view('changelog.index');
    })->name('changelog');

    Route::prefix('requisition')->group(function () {
        Route::get('/', 'RequisitionController@index')->name('requisition');
        Route::get('/delegation', 'RequisitionController@delegation')->name('requisition/delegation');
    });
    //procurement routes
    Route::prefix('procurement')->group(function () {
        Route::get('/', 'ProcurementController@index')->name('procurement');
        Route::get('/create', 'ProcurementController@create')->name('procurement/create');
        Route::post('/create', 'ProcurementController@store')->name('procurement/store');
        Route::get('/itemSearch', 'ProcurementController@itemSearch')->name('procurement/itemSearch');
        Route::post('archive', 'ProcurementController@archive')->name('procurement/archive');
        Route::get('/{id}', 'ProcurementController@show')->name('procurement/show');
        Route::get('/{id}/documents', 'ProcurementController@documents')->name('procurement/documents');
        Route::post('/{id}/documents', 'ProcurementController@storeDocument');
        Route::delete('/{id}/documents', 'ProcurementController@deleteDocument');
        Route::get('/{id}/quotations', 'ProcurementController@quotations')->name('procurement/quotations');
        Route::post('/{id}/quotations', 'ProcurementController@storeQuotation');
        Route::delete('/{id}/quotations', 'ProcurementController@deleteQuotation');
        Route::get('/{id}/amend', 'ProcurementController@amend')->name('procurement/amend');
        Route::post('/{id}/amend', 'ProcurementController@update')->name('procurement/update');
        Route::post('/{id}/submit', 'ProcurementController@submit')->name('procurement/submit');
        Route::post('/{id}/return', 'ProcurementController@return')->name('procurement/return');
        Route::post('/{id}/savePrices', 'ProcurementController@savePrices')->name('procurement/savePrices');
        Route::post('/{id}/delegate', 'ProcurementController@delegate')->name('procurement/delegate');
        Route::delete('/{id}/delete', 'ProcurementController@delete')->name('procurement/delete');
        Route::post('/{id}/cancel', 'ProcurementController@cancel')->name('procurement/cancel');
        Route::post('/{id}/finishDelegate', 'ProcurementController@finishDelegate')->name('procurement/finishDelegate');
        Route::post('/{id}/changeOwner', 'ProcurementController@changeOwner')->name('procurement/changeOwner');
    });
    //travel routes
    Route::prefix('travel')->group(function () {
        Route::get('/', 'TravelController@index')->name('travel');
        Route::get('/create', 'TravelController@create')->name('travel/create');
        Route::post('/create', 'TravelController@store')->name('travel/store');
        Route::get('/{id}', 'TravelController@show')->name('travel/show');

        Route::get('/{id}/documents', 'TravelController@documents')->name('travel/documents');
        Route::post('/{id}/documents', 'TravelController@storeDocument');
        Route::delete('/{id}/documents', 'TravelController@deleteDocument');

        Route::get('/{id}/amend', 'TravelController@amend')->name('travel/amend');
        Route::post('/{id}/amend', 'TravelController@update')->name('travel/update');
        Route::post('/{id}/submit', 'TravelController@submit')->name('travel/submit');
        Route::post('/{id}/approve', 'TravelController@approve')->name('travel/approve');
        Route::post('/{id}/transport', 'TravelController@transport')->name('travel/transport');
        Route::post('/{id}/subsistence', 'TravelController@subsistence')->name('travel/subsistence');
        Route::post('/{id}/return', 'TravelController@return')->name('travel/return');
        Route::post('/{id}/allocateVehicle', 'TravelController@allocateVehicle')->name('travel/allocateVehicle');
        Route::post('/{id}/allocateDriver', 'TravelController@allocateDriver')->name('travel/allocateDriver');
        Route::post('/{id}/delegate', 'TravelController@delegate')->name('travel/delegate');
        Route::post('/{id}/cancel', 'TravelController@cancel')->name('travel/cancel');
        Route::delete('/{id}/delete', 'TravelController@delete')->name('travel/delete');
        Route::post('/{id}/finishDelegate', 'TravelController@finishDelegate')->name('travel/finishDelegate');

    });
    Route::prefix('transport')->group(function () {
        Route::get('/{id}', 'TransportController@show')->name('transport/show');
        Route::post('/{id}/submit', 'TransportController@submit')->name('transport/submit');
        Route::post('/{id}/return', 'TransportController@return')->name('transport/return');
        Route::post('/{id}/approve', 'TransportController@approve')->name('transport/approve');
        Route::post('/{id}', 'TransportController@allocate')->name('transport/allocate');
    });

    Route::prefix('subsistence')->group(function () {
        Route::get('/{id}', 'SubsistenceController@show')->name('subsistence/show');
        Route::post('/{id}/submit', 'SubsistenceController@submit')->name('subsistence/submit');
        Route::post('/{id}/return', 'SubsistenceController@return')->name('subsistence/return');
        Route::post('/{id}/approve', 'SubsistenceController@approve')->name('subsistence/approve');
    });

    Route::get('/{id}/downloadTransport-pdf', [TransportPdfController::class, 'downloadTransportPdf'])->name('downloadTransport-pdf');
    Route::get('/{id}/downloadSubsistence-pdf', [SubsistencePdfController::class, 'downloadSubsistencePdf'])->name('downloadSubsistence-pdf');

    Route::prefix('purchase')->group(function () {
        Route::get('/', 'HomeController@index')->name('purchase');
        Route::get('/{id}', 'PurchaseController@show')->name('purchase/show');
        Route::delete('/{id}/reset', 'PurchaseController@reset')->name('purchase/reset');
        Route::post('/{id}/submit', 'PurchaseController@submit')->name('purchase/submit');
        Route::post('/{id}/return', 'PurchaseController@return')->name('purchase/return');
        Route::post('/{id}/savePoNumber', 'PurchaseController@savePoNumber')->name('purchase/savePoNumber');
        Route::post('/{id}/sendPayment', 'PurchaseController@sendPayment')->name('purchase/sendPayment');
        Route::post('/{id}/delegate', 'PurchaseController@delegate')->name('purchase/delegate');
        Route::post('/{id}/finishDelegate', 'PurchaseController@finishDelegate')->name('purchase/finishDelegate');
        Route::post('/{id}/rate', 'PurchaseController@rate')->name('purchase/rate');
    });
    Route::prefix('voucher')->group(function () {
        Route::get('/', 'VoucherController@index')->name('voucher');
        Route::get('/{id}', 'VoucherController@show')->name('voucher/show');
        Route::post('/{id}/submit', 'VoucherController@submit')->name('voucher/submit');
        Route::post('/{id}/return', 'VoucherController@return')->name('voucher/return');
        Route::post('/{id}/transfer', 'VoucherController@transfer')->name('voucher/transfer');
        Route::post('/{id}/setBankTax', 'VoucherController@setBankTax')->name('voucher/setBankTax');
        Route::post('/{id}/paid', 'VoucherController@paid')->name('voucher/paid');
    });


    // old rplus -> new rplus redirects ----------------
    Route::get('authentication/login', function () {
        //http://rplus.medcol.mw/index.php/authentication/login?back_url=dashboard
        return redirect(route('login'), 301);
    });
    Route::prefix('requisitions')->group(function () {
        Route::get('/', function () {
            return redirect(route('requisition'), 301);
        });
        Route::get('/requisitions', function () {
            return redirect(route('requisition'), 301);
        });
        Route::get('/briefcase', function () {
            return redirect(route('requisition', ['q' => 'mine']), 301);
        });
        Route::get('/requisition/{id}', function ($procurementId) {
            return redirect(route('procurement/show', $procurementId), 301);
        });
        Route::get('/pr/{id}', function ($purchaseId) {
            return redirect(route('purchase/show', $purchaseId), 301);
        });
    });
    // -------------------------------------------------
});
