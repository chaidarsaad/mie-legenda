<?php

namespace App\Exports;

use App\Models\Expense;
use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdersExport implements FromCollection, WithHeadings, WithTitle, WithEvents
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
                    'transaction_time' => Carbon::parse($order->transaction_time)->format('d M Y H:i:s'),
                    'total_price' => $order->total_price,
                    'total_item' => $order->total_item,
                    'payment_method' => $order->payment_method,
                    'kasir_id' => $order->kasir ? $order->kasir->name : 'Tidak ada kasir',
                ];
            });

        $expense = Expense::select('created_at', 'amount')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();

        $totalPrice = $orders->sum('total_price');
        $totalExpense = $expense->sum('amount');
        $totalLaba = $totalPrice - $totalExpense;

        $orders->push([
            'transaction_time' => 'Total Pemasukan',
            'total_price' => $totalPrice ?? 0,
            'total_item' => '',
            'payment_method' => '',
            'kasir_id' => ''
        ]);

        $orders->push([
            'transaction_time' => 'Pengeluaran',
            'total_price' => $totalExpense ?? 0,
            'total_item' => '',
            'payment_method' => '',
            'kasir_id' => ''
        ]);

        $orders->push([
            'transaction_time' => 'Laba',
            'total_price' => $totalLaba,
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
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow(); // Dapatkan jumlah baris terakhir

                // Atur lebar kolom secara spesifik
                $sheet->getColumnDimension('A')->setWidth(20); // Tanggal Pesanan
                $sheet->getColumnDimension('B')->setWidth(15); // Total Harga
                $sheet->getColumnDimension('C')->setWidth(12); // Total Item
                $sheet->getColumnDimension('D')->setWidth(18); // Metode Pembayaran
                $sheet->getColumnDimension('E')->setWidth(15); // Nama Kasir

                // Bisa juga otomatis menyesuaikan lebar kolom
                // foreach (range('A', 'E') as $col) {
                //     $sheet->getColumnDimension($col)->setAutoSize(true);
                // }

                $boldCenterStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center']
                ];

                $centerStyle = [
                    'alignment' => ['horizontal' => 'center']
                ];

                $leftStyle = [
                    'alignment' => ['horizontal' => 'left']
                ];

                $yellowFill = [
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00']
                    ]
                ];

                // 1. Membuat heading (baris pertama) bold dan rata tengah
                $sheet->getStyle("A1:E1")->applyFromArray($boldCenterStyle);

                // 2. Menyesuaikan gaya untuk baris lainnya
                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();

                    if (in_array($cellValue, ['Laba', 'Total Pemasukan', 'Pengeluaran'])) {
                        // Label di kolom A dibuat bold & rata tengah
                        $sheet->getStyle("A{$row}")->applyFromArray($boldCenterStyle);

                        // Jika "Laba", beri warna kuning di seluruh baris
                        if ($cellValue === 'Laba') {
                            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($yellowFill);
                        }
                    } else {
                        // Nilai di kolom "Tanggal Pesanan" (A) tetap rata kiri
                        $sheet->getStyle("A{$row}")->applyFromArray($leftStyle);
                    }
                }

                // 3. Terapkan format mata uang Rupiah untuk kolom B (Total Harga), D (Total Pemasukan), dan E (Laba, Pengeluaran)
                $sheet->getStyle("B2:B{$highestRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("D2:D{$highestRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
                $sheet->getStyle("E2:E{$highestRow}")->getNumberFormat()->setFormatCode('"Rp" #,##0');
            }
        ];
    }
}
