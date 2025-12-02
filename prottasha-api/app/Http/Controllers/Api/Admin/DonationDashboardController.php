<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Project;
use App\Models\DonationAllocation;
use App\Models\DonationDelivery;
use App\Models\MonthlySupportProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DonationDashboardController extends Controller
{
    /**
     * Get donation dashboard statistics
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $period = $request->get('period', 'this_month'); // this_month, last_month, this_year, last_year, all_time
            $dateRange = $this->getDateRange($period);

            $stats = [
                'total_donations' => $this->getTotalDonations($dateRange),
                'total_amount' => $this->getTotalDonationAmount($dateRange),
                'total_projects' => $this->getTotalProjects($dateRange),
                'active_projects' => $this->getActiveProjects(),
                'total_allocations' => $this->getTotalAllocations($dateRange),
                'total_deliveries' => $this->getTotalDeliveries($dateRange),
                'monthly_programs' => $this->getMonthlySupportStats($dateRange),
                'donor_stats' => $this->getDonorStats($dateRange),
                'recent_activities' => $this->getRecentActivities(),
                'donation_trends' => $this->getDonationTrends($period),
                'project_progress' => $this->getProjectProgress(),
                'top_donors' => $this->getTopDonors($dateRange),
                'category_breakdown' => $this->getCategoryBreakdown($dateRange),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Dashboard statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get date range for period
     */
    private function getDateRange($period)
    {
        switch ($period) {
            case 'this_month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => Carbon::now()->subMonth()->startOfMonth(),
                    'end' => Carbon::now()->subMonth()->endOfMonth()
                ];
            case 'this_year':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear()
                ];
            case 'last_year':
                return [
                    'start' => Carbon::now()->subYear()->startOfYear(),
                    'end' => Carbon::now()->subYear()->endOfYear()
                ];
            default:
                return null;
        }
    }

    /**
     * Get total donations count
     */
    private function getTotalDonations($dateRange)
    {
        $query = Donation::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
        return $query->count();
    }

    /**
     * Get total donation amount
     */
    private function getTotalDonationAmount($dateRange)
    {
        $query = Donation::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
        return $query->sum('amount');
    }

    /**
     * Get total projects count
     */
    private function getTotalProjects($dateRange)
    {
        $query = Project::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
        return $query->count();
    }

    /**
     * Get active projects count
     */
    private function getActiveProjects()
    {
        return Project::where('status', 'active')->count();
    }

    /**
     * Get total allocations
     */
    private function getTotalAllocations($dateRange)
    {
        $query = DonationAllocation::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
        return [
            'count' => $query->count(),
            'amount' => $query->sum('amount_allocated')
        ];
    }

    /**
     * Get total deliveries
     */
    private function getTotalDeliveries($dateRange)
    {
        $query = DonationDelivery::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }
        return [
            'count' => $query->count(),
            'amount' => $query->sum('amount_delivered'),
            'delivered' => $query->where('status', 'delivered')->count(),
            'pending' => $query->where('status', 'pending')->count()
        ];
    }

    /**
     * Get monthly support program stats
     */
    private function getMonthlySupportStats($dateRange)
    {
        $query = MonthlySupportProgram::query();
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        return [
            'total_programs' => $query->count(),
            'active_programs' => $query->where('status', 'active')->count(),
            'total_monthly_amount' => $query->where('status', 'active')->sum('monthly_amount'),
            'total_paid' => $query->sum('total_paid')
        ];
    }

    /**
     * Get donor statistics
     */
    private function getDonorStats($dateRange)
    {
        $donorQuery = User::whereHas('donations', function ($query) use ($dateRange) {
            if ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }
        });

        return [
            'total_donors' => $donorQuery->count(),
            'new_donors' => $dateRange ? User::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count() : 0,
            'repeat_donors' => $donorQuery->has('donations', '>', 1)->count()
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        return [
            'recent_donations' => Donation::with(['donor', 'project'])
                ->latest()
                ->limit(10)
                ->get(),
            'recent_projects' => Project::with(['category'])
                ->latest()
                ->limit(5)
                ->get(),
            'recent_deliveries' => DonationDelivery::with(['donation.donor', 'deliveredTo'])
                ->latest()
                ->limit(5)
                ->get()
        ];
    }

    /**
     * Get donation trends
     */
    private function getDonationTrends($period)
    {
        $groupBy = match($period) {
            'this_month', 'last_month' => 'DATE(created_at)',
            'this_year', 'last_year' => 'MONTH(created_at)',
            default => 'MONTH(created_at)'
        };

        $dateRange = $this->getDateRange($period);
        $query = Donation::selectRaw("$groupBy as period, COUNT(*) as count, SUM(amount) as total_amount");

        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        return $query->groupBy(DB::raw($groupBy))->orderBy('period')->get();
    }

    /**
     * Get project progress
     */
    private function getProjectProgress()
    {
        return Project::select([
            'id', 'title', 'target_amount', 'raised_amount', 'completion_percentage', 'status'
        ])->whereIn('status', ['active', 'completed'])
            ->orderBy('completion_percentage', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get top donors
     */
    private function getTopDonors($dateRange)
    {
        $query = User::select('users.*')
            ->selectRaw('SUM(donations.amount) as total_donated, COUNT(donations.id) as donation_count')
            ->join('donations', 'users.id', '=', 'donations.donor_id')
            ->with('userProfile');

        if ($dateRange) {
            $query->whereBetween('donations.created_at', [$dateRange['start'], $dateRange['end']]);
        }

        return $query->groupBy('users.id')
            ->orderBy('total_donated', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown($dateRange)
    {
        $query = DB::table('projects')
            ->join('project_categories', 'projects.category_id', '=', 'project_categories.id')
            ->join('donations', 'projects.id', '=', 'donations.project_id')
            ->select('project_categories.name as category_name')
            ->selectRaw('COUNT(donations.id) as donation_count, SUM(donations.amount) as total_amount');

        if ($dateRange) {
            $query->whereBetween('donations.created_at', [$dateRange['start'], $dateRange['end']]);
        }

        return $query->groupBy('project_categories.id', 'project_categories.name')
            ->orderBy('total_amount', 'desc')
            ->get();
    }

    /**
     * Get donation analytics by location
     */
    public function getDonationsByLocation(Request $request)
    {
        try {
            $level = $request->get('level', 'district'); // district, division, country
            $dateRange = $this->getDateRange($request->get('period', 'this_month'));

            $query = DB::table('donations')
                ->join('projects', 'donations.project_id', '=', 'projects.id')
                ->join('districts', 'projects.district_id', '=', 'districts.id')
                ->join('divisions', 'districts.division_id', '=', 'divisions.id')
                ->join('countries', 'divisions.country_id', '=', 'countries.id');

            if ($dateRange) {
                $query->whereBetween('donations.created_at', [$dateRange['start'], $dateRange['end']]);
            }

            switch ($level) {
                case 'country':
                    $query->select('countries.name as location_name')
                        ->selectRaw('COUNT(donations.id) as donation_count, SUM(donations.amount) as total_amount')
                        ->groupBy('countries.id', 'countries.name');
                    break;
                case 'division':
                    $query->select('divisions.name as location_name', 'countries.name as country_name')
                        ->selectRaw('COUNT(donations.id) as donation_count, SUM(donations.amount) as total_amount')
                        ->groupBy('divisions.id', 'divisions.name', 'countries.name');
                    break;
                default: // district
                    $query->select('districts.name as location_name', 'divisions.name as division_name')
                        ->selectRaw('COUNT(donations.id) as donation_count, SUM(donations.amount) as total_amount')
                        ->groupBy('districts.id', 'districts.name', 'divisions.name');
            }

            $result = $query->orderBy('total_amount', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Location-based donation analytics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
