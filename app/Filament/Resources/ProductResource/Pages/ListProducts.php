<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Imports\ProductImport;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Upload Produk')
                ->label('Upload Product')
                ->icon('heroicon-s-arrow-up-tray')
                ->color('danger')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Template Produk')
                ])
                ->action(function (array $data) {
                    $file = public_path('storage/' . $data['attachment']);
                    try {
                        Excel::import(new ProductImport, $file);
                        Notification::make()
                            ->success()
                            ->title('Product imported')
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Product failed to import')
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
            Action::make("Download Template")
                ->url(route('download-template-product'))
                ->color('success')
                ->icon('heroicon-s-arrow-down-tray'),
            Action::make("Export Produk & Kategori")
                ->label('Export Produk & Kategori')
                ->url(route('download-data'))
                ->color('primary')
                ->icon('heroicon-s-arrow-up-tray')
                ->visible(function () {
                    return Product::exists();
                }),
            Actions\CreateAction::make(),
        ];
    }
}
