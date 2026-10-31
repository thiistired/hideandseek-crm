<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CrmTransactionItem;
use App\Models\Customer;
use App\Models\Product;

/**
 * Prototype gợi ý sản phẩm theo luật cơ bản L1 đến L5 (Bảng 3.15).
 * Chỉ đọc dữ liệu, không ghi vào cơ sở dữ liệu.
 */
class RecommendationService
{
    /** Lấy dữ liệu của một khách hàng rồi áp dụng các luật. */
    public function forCustomer(Customer $customer): array
    {
        // Sản phẩm khách đã mua trong các giao dịch hoàn tất
        $purchases = CrmTransactionItem::query()
            ->whereHas('transaction', fn ($q) => $q->where('customer_id', $customer->id)->where('status', 'completed'))
            ->with('product.category', 'product.style')
            ->get()
            ->map(fn ($item) => [
                'product_id'    => $item->product_id,
                'product_code'  => $item->product->product_code,
                'product_name'  => $item->product->name,
                'category_id'   => $item->product->category_id,
                'category_name' => $item->product->category->name,
                'style_id'      => $item->product->style_id,
                'style_name'    => $item->product->style->name,
                'subtotal'      => (float) $item->subtotal,
            ])->all();

        $catalog = Product::query()->with('category', 'style')->get()
            ->map(fn ($p) => [
                'id'            => $p->id,
                'code'          => $p->product_code,
                'name'          => $p->name,
                'category_id'   => $p->category_id,
                'category_name' => $p->category->name,
                'style_id'      => $p->style_id,
                'style_name'    => $p->style->name,
                'price'         => (float) $p->price,
                'active'        => $p->status === 'active',
            ])->all();

        $categories = Category::query()->orderBy('id')->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();

        return $this->rank($purchases, $catalog, $categories);
    }

    /**
     * Áp dụng luật L1–L5 trên dữ liệu đã nạp sẵn (mảng thuần, không truy cập cơ sở dữ liệu).
     */
    public function rank(array $purchases, array $catalog, array $categories): array
    {
        if (count($purchases) === 0) {
            return ['has_history' => false, 'preferred_style' => null, 'need_categories' => [], 'items' => [], 'purchases' => []];
        }

        // L1: phong cách ưu tiên = phong cách khách đã chi nhiều nhất (hòa thì lấy mã phong cách nhỏ hơn)
        $spendByStyle = [];
        $styleNames = [];
        foreach ($purchases as $p) {
            $spendByStyle[$p['style_id']] = ($spendByStyle[$p['style_id']] ?? 0) + $p['subtotal'];
            $styleNames[$p['style_id']] = $p['style_name'];
        }
        uksort($spendByStyle, fn ($a, $b) => $spendByStyle[$b] <=> $spendByStyle[$a] ?: $a <=> $b);
        $preferredId = array_key_first($spendByStyle);

        // L2: nhóm cần phối = danh mục chưa mua; nếu đã mua đủ thì xét tất cả danh mục
        $boughtCategoryIds = array_values(array_unique(array_column($purchases, 'category_id')));
        $need = array_values(array_filter($categories, fn ($c) => !in_array($c['id'], $boughtCategoryIds, true)));
        if (count($need) === 0) {
            $need = $categories;
        }
        $needIds = array_column($need, 'id');

        $boughtProductIds = array_column($purchases, 'product_id');
        $boughtStyleIds = array_keys($spendByStyle);

        // L3 + L4: ứng viên và điểm luật
        $candidates = [];
        foreach ($catalog as $prod) {
            if (!$prod['active'] || in_array($prod['id'], $boughtProductIds, true) || !in_array($prod['category_id'], $needIds, true)) {
                continue;
            }
            if ($prod['style_id'] === $preferredId) {
                $score = 2;
            } elseif (in_array($prod['style_id'], $boughtStyleIds, true)) {
                $score = 1;
            } else {
                $score = 0;
            }
            $prod['score'] = $score;
            $candidates[] = $prod;
        }

        // L5: mỗi nhóm cần phối chọn 1 sản phẩm điểm cao nhất (hòa thì lấy mã nhỏ hơn)
        $best = [];
        foreach ($candidates as $prod) {
            $cid = $prod['category_id'];
            if (!isset($best[$cid]) || $prod['score'] > $best[$cid]['score']
                || ($prod['score'] === $best[$cid]['score'] && strcmp($prod['code'], $best[$cid]['code']) < 0)) {
                $best[$cid] = $prod;
            }
        }

        $items = array_values($best);
        usort($items, fn ($a, $b) => $b['score'] <=> $a['score'] ?: strcmp($a['code'], $b['code']));
        $items = array_slice($items, 0, 4);

        $preferredName = $styleNames[$preferredId];
        foreach ($items as &$it) {
            $it['reason'] = match ($it['score']) {
                2 => "Cùng phong cách ưu tiên ({$preferredName}); nhóm cần phối: {$it['category_name']}",
                1 => "Cùng phong cách {$it['style_name']} với sản phẩm khách đã mua; nhóm cần phối: {$it['category_name']}",
                default => "Khác phong cách đã mua; nhóm cần phối: {$it['category_name']}",
            };
        }
        unset($it);

        return [
            'has_history'     => true,
            'preferred_style' => $preferredName,
            'need_categories' => $this->sortedNames($need),
            'items'           => $items,
            'purchases'       => $purchases,
        ];
    }

    private function sortedNames(array $categories): array
    {
        $names = array_column($categories, 'name');
        sort($names);

        return $names;
    }
}
