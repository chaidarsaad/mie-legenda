<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }
    protected function getStats(): array
    {
        $category_count = Category::count();
        $product_count = Product::count();
        $order_count = Order::count();
        $pemasukan = Order::sum('total_price');
        $pengeluaran = Expense::sum('amount');
        $laba = $pemasukan - $pengeluaran;

        return [
            Stat::make('Total Categories', $category_count),
            Stat::make('Total Product', $product_count),
            Stat::make('Total Orders', $order_count),
            Stat::make('Total Pemasukan', 'Rp ' . number_format($pemasukan, 0, ",", ",")),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($pengeluaran, 0, ",", ",")),
            Stat::make('Total Laba', 'Rp ' . number_format($laba, 0, ",", ",")),
        ];
    }
}
