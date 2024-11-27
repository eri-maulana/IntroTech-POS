<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use App\Models\Product;
use App\Enums\OrderStatus;
use App\Models\OrderDetail;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use App\Filament\Resources\OrderResource;

class CreateTransaction extends Page implements HasForms
{

    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.create-transaction';

    public Order $record;
    public mixed $selectedProduct;
    public int $quantityValue = 1;
    public int $discount = 0;

    public function getTitle(): string
    {
        return "Pesanan: {$this->record->order_number}";
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedProduct')
                ->label('Pilih Produk')
                ->searchable()
                ->preload()
                ->options(Product::pluck('name', 'id')->toArray())
                ->live()
                ->afterStateUpdated(function ($state) {
                    $product = Product::find($state);
                    $this->record->orderDetails()->updateOrCreate(
                        [
                            'order_id' => $this->record->id,
                            'product_id' => $state,
                        ],
                        [
                            'product_id' => $state,
                            'quantity' => $this->quantityValue,
                            'price' => $product->price,
                            'subtotal' => $product->price * $this->quantityValue,
                        ]
                    );
                }),
        ];
    }

    public function updateQuantity(OrderDetail $orderDetail, $quantity): void
    {
        if ($quantity > 0) {
            $orderDetail->update([
                'quantity' => $quantity,
                'subtotal' => $orderDetail->price * $quantity,
            ]);
        }
    }

    public function removeProduct(OrderDetail $orderDetail): void
    {
        $orderDetail->delete();

        $this->dispatch('productRemoved');
    }

    public function updateOrder(): void
    {
        $subtotal = $this->record->orderDetails->sum('subtotal');

        $this->record->update([
            'discount' => $this->discount,
            'total' => $subtotal - $this->discount,
        ]);
    }

    public function finalizeOrder(): void
    {
        $this->updateOrder();
        $this->record->update(['status' => OrderStatus::SELESAI]);
        $this->redirect('/orders');
    }

    public function saveAsDraft(): void
    {
        $this->updateOrder();
        $this->redirect('/orders');
    }
}
