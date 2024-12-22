<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductImport implements ToModel, WithHeadingRow, WithMultipleSheets, SkipsEmptyRows, WithValidation
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'],
            'category_id' => $row['category_id'],
            'price' => $row['price'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required',
            '*.category_id' => 'required|exists:categories,id',
            '*.price' => 'required|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.name' => 'Nama produk harus diisi',
            '*.category_id' => 'Category produk tidak valid',
            '*.price' => 'Harga produk harus angka',
        ];
    }
}
