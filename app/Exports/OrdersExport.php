<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrdersExport implements FromCollection, WithHeadings, WithTitle
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = Carbon::parse($startDate)->startOfDay();
        $this->endDate = Carbon::parse($endDate)->endOfDay();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $orders = Order::select('transaction_time', 'total_price', 'total_item', 'payment_method', 'kasir_id')
            ->whereBetween('transaction_time', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($order) {
                return [
                    'transaction_time' => Carbon::parse($order->transaction_time)->format('d M Y H:i:s'), // Format sesuai permintaan
                    'total_price' => $order->total_price,
                    'total_item' => $order->total_item,
                    'payment_method' => $order->payment_method,
                    'kasir_id' => $order->kasir ? $order->kasir->name : 'Tidak ada kasir',
                ];
            });

        // Hitung total price dari semua pesanan
        $totalPrice = $orders->sum('total_price');

        // Tambahkan baris total di bagian bawah
        $orders->push([
            'transaction_time' => 'Total',
            'total_price' => $totalPrice,
            'total_item' => '',
            'payment_method' => '',
            'kasir_id' => ''
        ]);

        return collect($orders);
    }

    public function headings(): array
    {
        return [
            'Tanggal Pesanan',
            'Total Harga',
            'Total Item',
            'Metode Pembayaran',
            'Nama Kasir'
        ];
    }

    public function title(): string
    {
        return 'pesanan';
    }
}
