<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class OrderStats extends BaseWidget
{
    use InteractsWithPageTable;

    // public static function canView(): bool
    // {
    //     return auth()->user()->hasRole(['super_admin', 'manager']);
    // }

    protected function getStats(): array
    {
        $createdFrom = new Carbon($this->tableFilters['created_at']['created_from']) ?? now()->startOfMonth();
        $createdTo = new Carbon($this->tableFilters['created_at']['created_until']) ?? now()->endOfMonth();

        $count = Trend::model(Order::class)->between(start: $createdFrom, end: $createdTo)->perDay()->count();

        $profit = Trend::query(Order::query()->where('status', OrderStatus::SELESAI))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('profit');

        $total = Trend::query(Order::query()->where('status', OrderStatus::SELESAI))
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('total');

        return [
            Stat::make('Pesanan', $this->getPageTableQuery()->count())
                ->chart($count->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-shopping-bag')
                ->description('Pesanan bulan ini.')
                ->descriptionColor('gray')
                ->color('success'),

            Stat::make('Total', 'Rp ' . number_format(
                Order::query()
                    ->where('status', OrderStatus::SELESAI)
                    ->when(
                        $this->tableFilters['created_at']['created_from'] && $this->tableFilters['created_at']['created_until'],
                        fn ($query) => $query->whereDate('created_at', '>=', $createdFrom)->whereDate('created_at', '<=', $createdTo)
                    )
                    ->sum('total'), 0, ',', '.'
            )
            )
                ->chart($total->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-banknotes')
                ->description('Penjualan bulan ini.')
                ->descriptionColor('gray')
                ->color('success'),

            Stat::make('Keuntungan', 'Rp ' . number_format(
                Order::query()
                    ->where('status', OrderStatus::SELESAI)
                    ->when(
                        $this->tableFilters['created_at']['created_from'] && $this->tableFilters['created_at']['created_until'],
                        fn ($query) => $query->whereDate('created_at', '>=', $createdFrom)->whereDate('created_at', '<=', $createdTo)
                    )
                    ->sum('profit'), 0, ',', '.'
            )
            )
                ->chart($profit->map(fn (TrendValue $item) => $item->aggregate)->toArray())
                ->icon('heroicon-o-banknotes')
                ->description('Keuntungan bulan ini.')
                ->descriptionColor('gray')
                ->color('success'),
        ];
    }

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }
}
