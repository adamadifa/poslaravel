<?php

namespace App\Http\Controllers;

use App\Models\CashierShift;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class CashierShiftController extends Controller
{
    /**
     * Get the current active shift for the logged-in user.
     */
    public function current(Request $request)
    {
        $userId = auth()->id();
        $shift = CashierShift::with(['warehouse', 'user'])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => $shift,
        ]);
    }

    /**
     * Open a new cashier shift (Input Modal Awal).
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'starting_cash.required' => 'Modal awal kasir wajib diisi.',
            'starting_cash.numeric' => 'Modal awal harus berupa angka valid.',
        ]);

        $userId = auth()->id();

        // Check if user already has an active open shift
        $existingShift = CashierShift::where('user_id', $userId)
            ->where('status', 'open')
            ->first();

        if ($existingShift) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda masih memiliki sesi shift kasir yang aktif.',
                'data' => $existingShift,
            ], 422);
        }

        $startingCash = (float) $validated['starting_cash'];

        $shift = CashierShift::create([
            'user_id' => $userId,
            'warehouse_id' => $validated['warehouse_id'],
            'opened_at' => now(),
            'starting_cash' => $startingCash,
            'expected_cash' => $startingCash,
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Sesi shift kasir berhasil dibuka.',
                'data' => $shift->load(['warehouse', 'user']),
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil dibuka.');
    }

    /**
     * Close an active shift (Input Kas Fisik & Hitung Selisih).
     */
    public function close(Request $request, CashierShift $shift)
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'closing_cash.required' => 'Penghitungan fisik kas aktual wajib diisi.',
        ]);

        if ($shift->status === 'closed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Shift ini sudah ditutup sebelumnya.',
            ], 422);
        }

        $closingCash = (float) $validated['closing_cash'];
        $expectedCash = (float) $shift->expected_cash;
        $diff = $closingCash - $expectedCash;

        $shift->update([
            'closed_at' => now(),
            'closing_cash' => $closingCash,
            'cash_difference' => $diff,
            'status' => 'closed',
            'notes' => $validated['notes'] ?? $shift->notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Shift kasir berhasil ditutup.',
                'data' => $shift,
            ]);
        }

        return redirect()->back()->with('success', 'Shift kasir berhasil ditutup.');
    }
}
