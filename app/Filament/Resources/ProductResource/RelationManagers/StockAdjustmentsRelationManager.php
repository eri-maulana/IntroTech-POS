<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StockAdjustmentResource;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class StockAdjustmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockAdjustments';

    public function form(Form $form): Form
    {
        return StockAdjustmentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return StockAdjustmentResource::table($table);
    }
}
