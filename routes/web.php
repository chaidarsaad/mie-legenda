<?php

use App\Exports\CategoryExport;
use App\Exports\DataExport;
use App\Exports\ExpenseExport;
use App\Exports\OrdersExport;
use App\Exports\TemplateExport;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
use TomatoPHP\FilamentPWA\Http\Controllers\PWAController;

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/download-template-product', function () {
        return Excel::download(new TemplateExport, 'produk.xlsx');
    })->name('download-template-product');

    Route::get('/download-data', function () {
        return Excel::download(new DataExport, 'data.xlsx');
    })->name('download-data');

    Route::get('/download-data-pesanan', function () {
        return Excel::download(new OrdersExport, 'pesanan.xlsx');
    })->name('download-data-pesanan');
});
