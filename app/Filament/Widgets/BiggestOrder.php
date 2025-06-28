<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;

class BiggestOrder extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Order terbesar';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }
    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate = $this->filters['endDate'] ?? null;

        if (!empty($this->filters['startDate'])) {
            $startDate = Carbon::parse($this->filters['startDate']);
        }

        if (!empty($this->filters['endDate'])) {
            $endDate = Carbon::parse($this->filters['endDate'])->endOfDay();
        }

        $query = Order::query()->orderBy('total_price', 'desc');
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_time', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('transaction_time', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('transaction_time', '<=', $endDate);
        }

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
            // ->poll('10s')
        ;
    }
}
