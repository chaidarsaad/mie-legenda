<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductFavorite extends BaseWidget
{
    use InteractsWithPageFilters, HasWidgetShield;
    protected static ?int $sort = 1;
    protected static ?string $heading = 'Produk paling banyak dipesan';
    protected static bool $hasPageFilters = true;

    public function table(Table $table): Table
    {
        // Default startDate ke 01/01/2025, endDate ke sekarang
        $startDate = Carbon::createFromFormat('d/m/Y', '01/01/2025')->startOfDay();
        $endDate = now()->endOfDay();

        // Override jika filter startDate diberikan
        if (!empty($this->filters['startDate'])) {
            $startDate = Carbon::parse($this->filters['startDate'])->startOfDay();
        }

        // Override jika filter endDate diberikan
        if (!empty($this->filters['endDate'])) {
            $endDate = Carbon::parse($this->filters['endDate'])->endOfDay();
        }

        $query = OrderItem::query()
            ->select([
                'order_items.product_id as id', // Alias sebagai id agar Filament mengenalinya
                'products.name as product_name',
                DB::raw('COUNT(DISTINCT order_id) as total_orders'),
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price * quantity) as total_revenue')
            ])
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereHas('order', function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } elseif ($startDate) {
                    $query->where('created_at', '>=', $startDate);
                } elseif ($endDate) {
                    $query->where('created_at', '<=', $endDate);
                }
            })
            ->groupBy('order_items.product_id', 'products.name')
            ->orderByDesc('total_quantity');




        return $table
            ->query(
                $query
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label(__('Name Product')),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Total Terjual'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Total Pendapatan')
                    ->money('IDR'),
            ])
            ->paginationPageOptions([5, 10, 25, 50, 100, 250])
            ->defaultPaginationPageOption(5)
            ->poll('10s');
    }
}
