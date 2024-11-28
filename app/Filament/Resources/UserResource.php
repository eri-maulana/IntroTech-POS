<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Traits\HasNavigationBadge;
use Awcodes\FilamentGravatar\Gravatar;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Filament\Tables\Contracts\HasTable;
use stdClass;

class UserResource extends Resource
{
    use HasNavigationBadge;

    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Manajemen Pengguna';
    protected static ?string $navigationLabel = 'Pengguna';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar_url')
                    ->columnSpanFull()
                    ->avatar()
                    ->label('Gambar'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama')
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->label('Kata Sandi')
                    ->dehydrateStateUsing(fn($state) => bcrypt($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->minLength(8)
                    ->live(1000)
                    ->revealable()
                    ->same('passwordConfirmation'),
                Forms\Components\TextInput::make('passwordConfirmation')
                    ->password()
                    ->label('Konfirmasi Kata Sandi')
                    ->dehydrated(false)
                    ->revealable()
                    ->hidden(fn(Forms\Get $get) => $get('password') == null),

                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Peran')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->circular()
                    ->alignCenter()
                    ->label('Avatar')
                    ->default(Gravatar::get()),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn($record) => $record->email)
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn($state) => str($state)->title()->replace('_', ' ')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat pada')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? $state->format('D d M, Y') : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Terakhir diubah')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? $state->format('D d M, Y') : '-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->label('Peran')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string 
    {

        $locale = app()->getLocale();

        if($locale == 'id'){
            return 'Pengguna';
        } else {
            return 'User';
        }
    }
}
