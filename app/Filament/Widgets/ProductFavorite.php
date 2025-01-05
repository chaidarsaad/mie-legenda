<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductFavorite extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Produk paling banyak dipesan';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }
    public function table(Table $table): Table
    {
        $productQuery = Product::query()
            ->withCount('orderItems')
            ->orderByDesc('order_items_count')
            ->take(10);
        return $table
            ->query(
                $productQuery
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name Product')),
                Tables\Columns\TextColumn::make('order_items_count')
                    ->label(__('Dipesan')),
            ])->defaultPaginationPageOption(5)
            ->poll('10s');
    }
}
