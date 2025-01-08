<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Imports\ExpenseImport;
use App\Models\Expense;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    public function getTitle(): string | Htmlable
    {
        return (__('Expense'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
