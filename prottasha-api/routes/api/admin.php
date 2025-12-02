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
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\PetController;
use App\Http\Controllers\Api\Admin\OrganizationController;
use App\Http\Controllers\Api\Admin\GroomerProfileController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\AppointmentController;
use App\Http\Controllers\Api\Admin\DonationController;
use App\Http\Controllers\Api\Admin\ProjectController;
use App\Http\Controllers\Api\Admin\DonationAllocationController;
use App\Http\Controllers\Api\Admin\DonationDeliveryController;
use App\Http\Controllers\Api\Admin\MonthlySupportProgramController;
use App\Http\Controllers\Api\Admin\ProjectVolunteerController;
use App\Http\Controllers\Api\Admin\RecurringDonationController;
use App\Http\Controllers\Api\Admin\UserProfileController;
use App\Http\Controllers\Api\Admin\MonthlyPaymentController;
use App\Http\Controllers\Api\Admin\SystemNotificationController;
use App\Http\Controllers\Api\Admin\GeographicDataController;
use App\Http\Controllers\Api\Admin\DonationDashboardController;


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

        // Donation Management Routes
        Route::apiResource('donations', DonationController::class);
        Route::controller(DonationController::class)->group(function () {
            Route::post('donations/all', 'index')->name('donations.all');
            Route::post('donations/restore/{id}', 'restore')->name('donations.restore');
            Route::patch('donations/{id}/approve', 'approve')->name('donations.approve');
            Route::get('donations-stats', 'stats')->name('donations.stats');
        });

        // Project Management Routes
        Route::apiResource('projects', ProjectController::class);
        Route::controller(ProjectController::class)->group(function () {
            Route::post('projects/all', 'index')->name('projects.all');
            Route::post('projects/restore/{id}', 'restore')->name('projects.restore');
        });

        // Donation Allocation Routes
        Route::apiResource('donation-allocations', DonationAllocationController::class);
        Route::controller(DonationAllocationController::class)->group(function () {
            Route::post('donation-allocations/all', 'index')->name('donation-allocations.all');
            Route::post('donation-allocations/restore/{id}', 'restore')->name('donation-allocations.restore');
        });

        // Donation Delivery Routes
        Route::apiResource('donation-deliveries', DonationDeliveryController::class);
        Route::controller(DonationDeliveryController::class)->group(function () {
            Route::post('donation-deliveries/all', 'index')->name('donation-deliveries.all');
            Route::post('donation-deliveries/restore/{id}', 'restore')->name('donation-deliveries.restore');
        });

        // Monthly Support Program Routes
        Route::apiResource('monthly-support-programs', MonthlySupportProgramController::class);
        Route::controller(MonthlySupportProgramController::class)->group(function () {
            Route::post('monthly-support-programs/all', 'index')->name('monthly-support-programs.all');
            Route::post('monthly-support-programs/restore/{id}', 'restore')->name('monthly-support-programs.restore');
        });

        // Project Volunteer Routes
        Route::apiResource('project-volunteers', ProjectVolunteerController::class);
        Route::controller(ProjectVolunteerController::class)->group(function () {
            Route::post('project-volunteers/all', 'index')->name('project-volunteers.all');
            Route::post('project-volunteers/restore/{id}', 'restore')->name('project-volunteers.restore');
        });

        // Recurring Donation Routes
        Route::apiResource('recurring-donations', RecurringDonationController::class);
        Route::controller(RecurringDonationController::class)->group(function () {
            Route::post('recurring-donations/all', 'index')->name('recurring-donations.all');
            Route::post('recurring-donations/restore/{id}', 'restore')->name('recurring-donations.restore');
        });

        // User Profile Routes
        Route::apiResource('user-profiles', UserProfileController::class);
        Route::controller(UserProfileController::class)->group(function () {
            Route::post('user-profiles/all', 'index')->name('user-profiles.all');
            Route::post('user-profiles/restore/{id}', 'restore')->name('user-profiles.restore');
        });

        // Monthly Payment Routes
        Route::apiResource('monthly-payments', MonthlyPaymentController::class);
        Route::controller(MonthlyPaymentController::class)->group(function () {
            Route::post('monthly-payments/all', 'index')->name('monthly-payments.all');
            Route::post('monthly-payments/restore/{id}', 'restore')->name('monthly-payments.restore');
        });

        // System Notification Routes
        Route::apiResource('system-notifications', SystemNotificationController::class);
        Route::controller(SystemNotificationController::class)->group(function () {
            Route::post('system-notifications/all', 'index')->name('system-notifications.all');
            Route::post('system-notifications/restore/{id}', 'restore')->name('system-notifications.restore');
            Route::patch('system-notifications/{id}/read', 'markAsRead')->name('system-notifications.mark-read');
            Route::patch('system-notifications/mark-all-read', 'markAllAsRead')->name('system-notifications.mark-all-read');
        });

        // Geographic Data Routes
        Route::prefix('geographic')->controller(GeographicDataController::class)->group(function () {
            Route::get('countries', 'getCountries')->name('geographic.countries');
            Route::get('divisions/{countryId?}', 'getDivisions')->name('geographic.divisions');
            Route::get('districts/{divisionId?}', 'getDistricts')->name('geographic.districts');
            Route::get('thanas/{districtId?}', 'getThanas')->name('geographic.thanas');
            Route::get('upazilas/{districtId?}', 'getUpazilas')->name('geographic.upazilas');
            Route::get('unions/{upazilaId?}', 'getUnions')->name('geographic.unions');
            Route::get('disabilities', 'getDisabilities')->name('geographic.disabilities');
            Route::get('project-categories', 'getProjectCategories')->name('geographic.project-categories');
            Route::get('hierarchy/{level}/{id}', 'getLocationHierarchy')->name('geographic.hierarchy');
        });

        // Donation Dashboard Routes
        Route::prefix('donation-dashboard')->controller(DonationDashboardController::class)->group(function () {
            Route::get('stats', 'getDashboardStats')->name('donation-dashboard.stats');
            Route::get('location-analytics', 'getDonationsByLocation')->name('donation-dashboard.location-analytics');
        });
});
