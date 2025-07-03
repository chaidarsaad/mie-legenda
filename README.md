🧾 Backend Point of Sales (POS) - Flutter App
Deskripsi Singkat:

Ini adalah repository backend untuk aplikasi Point of Sales (POS) yang digunakan dalam lingkungan internal Koperasi atau Toko. Backend ini dibangun menggunakan Laravel 11 dengan Filament Admin Panel, dan akan diakses oleh aplikasi frontend berbasis Flutter.

🔧 Fitur Backend:
🔐 Otentikasi berbasis token (Laravel Sanctum / Passport)

📦 Manajemen produk, kategori, dan stok

🧾 Transaksi penjualan & struk digital

👥 Manajemen pengguna & kasir

💼 Laporan harian, mingguan, dan bulanan

📊 Dashboard berbasis Filament

🧱 Teknologi Digunakan:
Laravel 11

Filament 3.x (Admin UI)

MySQL

Laravel Sanctum (untuk API Auth, jika digunakan)

Spatie Permissions

⚙️ Instalasi & Setup:
bash
Copy
Edit
git clone https://github.com/username/pos-backend.git
cd pos-backend
composer install
cp .env.example .env
php artisan key:generate
# Konfigurasi database & lainnya di .env
php artisan migrate --seed
php artisan serve
📱 Integrasi dengan Frontend Flutter:
Semua endpoint API telah disiapkan dengan format JSON.

Endpoint mengikuti standar REST API.

Dukungan token login, otorisasi user, dan response terstruktur.

🔒 Catatan Keamanan:
Pastikan file .env tidak dipublish.

Gunakan HTTPS di production.

Gunakan personal access token jika perlu autentikasi tambahan untuk admin.

🧩 Status Proyek:
🚧 Masih dalam tahap pengembangan aktif
✍️ Didesain untuk digunakan oleh aplikasi POS berbasis Flutter di lingkungan operasional Ma’had, toko, atau koperasi.

