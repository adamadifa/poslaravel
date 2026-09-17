<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of Accounts (Kas & Bank).
     */
    public function index(Request $request)
    {
        $type = $request->query('type');
        $search = $request->query('search');

        $accounts = Account::when($type, fn ($q) => $q->where('type', $type))
            ->when($search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('account_code', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%");
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $totalCash = Account::where('type', 'cash')->where('is_active', true)->sum('current_balance');
        $totalBank = Account::where('type', 'bank')->where('is_active', true)->sum('current_balance');
        $totalBankAgent = Account::where('type', 'bank_agent')->where('is_active', true)->sum('current_balance');
        $totalPpob = Account::where('type', 'ppob_provider')->where('is_active', true)->sum('current_balance');
        $totalBalance = Account::where('is_active', true)->sum('current_balance');

        return view('finance.accounts.index', [
            'title' => 'Akun Kas, Bank & PPOB',
            'headerTitle' => 'Akun Kas, Bank & Agen PPOB',
            'headerDescription' => 'Kelola rekening bank, saldo kasir utama, kas operasional, rekening agen BRILink, serta deposit server PPOB.',
            'breadcrumbParent' => 'Keuangan & Finansial',
            'breadcrumbCurrent' => 'Akun Kas & Bank',
            'accounts' => $accounts,
            'totalCash' => $totalCash,
            'totalBank' => $totalBank,
            'totalBankAgent' => $totalBankAgent,
            'totalPpob' => $totalPpob,
            'totalBalance' => $totalBalance,
            'type' => $type,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created Account.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:accounts,account_code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,bank_agent,ppob_provider,other',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:100',
            'opening_balance' => 'required|numeric|min:0',
            'alert_minimum_balance' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['current_balance'] = $validated['opening_balance'];
        $validated['alert_minimum_balance'] = $validated['alert_minimum_balance'] ?? 0;
        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = true;

        if ($validated['is_default']) {
            Account::where('is_default', true)->update(['is_default' => false]);
        }

        Account::create($validated);

        return redirect()->route('accounts.index')->with('success', "Akun {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Show Account via AJAX.
     */
    public function show(Account $account)
    {
        return response()->json($account);
    }

    /**
     * Update existing Account.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_code' => "required|string|max:50|unique:accounts,account_code,{$account->id}",
            'name' => 'required|string|max:255',
            'type' => 'required|in:cash,bank,bank_agent,ppob_provider,other',
            'account_number' => 'nullable|string|max:100',
            'account_holder' => 'nullable|string|max:150',
            'bank_name' => 'nullable|string|max:100',
            'alert_minimum_balance' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $validated['alert_minimum_balance'] = $validated['alert_minimum_balance'] ?? 0;
        $validated['is_default'] = $request->has('is_default');
        $validated['is_active'] = $request->has('is_active');

        if ($validated['is_default']) {
            Account::where('id', '!=', $account->id)->where('is_default', true)->update(['is_default' => false]);
        }

        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', "Akun {$account->name} berhasil diperbarui.");
    }

    /**
     * Set Default Account.
     */
    public function setDefault(Account $account)
    {
        Account::where('id', '!=', $account->id)->where('is_default', true)->update(['is_default' => false]);
        $account->update(['is_default' => true]);

        return redirect()->route('accounts.index')->with('success', "Akun {$account->name} telah diset sebagai akun kas utama.");
    }

    /**
     * Delete Account.
     */
    public function destroy(Account $account)
    {
        if ($account->is_default) {
            return redirect()->back()->with('error', 'Akun default tidak dapat dihapus.');
        }

        $account->delete();

        return redirect()->route('accounts.index')->with('success', "Akun {$account->name} berhasil dihapus.");
    }

    /**
     * Get Mutations of an Account (Buku Mutasi / Rekening Koran) via AJAX.
     */
    public function mutations(Request $request, Account $account)
    {
        $type = $request->query('type'); // credit, debit
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $mutations = $account->mutations()
            ->with(['user'])
            ->when($type, fn ($q) => $q->where('mutation_type', $type))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate))
            ->latest('id')
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'account' => $account,
            'mutations' => $mutations,
        ]);
    }
}
