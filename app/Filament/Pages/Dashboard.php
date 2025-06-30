<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Carbon\Carbon;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->native(true)
                            ->closeOnDateSelection()
                            ->displayFormat('l, d F Y')
                            ->default(fn() => now()->startOfMonth())
                            ->placeholder('Pilih Tanggal')
                            ->maxDate(fn(Get $get) => $get('endDate') ?: now()),
                        DatePicker::make('endDate')
                            ->label('Tanggal Akhir')
                            ->closeOnDateSelection()
                            ->placeholder('Pilih Tanggal')
                            ->native(true)
                            ->displayFormat('l, d F Y')
                            ->minDate(fn(Get $get) => $get('startDate') ?: now()->startOfMonth())
                            ->maxDate(now())
                            ->default(fn() => now()->endOfDay())
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
