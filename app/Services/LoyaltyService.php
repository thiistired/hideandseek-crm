<?php

namespace App\Services;

use App\Models\CrmTransaction;
use App\Models\Customer;
use App\Models\MembershipTier;
use App\Models\PointTransaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Xử lý nghiệp vụ giao dịch – tích điểm – xét hạng (BR02 đến BR07).
 */
class LoyaltyService
{
    /**
     * Ghi nhận một giao dịch hoàn tất, tích điểm và xét lại hạng cho khách.
     *
     * @param  array<int, array{product_id:int|string, quantity:int|string}>  $items
     */
    public function recordTransaction(int $customerId, string $date, array $items, ?string $note, int $userId): CrmTransaction
    {
        $items = $this->mergeItems($items);

        if (count($items) === 0) {
            throw ValidationException::withMessages(['data.items' => 'Giao dịch phải có ít nhất một sản phẩm.']);
        }

        return DB::transaction(function () use ($customerId, $date, $items, $note, $userId) {
            // Khóa dòng khách hàng để hai thao tác cùng lúc không làm sai điểm
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);

            // Hệ số điểm của hạng TRƯỚC giao dịch này (BR05)
            $rate = (float) MembershipTier::findOrFail($customer->membership_tier_id)->point_rate;

            $products = Product::query()->whereIn('id', array_keys($items))->get()->keyBy('id');

            $total = 0;
            $lines = [];
            foreach ($items as $productId => $qty) {
                $price = (float) $products[$productId]->price;   // đơn giá lấy từ danh mục sản phẩm
                $subtotal = $price * $qty;
                $total += $subtotal;
                $lines[] = [
                    'product_id' => $productId,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'subtotal'   => $subtotal,
                ];
            }

            $transaction = CrmTransaction::create([
                'transaction_code' => $this->nextCode(),
                'customer_id'      => $customer->id,
                'transaction_date' => $date,
                'total_amount'     => $total,
                'status'           => 'completed',
                'created_by'       => $userId,
                'note'             => $note,
            ]);

            $transaction->items()->createMany($lines);

            // Điểm = làm tròn xuống (tổng tiền / 100.000 × hệ số hạng trước đó)
            $earned = (int) floor(round($total / 100000 * $rate, 6));

            if ($earned > 0) {
                PointTransaction::create([
                    'customer_id'              => $customer->id,
                    'points_change'            => $earned,
                    'reason'                   => 'Tích điểm từ ' . $transaction->transaction_code,
                    'reference_transaction_id' => $transaction->id,
                    'created_by'               => $userId,
                ]);
            }

            $this->refreshCustomer($customer);

            return $transaction;
        });
    }

    /**
     * Điều chỉnh điểm thủ công (UC09, BR07): bắt buộc có lý do, số điểm khác 0,
     * điểm hiện tại sau điều chỉnh không âm. Không thay đổi hạng (BR06).
     */
    public function adjustPoints(int $customerId, int $points, string $reason, int $userId): PointTransaction
    {
        $reason = trim($reason);

        if ($points === 0) {
            throw ValidationException::withMessages(['points' => 'Số điểm điều chỉnh phải khác 0.']);
        }
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'Phải nhập lý do điều chỉnh.']);
        }

        return DB::transaction(function () use ($customerId, $points, $reason, $userId) {
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);
            $current = (int) PointTransaction::where('customer_id', $customer->id)->sum('points_change');

            if ($current + $points < 0) {
                throw ValidationException::withMessages([
                    'points' => "Điểm hiện tại là {$current}; không thể điều chỉnh làm điểm âm.",
                ]);
            }

            $row = PointTransaction::create([
                'customer_id'              => $customer->id,
                'points_change'            => $points,
                'reason'                   => $reason,
                'reference_transaction_id' => null,
                'created_by'               => $userId,
            ]);

            $customer->update(['current_points' => $current + $points]);

            return $row;
        });
    }

    /** Điểm hiện tại = tổng lịch sử điểm; hạng = hạng cao nhất có min_spending <= chi tiêu hoàn tất (BR06, BR07). */
    private function refreshCustomer(Customer $customer): void
    {
        $spent = (float) CrmTransaction::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $tier = MembershipTier::query()
            ->where('min_spending', '<=', $spent)
            ->orderByDesc('min_spending')
            ->first();

        $customer->update([
            'current_points'     => (int) PointTransaction::where('customer_id', $customer->id)->sum('points_change'),
            'membership_tier_id' => $tier?->id ?? $customer->membership_tier_id,
        ]);
    }

    /** Gộp các dòng trùng sản phẩm (bảng chi tiết không cho trùng sản phẩm trong một giao dịch). */
    private function mergeItems(array $items): array
    {
        $merged = [];
        foreach ($items as $item) {
$productId = (int) ($item['product_id'] ?? 0);
            $qty = (int) ($item['quantity'] ?? 0);
            if ($productId > 0 && $qty > 0) {
                $merged[$productId] = ($merged[$productId] ?? 0) + $qty;
            }
        }
        return $merged;
    }

    private function nextCode(): string
    {
        $last = CrmTransaction::query()->lockForUpdate()->max('transaction_code');
        $number = $last ? ((int) substr($last, 2)) + 1 : 1;

        return 'GD' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}