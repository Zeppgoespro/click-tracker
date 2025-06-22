<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClickResource\Pages;
use App\Filament\Resources\ClickResource\RelationManagers;
use App\Models\Click;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Actions\CreateAction;

class ClickResource extends Resource
{
    protected static ?string $model = Click::class;

    protected static ?string $navigationIcon = 'heroicon-o-cursor-arrow-rays';
    protected static ?string $navigationGroup = 'Кликер';

    protected static ?string $modelLabel = 'Клик';
    protected static ?string $pluralModelLabel = 'Клики';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('site_id')
                    ->label('Выберите сайт')
                    ->relationship('site', 'name')
                    ->required(),

                Forms\Components\TextInput::make('page_url')
                    ->label('Адрес страницы')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DateTimePicker::make('clicked_at')
                    ->label('Время клика')
                    ->required(),

                Forms\Components\TextInput::make('x_coordinate')
                    ->label('X координата')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('y_coordinate')
                    ->label('Y координата')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site.name')->label('Название')->searchable(),
                // Tables\Columns\TextColumn::make('site.address')->label('Адрес сайта')->searchable(),
                Tables\Columns\TextColumn::make('page_url')->label('Адрес страницы')->searchable(),
                Tables\Columns\TextColumn::make('clicked_at')->label('Время клика')->dateTime(),
                Tables\Columns\TextColumn::make('x_coordinate')->label('X координата'),
                Tables\Columns\TextColumn::make('y_coordinate')->label('Y координата'),
            ])
            ->headerActions([
                CreateAction::make()->hidden(),
            ])
            ->filters([
                //
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

    // Запрещаем возможность создавать новые записи
    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClicks::route('/'),
            // 'create' => Pages\CreateClick::route('/create'),
            // 'edit' => Pages\EditClick::route('/{record}/edit'),
        ];
    }
}
