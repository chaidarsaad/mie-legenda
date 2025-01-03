<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make("Export Pesanan")
                ->label('Export Pesanan Bulan Ini')
                ->url(route('download-data-pesanan'))
                ->color('primary')
                ->icon('heroicon-s-arrow-up-tray')
                ->visible(function () {
                    return Order::exists();
                })
                ->openUrlInNewTab(),
            Actions\CreateAction::make(),
        ];
    }
}
