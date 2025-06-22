<?php

namespace App\Filament\Resources\ClickResource\Pages;

use App\Filament\Resources\ClickResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClick extends EditRecord
{
    protected static string $resource = ClickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
