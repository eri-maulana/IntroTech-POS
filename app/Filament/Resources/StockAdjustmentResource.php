<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\StockAdjustment;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers;
use App\Filament\Resources\ProductResource\RelationManagers\StockAdjustmentsRelationManager;
use Filament\Tables\Contracts\HasTable;
use stdClass;

class StockAdjustmentResource extends Resource
{
    use \App\Traits\HasNavigationBadge;
    
    protected static ?string $model = StockAdjustment::class;
    protected static ?string $navigationGroup = 'Stok';
    protected static ?string $navigationLabel = 'Penyesuaian Stok';
    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Pilih Produk')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->hiddenOn(StockAdjustmentsRelationManager::class),
                Forms\Components\TextInput::make('quantity_adjusted')
                    ->required()
                    ->numeric()
                    ->label('Stok yang disesuaikan'),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->label('Alasan')
                    ->maxLength(65535)
                    ->default('Tambah Stok.')
                    ->placeholder('Alasan Penyesuaian Stok')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('no')->state(
                    static function (HasTable $livewire, stdClass $rowLoop): string {
                        return (string) (
                            $rowLoop->iteration +
                            ($livewire->getTableRecordsPerPage() * (
                                $livewire->getTablePage() - 1
                            ))
                        );
                    }
                ),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->label('Nama Produk')
                    ->hiddenOn(StockAdjustmentsRelationManager::class),
                    
                Tables\Columns\TextColumn::make('quantity_adjusted')
                    ->label('Adjusted')
                    ->numeric()
                    ->alignCenter()
                    ->label('Stok Disesuaikan')
                    // ->suffix(' Kuantitas')
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->limit(50)
                    ->label('Alasan')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Dibuat pada')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Terakhir diubah')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->preload()
                    ->hiddenOn(StockAdjustmentsRelationManager::class),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStockAdjustments::route('/'),
        ];
    }

    public static function getLabel(): ?string 
    {

        $locale = app()->getLocale();

        if($locale == 'id'){
            return 'Penyesuaian Stok';
        } else {
            return 'Stock Adjustment';
        }
    }
}
