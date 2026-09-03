<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    /**
     * Display a listing of the audit trails.
     */
    public function index(Request $request)
    {
        $query = AuditTrail::query()->with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search term in description or user_name
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('user_name', 'like', "%{$term}%")
                  ->orWhere('action', 'like', "%{$term}%")
                  ->orWhere('ip_address', 'like', "%{$term}%");
            });
        }

        $auditTrails = $query->latest()->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        // Get distinct actions for dropdown
        $actions = AuditTrail::distinct()->pluck('action')->filter()->values();

        return view('audit_trails.index', compact('auditTrails', 'users', 'actions'));
    }

    /**
     * Display detailed JSON diff / modal data for an audit trail.
     */
    public function show(AuditTrail $auditTrail)
    {
        return response()->json([
            'id' => $auditTrail->id,
            'user_name' => $auditTrail->user_name,
            'action' => $auditTrail->action,
            'description' => $auditTrail->description,
            'model' => $auditTrail->auditable_type ? class_basename($auditTrail->auditable_type) : null,
            'model_id' => $auditTrail->auditable_id,
            'ip_address' => $auditTrail->ip_address,
            'user_agent' => $auditTrail->user_agent,
            'old_values' => $auditTrail->old_values,
            'new_values' => $auditTrail->new_values,
            'created_at' => $auditTrail->created_at->format('d/m/Y H:i:s'),
        ]);
    }
}
