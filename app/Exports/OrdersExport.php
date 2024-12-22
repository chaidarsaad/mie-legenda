<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrdersExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil data pesanan dan sertakan informasi nama kasir
        return Order::select('transaction_time', 'total_price', 'total_item', 'payment_method', 'kasir_id')
            ->get()
            ->map(function ($order) {
                // Tambahkan nama kasir dengan menggunakan relasi
                $order->kasir_name = $order->kasir ? $order->kasir->name : 'Tidak ada kasir'; // Mengambil nama kasir
                return $order;
            });
    }

    public function headings(): array
    {
        return [
            'transaction_time',
            'total_price',
            'total_item',
            'payment_method',
            'kasir_id'
        ];
    }

    public function title(): string
    {
        return 'pesanan';
    }
}
