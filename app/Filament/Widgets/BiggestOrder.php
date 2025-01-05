<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BiggestOrder extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Order terbesar';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }
    public function table(Table $table): Table
    {
        $query = Order::query()->orderBy('total_price', 'desc')->take(10);

        return $table
            ->query(
                $query
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_time')
                    ->label('Tanggal Transaksi')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->prefix('Rp ')
                    ->label(__('Total Price'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('Payment Method')),
            ])->defaultPaginationPageOption(5)
            ->poll('10s');
    }
}
