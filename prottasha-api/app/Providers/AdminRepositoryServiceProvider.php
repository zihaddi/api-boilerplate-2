<?php

namespace App\Providers;

use App\Interfaces\Admin\AuthRepositoryInterface;
use App\Interfaces\Admin\DoctorProfileRepositoryInterface;
use App\Interfaces\Admin\ServicePricingRepositoryInterface;
use App\Repositories\Admin\AuthClientRepository as AdminAuthClientRepository;
use App\Interfaces\Admin\AuthClientRepositoryInterface as AdminAuthClientRepositoryInterface;
use App\Interfaces\Admin\RolePermissionRepositoryInterface;
use App\Interfaces\Admin\RoleRepositoryInterface;
use App\Interfaces\Admin\TreeEntityRepositoryInterface;
use App\Interfaces\Admin\UserRepositoryInterface;
use App\Repositories\Admin\AuthRepository;
use App\Repositories\Admin\DoctorProfileRepository;
use App\Repositories\Admin\RolePermissionRepository;
use App\Repositories\Admin\RoleRepository;
use App\Repositories\Admin\ServicePricingRepository;
use App\Repositories\Admin\TreeEntityRepository;
use App\Repositories\Admin\UserRepository;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\Admin\PetRepositoryInterface;
use App\Repositories\Admin\PetRepository;
use App\Interfaces\Admin\PetCategoryRepositoryInterface;
use App\Repositories\Admin\PetCategoryRepository;
use App\Interfaces\Admin\PetSubcategoryRepositoryInterface;
use App\Repositories\Admin\PetSubcategoryRepository;
use App\Interfaces\Admin\PetBreedRepositoryInterface;
use App\Repositories\Admin\PetBreedRepository;
use App\Interfaces\Admin\DonationRepositoryInterface;
use App\Repositories\Admin\DonationRepository;
use App\Interfaces\Admin\ProjectRepositoryInterface;
use App\Repositories\Admin\ProjectRepository;
use App\Interfaces\Admin\DonationAllocationRepositoryInterface;
use App\Repositories\Admin\DonationAllocationRepository;
use App\Interfaces\Admin\DonationDeliveryRepositoryInterface;
use App\Repositories\Admin\DonationDeliveryRepository;
use App\Interfaces\Admin\MonthlySupportProgramRepositoryInterface;
use App\Repositories\Admin\MonthlySupportProgramRepository;
use App\Interfaces\Admin\ProjectVolunteerRepositoryInterface;
use App\Repositories\Admin\ProjectVolunteerRepository;
use App\Interfaces\Admin\RecurringDonationRepositoryInterface;
use App\Repositories\Admin\RecurringDonationRepository;
use App\Interfaces\Admin\UserProfileRepositoryInterface;
use App\Repositories\Admin\UserProfileRepository;
use App\Interfaces\Admin\MonthlyPaymentRepositoryInterface;
use App\Repositories\Admin\MonthlyPaymentRepository;
use App\Interfaces\Admin\SystemNotificationRepositoryInterface;
use App\Repositories\Admin\SystemNotificationRepository;
use App\Interfaces\Admin\OrganizationRepositoryInterface;
use App\Repositories\Admin\OrganizationRepository;
use App\Interfaces\Admin\GroomerProfileRepositoryInterface;
use App\Repositories\Admin\GroomerProfileRepository;
use App\Interfaces\Admin\ServiceRepositoryInterface;
use App\Repositories\Admin\ServiceRepository;
use App\Interfaces\Admin\AppointmentRepositoryInterface;
use App\Repositories\Admin\AppointmentRepository;


class AdminRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AdminAuthClientRepositoryInterface::class, AdminAuthClientRepository::class);
        $this->app->bind(TreeEntityRepositoryInterface::class, TreeEntityRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(RolePermissionRepositoryInterface::class, RolePermissionRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        // Event Management Bindings


         $this->app->bind(PetRepositoryInterface::class, PetRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(GroomerProfileRepositoryInterface::class, GroomerProfileRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(AppointmentRepositoryInterface::class, AppointmentRepository::class);
        $this->app->bind(ServicePricingRepositoryInterface::class, ServicePricingRepository::class);
        $this->app->bind(DoctorProfileRepositoryInterface::class, DoctorProfileRepository::class);
       // Pet Taxonomy Bindings
        $this->app->bind(PetCategoryRepositoryInterface::class, PetCategoryRepository::class);
        $this->app->bind(PetSubcategoryRepositoryInterface::class, PetSubcategoryRepository::class);
        $this->app->bind(PetBreedRepositoryInterface::class, PetBreedRepository::class);

        // Donation Management Bindings
        $this->app->bind(DonationRepositoryInterface::class, DonationRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(DonationAllocationRepositoryInterface::class, DonationAllocationRepository::class);
        $this->app->bind(DonationDeliveryRepositoryInterface::class, DonationDeliveryRepository::class);
        $this->app->bind(MonthlySupportProgramRepositoryInterface::class, MonthlySupportProgramRepository::class);
        $this->app->bind(ProjectVolunteerRepositoryInterface::class, ProjectVolunteerRepository::class);
        $this->app->bind(RecurringDonationRepositoryInterface::class, RecurringDonationRepository::class);
        $this->app->bind(UserProfileRepositoryInterface::class, UserProfileRepository::class);
        $this->app->bind(MonthlyPaymentRepositoryInterface::class, MonthlyPaymentRepository::class);
        $this->app->bind(SystemNotificationRepositoryInterface::class, SystemNotificationRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
