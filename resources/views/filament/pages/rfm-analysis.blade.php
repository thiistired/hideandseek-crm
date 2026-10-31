<x-filament-panels::page>
    @php
        $colors = ['VIP' => 'success', 'Thường xuyên' => 'info', 'Nguy cơ rời bỏ' => 'danger', 'Chưa phân nhóm' => 'gray'];
    @endphp

    <x-filament::section>
        {{ $this->form }}
        <p style="margin-top:.75rem; font-size:.875rem; opacity:.8;">
            Kết quả tính tại ngày {{ $asOfLabel }} từ các giao dịch hoàn tất, không lưu thành bảng riêng.
        </p>
    </x-filament::section>

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(10rem, 1fr)); gap:1rem;">
        @foreach ($counts as $name => $count)
            <x-filament::section>
                <div style="font-size:.875rem; opacity:.8;">{{ $name }}</div>
                <div style="font-size:1.75rem; font-weight:700;">{{ $count }}</div>
            </x-filament::section>
        @endforeach
    </div>

    <x-filament::section>
        <table style="width:100%; border-collapse:collapse; font-size:.875rem;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid rgba(128,128,128,.35);">
                    <th style="padding:.5rem;">Mã KH</th>
                    <th style="padding:.5rem;">Họ và tên</th>
                    <th style="padding:.5rem; text-align:center;">R (ngày)</th>
                    <th style="padding:.5rem; text-align:center;">F</th>
                    <th style="padding:.5rem; text-align:right;">M (đ)</th>
                    <th style="padding:.5rem; text-align:center;">Điểm R-F-M</th>
                    <th style="padding:.5rem;">Nhóm</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    <tr style="border-bottom:1px solid rgba(128,128,128,.2);">
                        <td style="padding:.5rem;">{{ $r['code'] }}</td>
                        <td style="padding:.5rem;">{{ $r['name'] }}</td>
                        <td style="padding:.5rem; text-align:center;">{{ $r['recency'] ?? '—' }}</td>
                        <td style="padding:.5rem; text-align:center;">{{ $r['frequency'] ?? '—' }}</td>
                        <td style="padding:.5rem; text-align:right;">{{ $r['monetary'] !== null ? number_format($r['monetary'], 0, ',', '.') : '—' }}</td>
                        <td style="padding:.5rem; text-align:center;">{{ $r['rfm'] ?? '—' }}</td>
                        <td style="padding:.5rem;"><x-filament::badge :color="$colors[$r['segment']]">{{ $r['segment'] }}</x-filament::badge></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p style="margin-top:.75rem; font-size:.875rem; opacity:.8;">Hiển thị {{ count($rows) }}/{{ $total }} khách hàng.</p>
    </x-filament::section>
</x-filament-panels::page>
