<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\ServiceStaff;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    /**
     * Start servicing an item / whole sale.
     */
    public function startService(int $saleId, ?int $staffId = null): bool
    {
        $sale = Sale::with('items.assignment')->find($saleId);
        if (! $sale) {
            return false;
        }

        $sale->update([
            'service_status' => 'in_progress',
            'service_started_at' => now(),
            'assigned_staff_id' => $staffId ?? $sale->assigned_staff_id,
        ]);

        foreach ($sale->items as $item) {
            if ($item->assignment) {
                $item->assignment->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }
        }

        return true;
    }

    /**
     * Complete service order, calculate actual duration and commissions.
     */
    public function completeService(int $saleId): bool
    {
        return DB::transaction(function () use ($saleId) {
            $sale = Sale::with(['items.assignment', 'items.product'])->find($saleId);
            if (! $sale) {
                return false;
            }

            $completedAt = now();
            $startedAt = $sale->service_started_at ?? $sale->created_at;
            $durationMinutes = max(1, $startedAt->diffInMinutes($completedAt));

            $sale->update([
                'service_status' => 'completed',
                'service_completed_at' => $completedAt,
            ]);

            foreach ($sale->items as $item) {
                if ($item->assignment) {
                    $staffId = $item->assignment->staff_user_id;
                    $commission = $this->calculateCommission($item->product_id, $staffId, $item->subtotal);

                    $item->assignment->update([
                        'status' => 'completed',
                        'completed_at' => $completedAt,
                        'duration_actual_minutes' => $durationMinutes,
                        'commission_amount' => $commission,
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Calculate commission amount for a service item.
     */
    public function calculateCommission(int $productId, int $staffUserId, float $subtotal): float
    {
        $serviceStaff = ServiceStaff::where('product_id', $productId)
            ->where('user_id', $staffUserId)
            ->where('is_active', true)
            ->first();

        if (! $serviceStaff) {
            return 0.0;
        }

        if ($serviceStaff->commission_type === 'fixed') {
            return (float) $serviceStaff->commission_value;
        } elseif ($serviceStaff->commission_type === 'percent') {
            return (float) ($subtotal * ($serviceStaff->commission_value / 100));
        }

        return 0.0;
    }
}
