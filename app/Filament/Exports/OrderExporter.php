<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Exports\Enums\ExportFormat;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('customer.name')
                ->label('Nama Pelanggan'),
            ExportColumn::make('order_number')
            ->label('Nomor Pesanan'),
            ExportColumn::make('order_name')
            ->label('Nama Pesanan'),
            ExportColumn::make('discount')
            ->label('Diskon'),
            ExportColumn::make('total'),
            ExportColumn::make('payment_method')->formatStateUsing(fn ($state) => $state->value)
            ->label('Metode Pembayaran'),
            ExportColumn::make('status')->formatStateUsing(fn ($state) => $state->value),
            ExportColumn::make('created_at')
            ->label('Tanggal dibuat'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Data Pesanan telah selesai dan ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' ekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }

    public function getFormats(): array
{
    return [
        ExportFormat::Xlsx,
    ];
}
}
