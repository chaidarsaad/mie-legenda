<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
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
        // Menentukan rentang waktu bulan ini
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d 23:59:59');

        // Ambil data pesanan dalam rentang bulan ini
        $orders = Order::select('transaction_time', 'total_price', 'total_item', 'payment_method', 'kasir_id')
            ->whereBetween('transaction_time', [$startOfMonth, $endOfMonth])
            ->get()
            ->map(function ($order) {
                $kasir_name = $order->kasir ? $order->kasir->name : 'Tidak ada kasir';
                $order->kasir_id = $kasir_name;
                return $order;
            });


        // Hitung total price dari semua pesanan
        $totalPrice = $orders->sum('total_price');

        // Tambahkan baris dengan total_price di bagian bawah
        $orders->push((object)[
            'transaction_time' => 'Total',
            'total_price' => $totalPrice,
            'total_item' => '',
            'payment_method' => '',
            'kasir_id' => ''
        ]);

        return $orders;
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
