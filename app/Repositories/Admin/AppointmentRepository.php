<?php

namespace App\Repositories\Admin;

use App\Interfaces\Admin\AppointmentRepositoryInterface;
use App\Models\Appointment;
use App\Models\ServicePricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentRepository implements AppointmentRepositoryInterface
{
    public function index($request)
    {
        try {
            $query = Appointment::with([
                'pet.owner',
                'professional',
                'service'
            ])->filter($request->all());

            if ($request->has('per_page')) {
                $appointments = $query->paginate($request->per_page);
            } else {
                $appointments = $query->get();
            }

            return response()->json([
                'status' => true,
                'message' => 'Appointments retrieved successfully',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving appointments: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            // Get service pricing for location
            $servicePricing = ServicePricing::where('service_id', $request->service_id)
                ->where('location_type', $request->location_type)
                ->where('status', true)
                ->first();

            if (!$servicePricing) {
                return response()->json([
                    'status' => false,
                    'message' => 'Service pricing not found for the selected location type',
                    'data' => null
                ], 400);
            }

            $appointmentData = $request->only([
                'pet_id',
                'professional_id',
                'professional_type',
                'service_id',
                'scheduled_datetime',
                'duration_minutes',
                'location_type',
                'customer_notes'
            ]);

            // Set cost snapshot at booking time
            $appointmentData['base_cost'] = $servicePricing->price;
            $appointmentData['additional_fees'] = $servicePricing->additional_fees;
            $appointmentData['booked_at'] = now();

            $appointment = Appointment::create($appointmentData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Appointment created successfully',
                'data' => $appointment->load([
                    'pet.owner',
                    'professional',
                    'service'
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error creating appointment: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $appointment = Appointment::with([
                'pet.owner',
                'professional',
                'service'
            ])->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Appointment retrieved successfully',
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving appointment: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function update($id, $request)
    {
        DB::beginTransaction();
        try {
            $appointment = Appointment::findOrFail($id);

            $appointmentData = $request->only([
                'scheduled_datetime',
                'duration_minutes',
                'location_type',
                'customer_notes',
                'professional_notes'
            ]);

            // If location type changes, update pricing
            if ($request->has('location_type') && $request->location_type !== $appointment->location_type) {
                $servicePricing = ServicePricing::where('service_id', $appointment->service_id)
                    ->where('location_type', $request->location_type)
                    ->where('status', true)
                    ->first();

                if ($servicePricing) {
                    $appointmentData['base_cost'] = $servicePricing->price;
                    $appointmentData['additional_fees'] = $servicePricing->additional_fees;
                }
            }

            $appointment->update($appointmentData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Appointment updated successfully',
                'data' => $appointment->load([
                    'pet.owner',
                    'professional',
                    'service'
                ])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating appointment: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Appointment deleted successfully',
                'data' => null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error deleting appointment: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function updateStatus($id, $request)
    {
        DB::beginTransaction();
        try {
            $appointment = Appointment::findOrFail($id);
            $status = $request->status;

            $updateData = ['status' => $status];

            // Set timestamps based on status
            switch ($status) {
                case 'confirmed':
                    $updateData['confirmed_at'] = now();
                    break;
                case 'in_progress':
                    $updateData['started_at'] = now();
                    break;
                case 'completed':
                    $updateData['completed_at'] = now();
                    break;
                case 'cancelled':
                    $updateData['cancelled_at'] = now();
                    if ($request->has('cancellation_reason')) {
                        $updateData['cancellation_reason'] = $request->cancellation_reason;
                    }
                    break;
            }

            $appointment->update($updateData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Appointment status updated successfully',
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating appointment status: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByPet($petId)
    {
        try {
            $appointments = Appointment::with([
                'professional',
                'service'
            ])
            ->where('pet_id', $petId)
            ->orderBy('scheduled_datetime', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'message' => 'Pet appointments retrieved successfully',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving pet appointments: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getByProfessional($type, $id)
    {
        try {
            $appointments = Appointment::with([
                'pet.owner',
                'service'
            ])
            ->where('professional_type', $type)
            ->where('professional_id', $id)
            ->orderBy('scheduled_datetime', 'desc')
            ->get();

            return response()->json([
                'status' => true,
                'message' => ucfirst($type) . ' appointments retrieved successfully',
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving professional appointments: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function getDashboardStats($request)
    {
        try {
            $dateFrom = $request->date_from ?? Carbon::now()->startOfMonth();
            $dateTo = $request->date_to ?? Carbon::now()->endOfMonth();

            $stats = [
                'total_appointments' => Appointment::whereBetween('scheduled_datetime', [$dateFrom, $dateTo])->count(),
                'completed_appointments' => Appointment::where('status', 'completed')
                    ->whereBetween('scheduled_datetime', [$dateFrom, $dateTo])
                    ->count(),
                'cancelled_appointments' => Appointment::where('status', 'cancelled')
                    ->whereBetween('scheduled_datetime', [$dateFrom, $dateTo])
                    ->count(),
                'upcoming_appointments' => Appointment::where('scheduled_datetime', '>', now())
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->count(),
                'total_revenue' => Appointment::where('status', 'completed')
                    ->whereBetween('completed_at', [$dateFrom, $dateTo])
                    ->sum('total_cost'),
                'monthly_revenue' => Appointment::where('status', 'completed')
                    ->whereBetween('completed_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->sum('total_cost')
            ];

            return response()->json([
                'status' => true,
                'message' => 'Dashboard stats retrieved successfully',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving dashboard stats: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
