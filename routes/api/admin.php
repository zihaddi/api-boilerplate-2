<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\Admin\AuthClientController;
use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\RolePermissionController;
use App\Http\Controllers\Api\Admin\ServicePricingController;
use App\Http\Controllers\Api\Admin\TreeEntityController;
use App\Http\Controllers\Api\Admin\DoctorProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\PaymentController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\PetController;
use App\Http\Controllers\Api\Admin\OrganizationController;
use App\Http\Controllers\Api\Admin\GroomerProfileController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\AppointmentController;


//Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login')->name('adminAuth.login');
    Route::post('/otp-resend', 'reqOtpResend')->name('adminAuth.otp_resend');
    Route::post('/otp-verify', 'reqOtpVerify')->name('adminAuth.otp_verify');
    Route::post('/set-password', 'setNewPassword')->name('adminAuth.set_password');
    Route::post('/forgot-password', 'forgotPassword')->name('adminAuth.forgotPassword');
});

//Use Refresh Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

//Use Access Token
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    // Auth
    Route::controller(AuthController::class)->group(function () {
        Route::post('/user', 'getUser')->name('adminAuth.getUser');
        Route::post('/logout', 'logout')->name('adminAuth.logout');
    });
    // Tree Entity
    Route::controller(TreeEntityController::class)->name('tree-entity.')->prefix('tree-entity')->group(function () {
        Route::get('build-menu', 'buildmenu')->name('build-menu');
        Route::post('main-menu', 'treemenuNew')->name('tree-menu');
        Route::post('update-menu', 'updateMenu')->name('update-menu');
        Route::post('delete-menu', 'deleteMenu')->name('delete-menu');
        Route::post('restore/{id}', 'restore')->name('restore');
    });

    Route::apiResource('tree-entity', TreeEntityController::class);
    Route::apiResource('auth-client', AuthClientController::class);
    Route::controller(AuthClientController::class)->group(function () {
        Route::post('auth-client/all', 'index')->name('auth-client.all');
        Route::post('auth-client/restore/{id}', 'restore')->name('tree-entity.restore');
    });

    // Roles
    Route::apiResource('roles', RoleController::class);
    Route::controller(RoleController::class)->group(function () {
        Route::post('roles/all', 'index')->name('roles.all');
        Route::post('roles/restore/{id}', 'restore')->name('roles.restore');
    });

    //use when required
    //->middleware([
    //     'index' => 'check.permission:view',
    //     'store' => 'check.permission:add',
    //     'update' => 'check.permission:edit',
    //     'destroy' => 'check.permission:delete',
    // ])

    // Role Permissions
    Route::controller(RolePermissionController::class)->group(function () {
        Route::post('role-permissions/show/{id}', 'show')->name('roles.show');
        Route::post('role-permissions/permission-update/{id}', 'pupdate')->name('roles.permission-update');
    });


    //Users
    Route::apiResource('users', UserController::class);
    Route::controller(UserController::class)->group(function () {
        Route::post('users/all', 'index')->name('users.all');
        Route::post('users/restore/{id}', 'restore')->name('users.restore');
    });


     // Pet Management Routes
        Route::prefix('pets')->group(function () {
            Route::get('/', [PetController::class, 'index']);
            Route::post('/', [PetController::class, 'store']);
            Route::get('/{id}', [PetController::class, 'show']);
            Route::put('/{id}', [PetController::class, 'update']);
            Route::delete('/{id}', [PetController::class, 'destroy']);
            Route::get('/owner/{ownerId}', [PetController::class, 'getByOwner']);
            Route::get('/categories/list', [PetController::class, 'getPetCategories']);
            Route::get('/subcategories/list', [PetController::class, 'getPetSubcategories']);
            Route::get('/breeds/list', [PetController::class, 'getPetBreeds']);
        });




        // Pet Category Management Routes
        Route::prefix('pet-categories')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'destroy']);
            Route::get('/active/list', [App\Http\Controllers\Api\Admin\PetCategoryController::class, 'getActive']);
        });

        // Pet Subcategory Management Routes
        Route::prefix('pet-subcategories')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'destroy']);
            Route::get('/category/{categoryId}', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'getByCategory']);
            Route::get('/active/list', [App\Http\Controllers\Api\Admin\PetSubcategoryController::class, 'getActive']);
        });

        // Pet Breed Management Routes
        Route::prefix('pet-breeds')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'destroy']);
            Route::get('/subcategory/{subcategoryId}', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'getBySubcategory']);
            Route::get('/active/list', [App\Http\Controllers\Api\Admin\PetBreedController::class, 'getActive']);
        });

        // Pet Helper Routes (for dropdowns)
        Route::prefix('pets')->group(function () {
            Route::get('/categories', [App\Http\Controllers\Api\Admin\PetController::class, 'getPetCategories']);
            Route::get('/subcategories', [App\Http\Controllers\Api\Admin\PetController::class, 'getPetSubcategories']);
            Route::get('/breeds', [App\Http\Controllers\Api\Admin\PetController::class, 'getPetBreeds']);
        });

        // Organization Management Routes
        Route::prefix('organizations')->group(function () {
            Route::get('/', [OrganizationController::class, 'index']);
            Route::post('/', [OrganizationController::class, 'store']);
            Route::get('/{id}', [OrganizationController::class, 'show']);
            Route::put('/{id}', [OrganizationController::class, 'update']);
            Route::delete('/{id}', [OrganizationController::class, 'destroy']);
            Route::get('/active/list', [OrganizationController::class, 'getActive']);
        });

        // Groomer Profile Management Routes
        Route::prefix('groomer-profiles')->group(function () {
            Route::get('/', [GroomerProfileController::class, 'index']);
            Route::post('/', [GroomerProfileController::class, 'store']);
            Route::get('/{id}', [GroomerProfileController::class, 'show']);
            Route::put('/{id}', [GroomerProfileController::class, 'update']);
            Route::delete('/{id}', [GroomerProfileController::class, 'destroy']);
            Route::get('/organization/{organizationId}', [GroomerProfileController::class, 'getByOrganization']);
            Route::get('/user/{userId}', [GroomerProfileController::class, 'getByUser']);
        });

        // Doctor Profile Management Routes
        Route::prefix('doctor-profiles')->group(function () {
            Route::get('/', [DoctorProfileController::class, 'index']);
            Route::post('/', [DoctorProfileController::class, 'store']);
            Route::get('/{id}', [DoctorProfileController::class, 'show']);
            Route::put('/{id}', [DoctorProfileController::class, 'update']);
            Route::delete('/{id}', [DoctorProfileController::class, 'destroy']);
            Route::get('/organization/{organizationId}', [DoctorProfileController::class, 'getByOrganization']);
            Route::get('/user/{userId}', [DoctorProfileController::class, 'getByUser']);
        });

        // Service Management Routes
        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceController::class, 'index']);
            Route::post('/', [ServiceController::class, 'store']);
            Route::get('/{id}', [ServiceController::class, 'show']);
            Route::put('/{id}', [ServiceController::class, 'update']);
            Route::delete('/{id}', [ServiceController::class, 'destroy']);
            Route::get('/organization/{organizationId}', [ServiceController::class, 'getByOrganization']);
            Route::get('/{serviceId}/pricing', [ServiceController::class, 'getServicePricing']);
            Route::put('/{serviceId}/pricing', [ServiceController::class, 'updateServicePricing']);
        });

        // Service Pricing Management Routes
        Route::prefix('service-pricing')->group(function () {

            Route::get('/', [ServicePricingController::class, 'index']);
            Route::post('/', [ServicePricingController::class, 'store']);
            Route::get('/{id}', [ServicePricingController::class, 'show']);
            Route::put('/{id}', [ServicePricingController::class, 'update']);
            Route::delete('/{id}', [ServicePricingController::class, 'destroy']);
            Route::get('/service/{serviceId}', [ServicePricingController::class, 'getByService']);
            Route::get('/service/{serviceId}/category/{categoryId}', [ServicePricingController::class, 'getByServiceAndCategory']);
            Route::post('/bulk-update', [ServicePricingController::class, 'bulkUpdate']);
        });




        // Appointment Management Routes
        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentController::class, 'index']);
            Route::post('/', [AppointmentController::class, 'store']);
            Route::get('/{id}', [AppointmentController::class, 'show']);
            Route::put('/{id}', [AppointmentController::class, 'update']);
            Route::delete('/{id}', [AppointmentController::class, 'destroy']);
            Route::patch('/{id}/status', [AppointmentController::class, 'updateStatus']);
            Route::get('/pet/{petId}', [AppointmentController::class, 'getByPet']);
            Route::get('/professional/{type}/{id}', [AppointmentController::class, 'getByProfessional']);
            Route::get('/dashboard/stats', [AppointmentController::class, 'getDashboardStats']);
        });

        // Payment Management Routes
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/stats', [PaymentController::class, 'stats'])->name('stats');
            Route::get('/gateways', [PaymentController::class, 'gateways'])->name('gateways');
            Route::get('/my-payments', [PaymentController::class, 'userPayments'])->name('user-payments');
            Route::post('/initiate', [PaymentController::class, 'initiate'])->name('initiate');
            Route::get('/{transactionId}', [PaymentController::class, 'show'])->name('show');
            Route::post('/{transactionId}/confirm', [PaymentController::class, 'confirm'])->name('confirm');
            Route::post('/{transactionId}/refund', [PaymentController::class, 'refund'])->name('refund');
        });
});

// Payment Webhooks (No authentication required)
Route::prefix('payments')->name('payments.webhooks.')->group(function () {
    Route::post('/stripe/webhook', [PaymentController::class, 'stripeWebhook'])->name('stripe');
    Route::post('/paypal/webhook', [PaymentController::class, 'paypalWebhook'])->name('paypal');
    Route::post('/sslcommerz/success', [PaymentController::class, 'sslcommerzSuccess'])->name('sslcommerz.success');
    Route::post('/sslcommerz/fail', [PaymentController::class, 'sslcommerzFail'])->name('sslcommerz.fail');
    Route::post('/sslcommerz/cancel', [PaymentController::class, 'sslcommerzCancel'])->name('sslcommerz.cancel');
    Route::post('/sslcommerz/ipn', [PaymentController::class, 'sslcommerzIpn'])->name('sslcommerz.ipn');
});
