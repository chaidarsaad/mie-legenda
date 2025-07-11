<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class DataExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductsExport(),
            new CategoriesExport(),
        ];
    }
}

class ProductsExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Product::select('name', 'category_id', 'price', 'is_best_seller')->get();
    }

    public function headings(): array
    {
        return [
            'name',
            'category_id',
            'price',
            'is_best_seller',
        ];
    }

    public function title(): string
    {
        return 'Produk';
    }
}

class CategoriesExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection()
    {
        return Category::select('name')->get();
    }

    public function headings(): array
    {
        return [
            'name',
        ];
    }

    public function title(): string
    {
        return 'Kategori';
    }
}
