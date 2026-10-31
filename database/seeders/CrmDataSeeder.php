<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Bộ dữ liệu kiểm thử cố định của đồ án: 20 khách hàng, 12 sản phẩm, 38 giao dịch hoàn tất.
 * Không dùng dữ liệu ngẫu nhiên để kết quả điểm, hạng và RFM tính trước được (Chương 3, mục 3.6).
 * Điểm, hạng và điểm hiện tại được suy ra từ chính các giao dịch, không gán tay.
 */
class CrmDataSeeder extends Seeder
{
    // Quy tắc tích điểm (BR05): điểm = floor(tổng tiền / 100.000 x hệ số điểm của hạng TRƯỚC giao dịch)
    private const VND_PER_POINT = 100000;

    public function run(): void
    {
        DB::transaction(function () {
            $now = '2026-10-01 08:00:00';

            // 1. Tài khoản kiểm thử (mật khẩu: password)
            DB::table('users')->insert([
                ['id' => 1, 'name' => 'Nguyễn Minh Anh', 'email' => 'manager@hideandseek.vn',
                 'password' => Hash::make('password'), 'role' => 'manager', 'status' => 'active',
                 'created_at' => $now, 'updated_at' => $now],
                ['id' => 2, 'name' => 'Trần Khánh Linh', 'email' => 'marketing@hideandseek.vn',
                 'password' => Hash::make('password'), 'role' => 'marketing', 'status' => 'active',
                 'created_at' => $now, 'updated_at' => $now],
            ]);

            // 2. Hạng thành viên
            $tiers = [
                [1, 'Member', 0, 1.00, 'Hạng thành viên cơ bản'],
                [2, 'Silver', 2000000, 1.10, 'Khách hàng có tổng chi tiêu từ 2 triệu đồng'],
                [3, 'Gold', 5000000, 1.30, 'Khách hàng có tổng chi tiêu từ 5 triệu đồng'],
                [4, 'Diamond', 10000000, 1.50, 'Khách hàng có tổng chi tiêu từ 10 triệu đồng'],
            ];
            foreach ($tiers as [$id, $name, $min, $rate, $desc]) {
                DB::table('membership_tiers')->insert([
                    'id' => $id, 'name' => $name, 'min_spending' => $min, 'point_rate' => $rate,
                    'description' => $desc, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            // 3. Danh mục và phong cách
            $categories = [
                [1, 'Top', 'Áo và các sản phẩm mặc phần thân trên'],
                [2, 'Bottom', 'Quần và các sản phẩm mặc phần thân dưới'],
                [3, 'Outerwear', 'Áo khoác và lớp mặc ngoài'],
                [4, 'Accessories', 'Phụ kiện thời trang'],
            ];
            foreach ($categories as [$id, $name, $desc]) {
                DB::table('categories')->insert([
                    'id' => $id, 'name' => $name, 'description' => $desc, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            $styles = [
                [1, 'Korean Streetwear', 'Phong cách streetwear lấy cảm hứng Hàn Quốc'],
                [2, 'Minimal', 'Phong cách tối giản'],
                [3, 'Casual', 'Phong cách thường ngày'],
                [4, 'Vintage', 'Phong cách mang cảm hứng hoài cổ'],
            ];
            foreach ($styles as [$id, $name, $desc]) {
                DB::table('styles')->insert([
                    'id' => $id, 'name' => $name, 'description' => $desc, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            // 4. Sản phẩm [id, mã, tên, category_id, style_id, đơn giá]
            $products = [
                [1, 'HDS-TS-001', 'Oversized Slub T-Shirt', 1, 1, 350000],
                [2, 'HDS-TS-002', 'Boxy Graphic T-Shirt', 1, 1, 390000],
                [3, 'HDS-TS-003', 'Minimal Basic Tee', 1, 2, 320000],
                [4, 'HDS-PT-001', 'Wide Cargo Pants', 2, 1, 520000],
                [5, 'HDS-PT-002', 'Straight Relaxed Pants', 2, 2, 490000],
                [6, 'HDS-PT-003', 'Vintage Denim Pants', 2, 4, 580000],
                [7, 'HDS-JK-001', 'Oversized Utility Jacket', 3, 1, 720000],
                [8, 'HDS-JK-002', 'Minimal Coach Jacket', 3, 2, 680000],
                [9, 'HDS-AC-001', 'Logo Cap', 4, 1, 250000],
                [10, 'HDS-AC-002', 'Canvas Tote Bag', 4, 2, 290000],
                [11, 'HDS-AC-003', 'Vintage Shoulder Bag', 4, 4, 420000],
                [12, 'HDS-TS-004', 'Vintage Wash T-Shirt', 1, 4, 410000],
            ];
            $price = [];
            foreach ($products as [$id, $code, $name, $cat, $style, $p]) {
                $price[$id] = $p;
                DB::table('products')->insert([
                    'id' => $id, 'product_code' => $code, 'name' => $name, 'category_id' => $cat,
                    'style_id' => $style, 'price' => $p, 'status' => 'active',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            // 5. Giao dịch [số thứ tự, khách hàng (id), ngày giờ, [[product_id, số lượng], ...]]
            $transactions = [
                [1, 1, '2026-07-05 10:15:00', [[1, 1], [4, 1]]],
                [2, 1, '2026-08-03 14:20:00', [[2, 1], [10, 1]]],
                [3, 1, '2026-09-18 16:10:00', [[7, 1], [9, 1]]],
                [4, 2, '2026-06-12 11:00:00', [[2, 1], [9, 1]]],
                [5, 2, '2026-08-21 15:30:00', [[3, 1], [5, 1], [10, 1]]],
                [6, 3, '2026-05-08 17:45:00', [[4, 1], [7, 1]]],
                [7, 3, '2026-07-19 13:15:00', [[3, 1], [8, 1]]],
                [8, 3, '2026-09-25 19:00:00', [[6, 1], [7, 1]]],
                [9, 4, '2026-09-05 09:30:00', [[12, 1], [10, 1]]],
                [10, 5, '2026-07-27 10:50:00', [[1, 1], [5, 1]]],
                [11, 6, '2026-08-30 18:10:00', [[2, 1], [7, 1]]],
                [12, 7, '2026-09-20 12:40:00', [[2, 1], [11, 1]]],
                [13, 9, '2026-06-14 10:00:00', [[7, 2], [4, 1]]],
                [14, 9, '2026-07-22 14:30:00', [[8, 1], [6, 1], [12, 1]]],
                [15, 9, '2026-08-25 16:00:00', [[7, 1], [5, 1], [11, 1]]],
                [16, 9, '2026-10-12 11:20:00', [[8, 1], [3, 1], [9, 1]]],
                [17, 10, '2026-05-20 09:40:00', [[7, 2], [4, 2]]],
                [18, 10, '2026-06-28 15:10:00', [[8, 2], [6, 2]]],
                [19, 10, '2026-08-09 13:00:00', [[7, 1], [8, 1], [5, 2]]],
                [20, 10, '2026-08-29 17:30:00', [[7, 2], [11, 2]]],
                [21, 10, '2026-10-16 12:15:00', [[8, 2], [4, 1], [9, 2]]],
                [22, 11, '2026-04-25 10:30:00', [[7, 2], [6, 2]]],
                [23, 11, '2026-06-03 16:20:00', [[8, 1], [4, 1], [2, 1]]],
                [24, 11, '2026-07-02 11:45:00', [[7, 1], [5, 2], [10, 1]]],
                [25, 12, '2026-02-18 14:00:00', [[1, 1], [4, 1]]],
                [26, 12, '2026-03-10 10:10:00', [[2, 1], [9, 1]]],
                [27, 13, '2026-06-09 15:40:00', [[8, 1], [5, 1]]],
                [28, 13, '2026-07-30 13:25:00', [[7, 1], [2, 1]]],
                [29, 13, '2026-08-30 18:50:00', [[4, 1], [12, 1]]],
                [30, 14, '2026-08-04 09:20:00', [[3, 1], [9, 1]]],
                [31, 14, '2026-10-05 17:00:00', [[1, 1], [10, 1], [11, 1]]],
                [32, 15, '2026-10-18 14:10:00', [[7, 2], [8, 1], [6, 1]]],
                [33, 16, '2026-07-08 12:00:00', [[2, 1], [10, 1]]],
                [34, 16, '2026-08-10 16:45:00', [[12, 1], [5, 1]]],
                [35, 17, '2026-07-21 10:05:00', [[6, 1], [9, 1]]],
                [36, 19, '2026-08-12 11:30:00', [[7, 1], [5, 1]]],
                [37, 19, '2026-10-03 15:55:00', [[8, 1], [4, 1], [1, 1]]],
                [38, 20, '2026-10-19 13:40:00', [[3, 1], [9, 1]]],
            ];
            usort($transactions, fn ($x, $y) => strcmp($x[2], $y[2]));

            // 6. Khách hàng [mã, họ tên, SĐT, email, giới tính, ngày sinh, địa chỉ]; mọi khách bắt đầu ở hạng Member, 0 điểm
            $customers = [
                ['KH0001', 'Nguyễn Minh Quân', '0901000001', 'quan.nguyen@example.com', 'male', '2002-03-15', 'Biên Hòa, Đồng Nai'],
                ['KH0002', 'Trần Ngọc Mai', '0901000002', 'mai.tran@example.com', 'female', '2003-07-21', 'Thủ Đức, TP.HCM'],
                ['KH0003', 'Lê Hoàng Nam', '0901000003', 'nam.le@example.com', 'male', '2001-11-02', 'Quận 7, TP.HCM'],
                ['KH0004', 'Phạm Khánh Vy', '0901000004', 'vy.pham@example.com', 'female', '2004-01-28', 'Biên Hòa, Đồng Nai'],
                ['KH0005', 'Võ Gia Hân', '0901000005', 'han.vo@example.com', 'female', '2002-09-10', 'Long Thành, Đồng Nai'],
                ['KH0006', 'Đặng Quốc Huy', '0901000006', 'huy.dang@example.com', 'male', '2000-05-18', 'Bình Thạnh, TP.HCM'],
                ['KH0007', 'Bùi Thanh Thảo', '0901000007', 'thao.bui@example.com', 'female', '2003-12-01', 'Dĩ An, Bình Dương'],
                ['KH0008', 'Huỳnh Nhật Minh', '0901000008', 'minh.huynh@example.com', 'male', '2001-08-25', 'Quận 1, TP.HCM'],
                ['KH0009', 'Ngô Thùy Linh', '0901000009', 'linh.ngo@example.com', 'female', '2000-04-12', 'Quận 3, TP.HCM'],
                ['KH0010', 'Dương Minh Khang', '0901000010', 'khang.duong@example.com', 'male', '1998-10-05', 'Quận 10, TP.HCM'],
                ['KH0011', 'Phan Bảo Ngọc', '0901000011', 'ngoc.phan@example.com', 'female', '2002-02-19', 'Thủ Đức, TP.HCM'],
                ['KH0012', 'Lý Gia Bảo', '0901000012', 'bao.ly@example.com', 'male', '2001-06-30', 'Biên Hòa, Đồng Nai'],
                ['KH0013', 'Trương Hà My', '0901000013', 'my.truong@example.com', 'female', '2003-09-14', 'Quận 7, TP.HCM'],
                ['KH0014', 'Đỗ Anh Tuấn', '0901000014', 'tuan.do@example.com', 'male', '2004-03-22', 'Gò Vấp, TP.HCM'],
                ['KH0015', 'Hồ Khánh An', '0901000015', 'an.ho@example.com', 'female', '1999-12-08', 'Quận 1, TP.HCM'],
                ['KH0016', 'Vũ Đức Anh', '0901000016', 'anh.vu@example.com', 'male', '2002-07-27', 'Thuận An, Bình Dương'],
                ['KH0017', 'Lâm Thu Hà', '0901000017', 'ha.lam@example.com', 'female', '2000-11-16', 'Bình Thạnh, TP.HCM'],
                ['KH0018', 'Cao Minh Triết', '0901000018', 'triet.cao@example.com', 'male', '2005-01-09', 'Quận 5, TP.HCM'],
                ['KH0019', 'Mai Phương Thảo', '0901000019', 'thao.mai@example.com', 'female', '2001-05-03', 'Long Khánh, Đồng Nai'],
                ['KH0020', 'Tạ Hoàng Long', '0901000020', 'long.ta@example.com', 'male', '2003-08-21', 'Quận 12, TP.HCM'],
            ];
            $firstDate = [];
            foreach ($transactions as [, $cid, $date]) {
                $firstDate[$cid] ??= $date;
            }
            foreach ($customers as $i => [$code, $name, $phone, $email, $gender, $dob, $address]) {
                $id = $i + 1;
                $createdAt = isset($firstDate[$id])
                    ? date('Y-m-d H:i:s', strtotime($firstDate[$id] . ' -7 days'))
                    : $now;
                DB::table('customers')->insert([
                    'id' => $id, 'customer_code' => $code, 'full_name' => $name, 'phone' => $phone,
                    'email' => $email, 'gender' => $gender, 'date_of_birth' => $dob, 'address' => $address,
                    'membership_tier_id' => 1, 'current_points' => 0,
                    'created_at' => $createdAt, 'updated_at' => $createdAt,
                ]);
            }

            // 7. Ghi nhận giao dịch theo thứ tự thời gian: tính điểm theo hạng TRƯỚC giao dịch
            $spent = [];
            $points = [];
            foreach ($transactions as [$no, $cid, $when, $lines]) {
                $code = 'GD' . str_pad($no, 4, '0', STR_PAD_LEFT);
                $total = 0;
                $rows = [];
                foreach ($lines as [$pid, $qty]) {
                    $sub = $qty * $price[$pid];
                    $total += $sub;
                    $rows[] = [
                        'transaction_id' => $no, 'product_id' => $pid, 'quantity' => $qty,
                        'unit_price' => $price[$pid], 'subtotal' => $sub, 'created_at' => $when,
                    ];
                }

                $rate = $this->tierFor($tiers, $spent[$cid] ?? 0)[3];
                $earned = (int) floor(round($total / self::VND_PER_POINT * $rate, 6));

                DB::table('crm_transactions')->insert([
                    'id' => $no, 'transaction_code' => $code, 'customer_id' => $cid, 'transaction_date' => $when,
                    'total_amount' => $total, 'status' => 'completed', 'created_by' => 2, 'note' => null,
                    'created_at' => $when, 'updated_at' => $when,
                ]);
                DB::table('crm_transaction_items')->insert($rows);
                DB::table('point_transactions')->insert([
                    'customer_id' => $cid, 'points_change' => $earned, 'reason' => 'Tích điểm từ ' . $code,
                    'reference_transaction_id' => $no, 'created_by' => 2, 'created_at' => $when,
                ]);

                $spent[$cid] = ($spent[$cid] ?? 0) + $total;
                $points[$cid] = ($points[$cid] ?? 0) + $earned;
            }

            // 8. Một lần điều chỉnh điểm của Quản lý (UC08): KH0001 trừ 5 điểm
            DB::table('point_transactions')->insert([
                'customer_id' => 1, 'points_change' => -5, 'reason' => 'Điều chỉnh điểm do đổi ưu đãi',
                'reference_transaction_id' => null, 'created_by' => 1, 'created_at' => '2026-09-22 10:00:00',
            ]);
            $points[1] -= 5;

            // 9. Hạng và điểm hiện tại suy ra từ giao dịch và lịch sử điểm (BR06, BR07)
            foreach ($customers as $i => $c) {
                $id = $i + 1;
                DB::table('customers')->where('id', $id)->update([
                    'membership_tier_id' => $this->tierFor($tiers, $spent[$id] ?? 0)[0],
                    'current_points'     => $points[$id] ?? 0,
                ]);
            }
        });

        $this->command->info('Đã nạp bộ dữ liệu kiểm thử: 20 khách hàng, 12 sản phẩm, 38 giao dịch.');
    }

    // Hạng cao nhất có min_spending không vượt quá tổng chi tiêu (BR06); trả về [id, tên, min_spending, hệ số]
    private function tierFor(array $tiers, float $spent): array
    {
        $result = [$tiers[0][0], $tiers[0][1], $tiers[0][2], $tiers[0][3]];
        foreach ($tiers as [$id, $name, $min, $rate]) {
            if ($min <= $spent) {
                $result = [$id, $name, $min, $rate];
            }
        }
        return $result;
    }
}