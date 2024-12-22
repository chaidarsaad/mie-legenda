<?php

namespace App\Observers;

use App\Models\OrderItem;
use App\Models\Product;

class OrderItemObserver
{
    public function created(OrderItem $orderItem)
    {
        $this->updateBestSellers();
    }

    public function deleted(OrderItem $orderItem)
    {
        $this->updateBestSellers();
    }

    protected function updateBestSellers()
    {
        // Hitung jumlah pesanan untuk setiap produk dan ambil 10 teratas
        $topProducts = OrderItem::selectRaw('product_id, COUNT(*) as order_count')
            ->groupBy('product_id')
            ->orderByDesc('order_count') // Urutkan dari jumlah pesanan terbanyak
            ->limit(10)                 // Batasi ke 10 produk teratas
            ->pluck('product_id');      // Ambil ID produk

        // Ambil semua produk yang terpengaruh (10 besar atau favorit saat ini)
        $affectedProducts = Product::whereIn('id', $topProducts)
            ->orWhere('is_best_seller', 1)
            ->get();

        foreach ($affectedProducts as $product) {
            if (in_array($product->id, $topProducts->toArray()) && $product->is_best_seller != 1) {
                // Tandai sebagai favorit jika masuk dalam 10 besar
                $product->update(['is_best_seller' => 1]);
            } elseif (!in_array($product->id, $topProducts->toArray()) && $product->is_best_seller == 1) {
                // Reset status favorit jika tidak lagi masuk dalam 10 besar
                $product->update(['is_best_seller' => 0]);
            }
        }
    }


    // protected function updateBestSellers()
    // {
    //     // Hitung jumlah pesanan untuk setiap produk
    //     $productOrders = OrderItem::selectRaw('product_id, COUNT(*) as order_count')
    //         ->groupBy('product_id')
    //         ->get();

    //     foreach ($productOrders as $productOrder) {
    //         // Perbarui status is_best_seller untuk produk dengan pesanan lebih dari 10
    //         $product = Product::find($productOrder->product_id);
    //         if ($product) {
    //             $isBestSeller = $productOrder->order_count > 10 ? 1 : 0;
    //             if ($product->is_best_seller != $isBestSeller) {
    //                 $product->update(['is_best_seller' => $isBestSeller]);
    //             }
    //         }
    //     }

    //     // Reset status is_best_seller untuk produk yang tidak ada di daftar hasil perhitungan
    //     Product::whereNotIn('id', $productOrders->pluck('product_id'))
    //         ->where('is_best_seller', 1)
    //         ->update(['is_best_seller' => 0]);
    // }
}
