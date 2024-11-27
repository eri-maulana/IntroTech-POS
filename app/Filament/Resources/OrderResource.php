<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
use App\Enums\OrderStatus;
use Filament\Tables\Table;
use App\Enums\PaymentMethod;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;

class OrderResource extends Resource
{
    use \App\Traits\HasNavigationBadge;

    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'Transactions';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->required()
                        ->default(generateSequentialNumber(Order::class))
                        ->readOnly()
                        ->label('No. Pesanan'),
                    Forms\Components\TextInput::make('order_name')
                        ->maxLength(255)
                        ->placeholder('Nama Pesanan')
                        ->label('Nama Pesanan'),
                    Forms\Components\TextInput::make('total')
                        // ->readOnlyOn('create')
                        ->disabledOn('create')
                        ->default(0)
                        ->numeric(),
                    Forms\Components\Select::make('customer_id')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->label('Pelanggan (optional)')
                        ->placeholder('Pilih Pelanggan'),

                    Forms\Components\Group::make([
                        Forms\Components\Select::make('payment_method')
                            ->enum(PaymentMethod::class)
                            ->options(PaymentMethod::class)
                            ->default(PaymentMethod::TUNAI)
                            ->required()
                            ->label('Metode Pembayaran'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->enum(OrderStatus::class)
                            ->options(OrderStatus::class)
                            ->default(OrderStatus::MENUNGGU)
                            ->label('Status'),
                    ])->columnSpan(2)->columns(2)->label('Informasi Pesanan'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns(self::getTableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->multiple()
                    ->options(\App\Enums\PaymentMethod::class)
                    ->label('Metode Pembayaran'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->maxDate(fn(Forms\Get $get) => $get('end_date') ?: now())
                            ->native(false)
                            ->label('Tanggal Awal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false)
                            ->maxDate(now())
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->button()
                    ->color('gray')
                    ->icon('heroicon-o-printer')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('pdf.print-order', [
                            'order' => $record,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'receipt-' . $record->order_number . '.pdf');
                    })
                    ->label('Cetak'),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('gray'),
                    Tables\Actions\EditAction::make()
                        ->color('gray'),
                    Tables\Actions\Action::make('edit-transaction')
                        ->visible(fn(Order $record) => $record->status === OrderStatus::MENUNGGU)
                        ->label('Ubah Transaksi')
                        ->icon('heroicon-o-pencil')
                        ->url(fn($record) => "/orders/{$record->order_number}"),
                    Tables\Actions\Action::make('mark-as-complete')
                        ->visible(fn(Order $record) => $record->status === OrderStatus::MENUNGGU)
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn(Order $record) => $record->markAsComplete())
                        ->label('Tandai telah selesai.'),
                    Tables\Actions\Action::make('divider')->label('')->disabled(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Order $order) {
                            $order->orderDetails()->delete();
                            $order->delete();
                        }),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Support\Collection $records) {
                            $records->each(fn(Order $order) => $order->orderDetails()->delete());
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Ekspor Excel')
                    ->fileDisk('public')
                    ->color('success')
                    ->icon('heroicon-o-document-text')
                    ->exporter(\App\Filament\Exports\OrderExporter::class),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\OrderDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}/details'),
            'create-transaction' => Pages\CreateTransaction::route('{record}'),
        ];
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->searchable()
                ->sortable()
                ->label('No. Pesanan'),
            Tables\Columns\TextColumn::make('order_name')
                ->searchable()
                ->label('Nama Pesanan'),
            Tables\Columns\TextColumn::make('discount')
                ->numeric()
                ->sortable()
                ->label('Diskon'),
            Tables\Columns\TextColumn::make('total')
                ->numeric()
                ->alignCenter()
                ->sortable()
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('total')
                        ->money('IDR'),
                ),
            Tables\Columns\TextColumn::make('profit')
                ->numeric()
                ->alignCenter()
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('profit')
                        ->money('IDR'),
                )
                ->sortable()
                ->label('Keuntungan'),
            Tables\Columns\TextColumn::make('payment_method')
                ->badge()
                ->alignCenter()
                ->color('gray')
                ->label('Metode Pembayaran'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn($state) => $state->getColor())
                ->alignCenter(),
            Tables\Columns\TextColumn::make('user.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Nama Pengguna'),
            Tables\Columns\TextColumn::make('customer.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Nama Pelanggan'),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn($state) => $state->format('d M Y H:i'))
                ->label('Dibuat pada'),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn($state) => $state->format('d M Y H:i'))
                ->toggleable(isToggledHiddenByDefault: true)
                ->label('Terakhir diubah'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            TextEntry::make('order_number')
                ->color('gray')
                ->label('No. Pesanan'),
            TextEntry::make('customer.name')
                ->placeholder('-')
                ->label('Nama Pelanggan'),
            TextEntry::make('discount')
                ->color('gray')
                ->label('Diskon'),
            TextEntry::make('total')
                ->color('gray'),
            TextEntry::make('payment_method')
                ->badge()
                ->color('gray')
                ->label('Metode Pembayaran'),
            TextEntry::make('status')
                ->badge()
                ->color(fn($state) => $state
                    ->getColor()),
            TextEntry::make('created_at')
                ->dateTime()
                ->formatStateUsing(fn($state) => $state
                    ->format('d M Y H:i'))
                ->color('gray')
                ->label('Dibuat pada'),
        ]);
    }

    public static function getWidgets(): array
    {
        return [
            OrderStats::class,
        ];
    }
}
