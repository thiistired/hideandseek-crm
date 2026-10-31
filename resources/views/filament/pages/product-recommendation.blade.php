<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Chọn khách hàng</x-slot>
        <x-slot name="description">
            Gợi ý theo luật cơ bản (L1–L5) dựa trên lịch sử mua, nhóm sản phẩm và phong cách. Đây là prototype minh họa, không dùng học máy và không ghi vào cơ sở dữ liệu.
        </x-slot>

        <x-filament::input.wrapper style="max-width: 28rem;">
            <x-filament::input.select wire:model.live="customerId">
                <option value="">— Chọn khách hàng —</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->customer_code }} – {{ $c->full_name }}</option>
                @endforeach
            </x-filament::input.select>
        </x-filament::input.wrapper>
    </x-filament::section>

    @if ($customer && $result)
        @if (! $result['has_history'])
            <x-filament::section>
                <p>Khách hàng <strong>{{ $customer->full_name }}</strong> chưa có lịch sử mua nên chưa thể gợi ý.</p>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">Lịch sử mua của {{ $customer->full_name }}</x-slot>
                <table style="width:100%; border-collapse:collapse; font-size:.875rem;">
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid rgba(128,128,128,.35);">
                            <th style="padding:.5rem;">Mã sản phẩm</th>
                            <th style="padding:.5rem;">Tên sản phẩm</th>
                            <th style="padding:.5rem;">Nhóm</th>
                            <th style="padding:.5rem;">Phong cách</th>
                            <th style="padding:.5rem; text-align:right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($result['purchases'] as $p)
                            <tr style="border-bottom:1px solid rgba(128,128,128,.2);">
                                <td style="padding:.5rem;">{{ $p['product_code'] }}</td>
                                <td style="padding:.5rem;">{{ $p['product_name'] }}</td>
                                <td style="padding:.5rem;">{{ $p['category_name'] }}</td>
                                <td style="padding:.5rem;">{{ $p['style_name'] }}</td>
                                <td style="padding:.5rem; text-align:right;">{{ number_format($p['subtotal'], 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p style="margin-top:1rem;">
                    <strong>Phong cách ưu tiên:</strong> {{ $result['preferred_style'] }}
                    &nbsp;|&nbsp;
                    <strong>Nhóm cần phối:</strong> {{ implode(', ', $result['need_categories']) }}
                </p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Sản phẩm gợi ý</x-slot>
                @if (count($result['items']) === 0)
                    <p>Không còn sản phẩm phù hợp để gợi ý.</p>
                @else
                    <table style="width:100%; border-collapse:collapse; font-size:.875rem;">
                        <thead>
                            <tr style="text-align:left; border-bottom:1px solid rgba(128,128,128,.35);">
                                <th style="padding:.5rem;">Mã sản phẩm</th>
                                <th style="padding:.5rem;">Tên sản phẩm</th>
                                <th style="padding:.5rem;">Nhóm</th>
                                <th style="padding:.5rem;">Phong cách</th>
                                <th style="padding:.5rem; text-align:right;">Đơn giá</th>
                                <th style="padding:.5rem; text-align:center;">Điểm luật</th>
                                <th style="padding:.5rem;">Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['items'] as $it)
                                <tr style="border-bottom:1px solid rgba(128,128,128,.2);">
                                    <td style="padding:.5rem;">{{ $it['code'] }}</td>
                                    <td style="padding:.5rem;">{{ $it['name'] }}</td>
                                    <td style="padding:.5rem;">{{ $it['category_name'] }}</td>
                                    <td style="padding:.5rem;">{{ $it['style_name'] }}</td>
                                    <td style="padding:.5rem; text-align:right;">{{ number_format($it['price'], 0, ',', '.') }} đ</td>
                                    <td style="padding:.5rem; text-align:center;"><strong>{{ $it['score'] }}</strong></td>
                                    <td style="padding:.5rem;">{{ $it['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </x-filament::section>
        @endif
    @endif
</x-filament-panels::page>
