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
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }

    protected function getStats(): array
    {
        // Default startDate and endDate
        $startDate = null; // Tidak ada batasan tanggal awal
        $endDate = now()->endOfDay(); // Default hingga sekarang

        // Check if startDate filter is provided
        if (!empty($this->filters['startDate'])) {
            $startDate = Carbon::parse($this->filters['startDate']);
        }

        // Check if endDate filter is provided
        if (!empty($this->filters['endDate'])) {
            $endDate = Carbon::parse($this->filters['endDate'])->endOfDay();
        }

        // Query logic for optional startDate and endDate
        $orderQuery = Order::query();
        if ($startDate && $endDate) {
            $orderQuery->whereBetween('transaction_time', [$startDate, $endDate]);
        } elseif ($startDate) {
            $orderQuery->where('transaction_time', '>=', $startDate);
        } elseif ($endDate) {
            $orderQuery->where('transaction_time', '<=', $endDate);
        }

        $expenseQuery = Expense::query();
        if ($startDate && $endDate) {
            $expenseQuery->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $expenseQuery->where('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $expenseQuery->where('created_at', '<=', $endDate);
        }

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
            Stat::make('Total Pemasukan', 'Rp ' . number_format($pemasukan, 0, ",", ",")),
            Stat::make('Total Pengeluaran', 'Rp ' . number_format($pengeluaran, 0, ",", ",")),
            Stat::make('Total Laba', 'Rp ' . number_format($laba, 0, ",", ",")),
        ];
    }
}
