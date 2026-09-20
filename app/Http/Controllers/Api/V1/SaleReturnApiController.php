<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Services\SaleReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SaleReturnApiController extends BaseApiController
{
    protected SaleReturnService $returnService;

    public function __construct(SaleReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Get list of Sale Returns with filters and summary.
     */
    public function getReturns(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $status = $request->query('status');
        $warehouseId = $request->query('warehouse_id');
        $refundMethod = $request->query('refund_method');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $limit = (int) ($request->query('limit', 50));

        $query = SaleReturn::with([
            'sale',
            'customer',
            'warehouse',
            'account',
            'creator',
            'items.product.baseUnit',
            'items.unit',
        ])
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('return_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%")
                        ->orWhereHas('sale', fn ($saleQ) => $saleQ->where('invoice_number', 'like', "%{$search}%"))
                        ->orWhereHas('customer', fn ($custQ) => $custQ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status && $status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($refundMethod && $refundMethod !== 'all', fn ($q) => $q->where('refund_method', $refundMethod))
            ->when($startDate, fn ($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('return_date', '<=', $endDate));

        $returns = (clone $query)->latest('return_date')->latest('id')->limit($limit)->get();

        $totalCompletedRefund = (clone $query)->where('status', 'completed')->sum('refund_amount');
        $totalItemsReturned = (clone $query)->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
            ->where('sale_return_items.type', 'return')
            ->sum('sale_return_items.quantity');

        return $this->sendResponse([
            'returns' => $returns,
            'summary' => [
                'total_refund' => (float) $totalCompletedRefund,
                'total_items_returned' => (float) $totalItemsReturned,
                'returns_count' => $returns->count(),
            ],
        ], 'Data retur penjualan berhasil dimuat.');
    }

    /**
     * Get single Sale Return detail.
     */
    public function showReturn(SaleReturn $saleReturn): JsonResponse
    {
        $saleReturn->load([
            'sale.items.product',
            'customer',
            'warehouse',
            'account',
            'creator',
            'items.product.baseUnit',
            'items.unit',
        ]);

        return $this->sendResponse($saleReturn, 'Detail retur penjualan berhasil dimuat.');
    }

    /**
     * Search / lookup invoice for creating return in mobile.
     */
    public function searchInvoices(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');

        $sales = Sale::with(['customer', 'warehouse', 'items.product.baseUnit', 'user'])
            ->where(function ($q) {
                $q->where('status', 'completed')
                    ->orWhereNull('status')
                    ->orWhere('payment_status', 'paid');
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('sale_date')
            ->latest('id')
            ->limit(30)
            ->get();

        return $this->sendResponse($sales, 'Daftar invoice siap retur berhasil dimuat.');
    }

    /**
     * Store new Sale Return via API.
     */
    public function storeReturn(Request $request): JsonResponse
    {
        // Filter empty items
        if ($request->has('items') && is_array($request->input('items'))) {
            $filteredItems = array_values(array_filter($request->input('items'), function ($item) {
                return isset($item['quantity']) && (float) $item['quantity'] > 0;
            }));
            $request->merge(['items' => $filteredItems]);
        }

        if ($request->has('replacement_items') && is_array($request->input('replacement_items'))) {
            $filteredRepItems = array_values(array_filter($request->input('replacement_items'), function ($item) {
                return isset($item['quantity']) && (float) $item['quantity'] > 0;
            }));
            $request->merge(['replacement_items' => $filteredRepItems]);
        }

        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'return_date' => 'required|date',
            'refund_method' => 'required|in:cash,credit_deduction,exchange',
            'account_id' => 'nullable|exists:accounts,id',
            'reason' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'replacement_items' => 'nullable|array',
            'replacement_items.*.product_id' => 'required_with:replacement_items|exists:products,id',
            'replacement_items.*.unit_id' => 'nullable|exists:units,id',
            'replacement_items.*.quantity' => 'required_with:replacement_items|numeric|min:0.0001',
            'replacement_items.*.unit_price' => 'required_with:replacement_items|numeric|min:0',
        ], [
            'sale_id.required' => 'Faktur invoice penjualan wajib dipilih.',
            'sale_id.exists' => 'Faktur invoice penjualan tidak valid.',
            'reason.required' => 'Alasan retur wajib diisi.',
            'items.required' => 'Minimal 1 produk harus diretur dengan kuantiti lebih dari 0.',
            'items.min' => 'Minimal 1 produk harus diretur dengan kuantiti lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $saleReturn = $this->returnService->processSaleReturn($validator->validated());
            $saleReturn->load(['sale', 'customer', 'warehouse', 'account', 'items.product.baseUnit']);

            return $this->sendResponse($saleReturn, "Retur penjualan {$saleReturn->return_number} berhasil diproses.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses retur penjualan: '.$e->getMessage(), [], 422);
        }
    }

    /**
     * Cancel Sale Return.
     */
    public function destroyReturn(SaleReturn $saleReturn): JsonResponse
    {
        try {
            $this->returnService->cancelSaleReturn($saleReturn);

            return $this->sendResponse(null, "Retur penjualan {$saleReturn->return_number} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal membatalkan retur: '.$e->getMessage(), [], 422);
        }
    }
}
