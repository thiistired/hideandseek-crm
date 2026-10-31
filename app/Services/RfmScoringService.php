<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfmScoringService
{
    // Bảng 2.7: ngưỡng chấm điểm (điểm => ngưỡng), có thể chỉnh sửa
    private const R_DAYS  = [5 => 30, 4 => 60, 3 => 90, 2 => 180];                          // số ngày <= ngưỡng
    private const F_COUNT = [5 => 5, 4 => 4, 3 => 3, 2 => 2];                                // số giao dịch >= ngưỡng
    private const M_VND   = [5 => 10000000, 4 => 5000000, 3 => 2000000, 2 => 1000000];       // tổng tiền >= ngưỡng

    /**
     * Tính R, F, M, chấm điểm 1-5 và phân nhóm khách hàng.
     * Chỉ tính giao dịch hoàn tất tính đến ngày phân tích ($asOf, mặc định là hôm nay).
     */
    public function calculateAndSegment(?Carbon $asOf = null): Collection
    {
        $asOf = ($asOf ?? Carbon::now())->copy()->startOfDay();

        $rows = DB::table('crm_transactions')
            ->select(
                'customer_id',
                DB::raw('MAX(transaction_date) as last_purchase'),
                DB::raw('COUNT(*) as frequency'),
                DB::raw('SUM(total_amount) as monetary')
            )
            ->where('status', 'completed')
            ->where('transaction_date', '<=', $asOf->copy()->endOfDay())
            ->groupBy('customer_id')
            ->get();

        return $rows->map(function ($row) use ($asOf) {
            // quá khứ -> ngày phân tích: luôn là số dương ở cả Carbon 2 và Carbon 3
            $recency   = max(0, (int) Carbon::parse($row->last_purchase)->startOfDay()->diffInDays($asOf));
            $frequency = (int) $row->frequency;
            $monetary  = (float) $row->monetary;

            $r = $this->scoreRecency($recency);
            $f = $this->scoreAtLeast($frequency, self::F_COUNT);
            $m = $this->scoreAtLeast($monetary, self::M_VND);

            return [
                'customer_id' => $row->customer_id,
                'recency'     => $recency,
                'frequency'   => $frequency,
                'monetary'    => $monetary,
                'r_score'     => $r,
                'f_score'     => $f,
                'm_score'     => $m,
                'rfm_score'   => (int) "{$r}{$f}{$m}",
                'segment'     => $this->determineSegment($r, $f, $m),
            ];
        })->values();
    }

    // R: càng ít ngày càng cao điểm
    private function scoreRecency(int $days): int
    {
        foreach (self::R_DAYS as $score => $maxDays) {
            if ($days <= $maxDays) {
                return $score;
            }
        }
        return 1;
    }

    // F, M: giá trị càng lớn càng cao điểm
    private function scoreAtLeast(float|int $value, array $thresholds): int
    {
        foreach ($thresholds as $score => $min) {
            if ($value >= $min) {
                return $score;
            }
        }
        return 1;
    }

    // Bảng 2.8: VIP -> Nguy cơ rời bỏ -> Thường xuyên (xét theo thứ tự)
    private function determineSegment(int $r, int $f, int $m): string
    {
        $fm = (int) round(($f + $m) / 2); // .5 làm tròn lên

        if ($r >= 4 && $fm >= 4) {
            return 'VIP';
        }
        if ($r <= 2) {
            return 'Nguy cơ rời bỏ';
        }
        return 'Thường xuyên';
    }
}