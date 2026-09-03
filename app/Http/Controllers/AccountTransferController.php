<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Services\FinanceService;
use Illuminate\Http\Request;

class AccountTransferController extends Controller
{
    protected FinanceService $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Display listing of Account Transfers.
     */
    public function index(Request $request)
    {
        $fromAccountId = $request->query('from_account_id');
        $toAccountId = $request->query('to_account_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $search = $request->query('search');

        $transfers = AccountTransfer::with(['fromAccount', 'toAccount', 'creator'])
            ->when($fromAccountId, fn($q) => $q->where('from_account_id', $fromAccountId))
            ->when($toAccountId, fn($q) => $q->where('to_account_id', $toAccountId))
            ->when($startDate, fn($q) => $q->whereDate('transfer_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('transfer_date', '<=', $endDate))
            ->when($search, function ($q, $search) {
                $q->where('transfer_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            })
            ->latest('transfer_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $accounts = Account::where('is_active', true)->orderBy('name')->get();
        $totalTransferred = AccountTransfer::sum('amount');

        return view('finance.transfers.index', [
            'title' => 'Transfer Antar Kas & Bank',
            'headerTitle' => 'Transfer Saldo Antar Kas & Bank',
            'headerDescription' => 'Pindahkan saldo kas toko ke rekening bank (setoran) atau tarik tunai untuk kas operasional.',
            'breadcrumbParent' => 'Keuangan & Kas',
            'breadcrumbCurrent' => 'Transfer Kas/Bank',
            'transfers' => $transfers,
            'accounts' => $accounts,
            'totalTransferred' => $totalTransferred,
            'fromAccountId' => $fromAccountId,
            'toAccountId' => $toAccountId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
        ]);
    }

    /**
     * Store a newly created Account Transfer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_account_id' => 'required|exists:accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'transfer_fee' => 'nullable|numeric|min:0',
            'transfer_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $transfer = $this->financeService->transferAccount($validated);
            return redirect()->route('account-transfers.index')->with('success', "Transfer {$transfer->transfer_number} sebesar Rp " . number_format($transfer->amount, 0, ',', '.') . " berhasil diproses.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses transfer kas: ' . $e->getMessage());
        }
    }
}
