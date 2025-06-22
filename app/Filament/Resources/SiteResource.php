<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers;
use App\Models\Site;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Filament\Tables\Actions\Action;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationGroup = 'Кликер';

    protected static ?string $modelLabel = 'Сайт';
    protected static ?string $pluralModelLabel = 'Сайты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('address')
                    ->label('Адрес')
                    ->maxLength(255),

                Forms\Components\TextInput::make('site_key')
                    ->label('Уникальный ключ')
                    ->required()
                    ->maxLength(255),

                Forms\Components\FileUpload::make('screenshot_path')
                    ->label('Обложка (скриншот страницы)')
                    ->image()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Название')->searchable(),
                Tables\Columns\TextColumn::make('address')->label('Адрес')->searchable(),
                Tables\Columns\TextColumn::make('site_key')->label('Уникальный ключ')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Добавлен')->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('stats')
                ->label('Статистика')
                ->icon('heroicon-o-chart-bar')
                ->url(fn (Site $record): string => SiteResource::getUrl('stats', ['record' => $record->getKey()]))
                ->openUrlInNewTab(),
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
            'index'     => Pages\ListSites::route('/'),
            // 'create'    => Pages\CreateSite::route('/create'),
            // 'edit'      => Pages\EditSite::route('/{record}/edit'),
            'stats'     => Pages\StatsSite::route('/{record}/stats'),
        ];
    }
}
