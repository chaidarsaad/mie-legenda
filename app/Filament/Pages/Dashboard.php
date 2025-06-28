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
                        ->native(false)
                        ->closeOnDateSelection()
                        ->displayFormat('l, d F Y')
                        ->placeholder('Pilih Tanggal')
                        ->default(fn () => Carbon::createFromFormat('d/m/Y', '01/01/2025')->startOfDay())
                        ->maxDate(fn(Get $get) => $get('endDate') ?? now()->endOfDay()),

                    DatePicker::make('endDate')
                        ->label('Tanggal Akhir')
                        ->closeOnDateSelection()
                        ->placeholder('Pilih Tanggal')
                        ->native(false)
                        ->displayFormat('l, d F Y')
                        ->default(fn () => now()->endOfDay())
                        ->minDate(fn(Get $get) => $get('startDate') ?? Carbon::createFromFormat('d/m/Y', '01/01/2025')->startOfDay())
                        ->maxDate(now()->endOfDay()),
                    ])
                    ->columns(2),
            ]);
    }
}
