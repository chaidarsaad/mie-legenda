<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Container\Attributes\Auth;

class ExpenseChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran';
    protected static ?int $sort = 4;
    public ?string $filter = 'today';
    protected static string $color = 'danger';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user ? $user->hasRole(['admin', 'super_admin']) : false;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $dateRange = match ($activeFilter) {
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'period' => 'perHour',
            ],
            'week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'period' => 'perDay',
            ],
            'month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'period' => 'perDay',
            ],
            'year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'period' => 'perMonth',
            ],
        };

        $query = Trend::model(Expense::class)
            ->between(
                start: $dateRange['start'],
                end: $dateRange['end'],
            );

        if ($dateRange['period'] === 'perHour') {
            $data = $query->perHour();
        } elseif ($dateRange['period'] === 'perDay') {
            $data = $query->perDay();
        } else {
            $data = $query->perMonth();
        }

        $data = $data->sum('amount');

        $label = $data->map(function (TrendValue $value) use ($dateRange) {
            $date = Carbon::parse($value->date);

            if ($dateRange['period'] === 'perHour') {
                return $date->format('H:i');
            } elseif ($dateRange['period'] === 'perDay') {
                return $date->format('d M');
            }
            return $date->format('M Y');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran ' . $this->getFilters()[$activeFilter],
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $label,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari ini',
            'week' => 'Minggu ini',
            'month' => 'Bulan ini',
            'year' => 'Tahun ini',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
