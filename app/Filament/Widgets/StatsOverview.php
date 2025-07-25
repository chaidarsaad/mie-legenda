<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\App;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters, HasWidgetShield;

    protected static ?int $sort = 0;
    protected static bool $hasPageFilters = true;
    protected ?string $heading = 'Statistik';


    protected function getStats(): array
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

        // Query logic for optional startDate and endDate
        $orderQuery = Order::query()->whereBetween('transaction_time', [$startDate, $endDate]);
        $expenseQuery = Expense::query()->whereBetween('created_at', [$startDate, $endDate]);

        $category_count = Category::count();
        $product_count = Product::count();
        $order_count = $orderQuery->count();
        $pemasukan = $orderQuery->sum('total_price');
        $pengeluaran = $expenseQuery->sum('amount');
        $laba = $pemasukan - $pengeluaran;

        return [
            Stat::make('Total Categories', $category_count),
            Stat::make('Total Product', $product_count),
            Stat::make('Total Orders', $order_count),
            Stat::make('Total Pemasukan', 'Rp ' . number_format($pemasukan, 2, ",", ",")),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($pengeluaran, 2, ",", ",")),
            Stat::make('Total Laba', 'Rp ' . number_format($laba, 2, ",", ",")),
        ];
    }
}
