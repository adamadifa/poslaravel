<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use App\Services\FinanceService;
use App\Services\PurchasingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PurchasingApiController extends BaseApiController
{
    protected PurchasingService $purchasingService;

    protected FinanceService $financeService;

    public function __construct(PurchasingService $purchasingService, FinanceService $financeService)
    {
        $this->purchasingService = $purchasingService;
        $this->financeService = $financeService;
    }

    /**
     * Get list of Purchase Orders.
     */
    public function getOrders(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $status = $request->query('status');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $orders = PurchaseOrder::with(['supplier', 'warehouse', 'items.product.baseUnit', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($supplierId, function ($query, $supplierId) {
                $query->where('supplier_id', $supplierId);
            })
            ->when($warehouseId, function ($query, $warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('order_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('order_date', '<=', $endDate);
            })
            ->latest('order_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($orders, 'Data Purchase Order berhasil dimuat.');
    }

    /**
     * Get single Purchase Order detail.
     */
    public function showOrder(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'items.product.baseUnit',
            'items.unit',
            'receipts.items.product',
        ]);

        return $this->sendResponse($purchaseOrder, 'Detail Purchase Order berhasil dimuat.');
    }

    /**
     * Store a newly created Purchase Order.
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'in:draft,sent'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $po = $this->purchasingService->createPurchaseOrder($validator->validated());

            return $this->sendResponse($po, "Purchase Order {$po->po_number} berhasil dibuat.", 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal membuat PO: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update an existing Purchase Order.
     */
    public function updateOrder(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (in_array($purchaseOrder->status, ['received', 'partial'])) {
            return $this->sendError('PO yang sudah memiliki penerimaan barang (GRN) tidak dapat diedit.', [], 422);
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'in:draft,sent'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $po = $this->purchasingService->updatePurchaseOrder($purchaseOrder, $validator->validated());

            return $this->sendResponse($po, "Purchase Order {$po->po_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal memperbarui PO: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Update PO status (e.g. mark as sent or cancelled).
     */
    public function updateOrderStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => ['required', 'in:draft,sent,cancelled'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        if (in_array($purchaseOrder->status, ['received', 'partial']) && $request->input('status') === 'cancelled') {
            return $this->sendError('PO yang sudah diterima tidak dapat dibatalkan.', [], 422);
        }

        $purchaseOrder->update(['status' => $request->input('status')]);

        return $this->sendResponse(
            $purchaseOrder->load(['supplier', 'warehouse']),
            "Status PO {$purchaseOrder->po_number} diubah menjadi {$purchaseOrder->status}."
        );
    }

    /**
     * Delete an existing Purchase Order.
     */
    public function destroyOrder(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (in_array($purchaseOrder->status, ['received', 'partial'])) {
            return $this->sendError('PO yang sudah memiliki penerimaan barang (GRN) tidak dapat dihapus.', [], 422);
        }

        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return $this->sendResponse(null, "Purchase Order {$poNumber} berhasil dihapus.");
    }

    /**
     * Get list of Goods Receipts (GRN).
     */
    public function getReceipts(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $receipts = PurchaseReceipt::with(['supplier', 'warehouse', 'purchaseOrder', 'items.product.baseUnit', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('grn_number', 'like', "%{$search}%")
                    ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($startDate, fn ($q) => $q->whereDate('receipt_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('receipt_date', '<=', $endDate))
            ->latest('receipt_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($receipts, 'Data Penerimaan Barang (GRN) berhasil dimuat.');
    }

    /**
     * Store a newly created Goods Receipt (GRN).
     */
    public function storeReceipt(Request $request): JsonResponse
    {
        $input = $request->all();

        // Support aliases between web & mobile
        if (! isset($input['receipt_date']) && isset($input['received_date'])) {
            $input['receipt_date'] = $input['received_date'];
        }
        if (! isset($input['supplier_invoice_number']) && isset($input['invoice_number'])) {
            $input['supplier_invoice_number'] = $input['invoice_number'];
        }

        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as &$item) {
                if (! isset($item['unit_cost']) && isset($item['cost_price'])) {
                    $item['unit_cost'] = $item['cost_price'];
                }
            }
            unset($item);
        }

        $validator = Validator::make($input, [
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'receipt_date' => ['required', 'date'],
            'supplier_invoice_number' => ['nullable', 'string', 'max:100'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity_received' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.purchase_order_item_id' => ['nullable', 'exists:purchase_order_items,id'],
            'items.*.batch_number' => ['nullable', 'string'],
            'items.*.expiry_date' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $grn = $this->purchasingService->processGoodsReceipt($validator->validated());

            return $this->sendResponse($grn, "Penerimaan Barang {$grn->grn_number} berhasil dicatat.", 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal mencatat penerimaan: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Get list of Purchase Returns.
     */
    public function getReturns(Request $request): JsonResponse
    {
        $search = $request->query('q') ?? $request->query('search');
        $supplierId = $request->query('supplier_id');
        $warehouseId = $request->query('warehouse_id');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $returns = PurchaseReturn::with(['supplier', 'warehouse', 'purchaseReceipt', 'items.product.baseUnit', 'items.unit'])
            ->when($search, function ($query, $search) {
                $query->where('return_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($startDate, fn ($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('return_date', '<=', $endDate))
            ->latest('return_date')
            ->latest('id')
            ->get();

        return $this->sendResponse($returns, 'Data Retur Pembelian berhasil dimuat.');
    }

    /**
     * Get single Purchase Return detail.
     */
    public function showReturn(PurchaseReturn $purchaseReturn): JsonResponse
    {
        $purchaseReturn->load(['supplier', 'warehouse', 'purchaseReceipt', 'user', 'items.product.baseUnit', 'items.unit']);

        return $this->sendResponse($purchaseReturn, 'Detail Retur Pembelian berhasil dimuat.');
    }

    /**
     * Store a newly created Purchase Return.
     */
    public function storeReturn(Request $request): JsonResponse
    {
        $input = $request->all();

        if (! isset($input['return_date']) && isset($input['returned_date'])) {
            $input['return_date'] = $input['returned_date'];
        }

        if (isset($input['items']) && is_array($input['items'])) {
            foreach ($input['items'] as &$item) {
                if (isset($item['quantity_returned']) && ! isset($item['quantity'])) {
                    $item['quantity'] = $item['quantity_returned'];
                }
                if (! isset($item['unit_cost']) && isset($item['cost'])) {
                    $item['unit_cost'] = $item['cost'];
                } elseif (! isset($item['unit_cost']) && isset($item['cost_price'])) {
                    $item['unit_cost'] = $item['cost_price'];
                }
            }
            unset($item);
        }

        $validator = Validator::make($input, [
            'purchase_receipt_id' => ['nullable', 'exists:purchase_receipts,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'return_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.unit_id' => ['required', 'exists:units,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string'],
            'items.*.purchase_receipt_item_id' => ['nullable', 'exists:purchase_receipt_items,id'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $return = $this->purchasingService->processPurchaseReturn($validator->validated());

            return $this->sendResponse($return, "Retur Pembelian {$return->return_number} berhasil dicatat.", 201);
        } catch (\Exception $e) {
            return $this->sendError('Gagal mencatat retur: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Cancel / delete a Purchase Return.
     */
    public function destroyReturn(PurchaseReturn $purchaseReturn): JsonResponse
    {
        try {
            $returnNumber = $purchaseReturn->return_number;
            $this->purchasingService->cancelPurchaseReturn($purchaseReturn);

            return $this->sendResponse(null, "Retur Pembelian {$returnNumber} berhasil dibatalkan dan stok dikembalikan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal membatalkan retur: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Get list of Account Payables (Hutang Pembelian).
     */
    public function getPayables(Request $request): JsonResponse
    {
        $supplierId = $request->query('supplier_id');
        $paymentStatus = $request->query('payment_status'); // unpaid, partial, paid, all
        $search = $request->query('q') ?? $request->query('search');

        $receipts = PurchaseReceipt::with(['supplier', 'warehouse', 'purchaseOrder', 'payments.account'])
            ->where('status', 'confirmed')
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($paymentStatus && $paymentStatus !== 'all', function ($q) use ($paymentStatus) {
                $q->where('payment_status', $paymentStatus);
            }, function ($q) use ($paymentStatus) {
                if ($paymentStatus !== 'all') {
                    $q->whereIn('payment_status', ['unpaid', 'partial']);
                }
            })
            ->when($search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('grn_number', 'like', "%{$search}%")
                        ->orWhere('supplier_invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($sp) => $sp->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('receipt_date')
            ->get();

        $totalOutstanding = PurchaseReceipt::where('status', 'confirmed')
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(grand_total - paid_amount) as total')
            ->value('total') ?? 0;

        return $this->sendResponse([
            'total_outstanding' => (float) $totalOutstanding,
            'payables' => $receipts,
        ], 'Data Hutang Usaha berhasil dimuat.');
    }

    /**
     * Store AP Payment (Pelunasan Hutang Supplier).
     */
    public function storePayablePayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'purchase_receipt_id' => ['required', 'exists:purchase_receipts,id'],
            'account_id' => ['required', 'exists:accounts,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,transfer,check,other'],
            'reference_number' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi Gagal', $validator->errors()->all(), 422);
        }

        try {
            $payment = $this->financeService->processPayablePayment($validator->validated());

            return $this->sendResponse(
                $payment->load(['payable.supplier', 'account']),
                "Pembayaran hutang {$payment->payment_number} sebesar Rp ".number_format($payment->amount, 0, ',', '.').' berhasil dicatat.',
                201
            );
        } catch (\Exception $e) {
            return $this->sendError('Gagal memproses pembayaran: '.$e->getMessage(), [], 500);
        }
    }

    /**
     * Get payment history for a specific Purchase Receipt.
     */
    public function getPayablePayments(PurchaseReceipt $purchaseReceipt): JsonResponse
    {
        $payments = $purchaseReceipt->payments()->with(['account', 'creator'])->get();

        return $this->sendResponse($payments, 'Histori pembayaran berhasil dimuat.');
    }

    /**
     * Cancel / Delete an existing Payment (AP or AR).
     */
    public function destroyPayment(Payment $payment): JsonResponse
    {
        try {
            $paymentNumber = $payment->payment_number;
            $this->financeService->cancelPayment($payment);

            return $this->sendResponse(null, "Pembayaran {$paymentNumber} berhasil dibatalkan dan saldo kas dikembalikan.");
        } catch (\Exception $e) {
            return $this->sendError('Gagal membatalkan pembayaran: '.$e->getMessage(), [], 500);
        }
    }
}
