<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasTenantUser;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    use HasTenantUser;

    /**
     * Get tasks/events for calendar view
     */
    public function events(Request $request)
    {
        $userProfile = $this->getCurrentUserProfile();
        $userId = $userProfile->id;
        $role = $userProfile->role;

        // Base customer query
        $customerQuery = Customer::query()->whereNotNull('next_action_date');

        // Sales scope filter
        if ($role === 'sales') {
            $customerQuery->where('assigned_sales_id', $userId);
        }

        // Date range filter (start_date, end_date)
        if ($request->has('start_date') && $request->has('end_date')) {
            $customerQuery->whereBetween('next_action_date', [
                $request->start_date,
                $request->end_date
            ]);
        } elseif ($request->has('month') && $request->has('year')) {
            $month = $request->get('month');
            $year = $request->get('year');
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            $customerQuery->whereBetween('next_action_date', [$start, $end]);
        }

        // Priority filter
        if ($request->has('priority') && $request->priority !== 'all') {
            $customerQuery->where('next_action_priority', $request->priority);
        }

        // Status filter (pending vs done)
        if ($request->has('status') && $request->status !== 'all') {
            $customerQuery->where('next_action_status', $request->status);
        }

        $customers = $customerQuery->with([
            'area',
            'leadStatus',
            'assignedSales',
            'contacts' => function ($q) {
                $q->orderBy('is_primary', 'desc');
            }
        ])->get();

        // Transform into calendar events format
        $events = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'title' => $customer->next_action_plan ? $customer->next_action_plan : 'Follow Up: ' . ($customer->company ?: 'Pelanggan #' . $customer->id),
                'customer_id' => $customer->id,
                'customer_name' => $customer->company ?: ($customer->contacts->first() ? $customer->contacts->first()->name : 'Pelanggan #' . $customer->id),
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone,
                'is_individual' => $customer->is_individual,
                'date' => Carbon::parse($customer->next_action_date)->format('Y-m-d'),
                'priority' => $customer->next_action_priority ?: 'medium',
                'status' => $customer->next_action_status ?: 'pending',
                'lead_status' => $customer->leadStatus ? $customer->leadStatus->name : null,
                'lead_status_color' => $customer->leadStatus ? $customer->leadStatus->color : '#64748b',
                'area_name' => $customer->area ? $customer->area->name : null,
                'sales_name' => $customer->assignedSales ? $customer->assignedSales->name : null,
            ];
        });

        return response()->json([
            'events' => $events,
            'total' => $events->count(),
        ]);
    }
}
