<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use Filament\Actions;
use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;
    
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }

    public function getTabs(): array
    {
        $statuses = collect([
            'semua' => ['label' => 'Semua', 'badgeColor' => 'primary', 'status' => null],
            OrderStatus::MENUNGGU->name => ['label' => 'Menunggu', 'badgeColor' => 'warning', 'status' => OrderStatus::MENUNGGU],
            OrderStatus::SELESAI->name => ['label' => 'Selesai', 'badgeColor' => 'success', 'status' => OrderStatus::SELESAI],
            OrderStatus::DIBATALKAN->name => ['label' => 'Dibatalkan', 'badgeColor' => 'danger', 'status' => OrderStatus::DIBATALKAN],
        ]);

        return $statuses->mapWithKeys(function ($data, $key) {
            $badgeCount = is_null($data['status'])
                ? Order::count()
                : Order::where('status', $data['status'])->count();

            return [$key => Tab::make($data['label'])
                ->badge($badgeCount)
                ->modifyQueryUsing(fn($query) => is_null($data['status']) ? $query : $query->where('status', $data['status']))
                ->badgeColor($data['badgeColor'])];
        })->toArray();
    }
}
