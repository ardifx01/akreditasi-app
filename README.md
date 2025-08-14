# 📚 Sistem Akreditasi Perpustakaan Universitas

Aplikasi web berbasis Laravel untuk mendukung proses akreditasi perpustakaan universitas dengan fitur manajemen data, statistik, dan pelaporan yang komprehensif.

## 🚀 Fitur Utama

### 📊 Manajemen Data
- **Staff & Personil**: Pengelolaan data staf perpustakaan
- **Pelatihan**: Pencatatan program pelatihan dan pengembangan kompetensi
- **Sertifikasi**: Manajemen sertifikasi profesional staf
- **SKP**: Sistem penilaian kinerja pegawai
- **Ijazah & Transkrip**: Pengelolaan data akademik

### 🤝 Kerja Sama
- **MoU (Memorandum of Understanding)**: Manajemen kerja sama dengan institusi lain
- **Statistik Koleksi**: Analisis dan laporan koleksi perpustakaan

### 📈 Statistik & Laporan
- **Visit History**: Pencatatan kunjungan perpustakaan
- **Laporan Kehadiran**: Sistem absensi dan pelaporan kehadiran
- **Dashboard Analytics**: Visualisasi data dengan grafik dan chart

### 📚 Manajemen Koleksi
- **E-Book**: Pengelolaan koleksi buku digital
- **Periodikal**: Manajemen majalah dan jurnal
- **Peminjaman**: Sistem peminjaman dan pengembalian

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 11** - Framework PHP modern
- **PHP 8.2+** - Bahasa pemrograman utama
- **MySQL** - Database relasional
- **Redis** - Cache dan session management

### Frontend
- **Blade Template Engine** - Templating Laravel
- **Tailwind CSS** - Utility-first CSS framework
- **Alpine.js** - JavaScript minimal untuk interaktivitas
- **Chart.js** - Visualisasi data

### Tools & Libraries
- **Laravel Breeze** - Authentication scaffolding
- **Laravel DomPDF** - Export PDF
- **Yajra DataTables** - Tabel interaktif
- **Pest PHP** - Testing framework

## 📋 Persyaratan Sistem

- PHP 8.2 atau lebih tinggi
- Composer
- MySQL 8.0+
- Node.js & NPM
- Web Server (Apache/Nginx)

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone [repository-url]
cd akreditasi-app
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install NPM dependencies
npm install
```

### 3. Setup Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE akreditasi_app;
exit

# Run migrations
php artisan migrate

# Run seeders (optional)
php artisan db:seed
```

### 5. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 6. Start Development Server
```bash
php artisan serve
```

## ⚙️ Konfigurasi

### Database Configuration
Edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=akreditasi_app
DB_USERNAME=root
DB_PASSWORD=
```

### Mail Configuration (Optional)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 📁 Struktur Direktori

```
akreditasi-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Helpers/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── public/
│   ├── css/
│   ├── js/
│   └── img/
├── routes/
├── storage/
└── tests/
```

## 🔧 Perintah Artisan

```bash
# Clear cache
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

```

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

## 📊 Database Schema

### Tabel Utama
- `users` - Data pengguna sistem
- `tb_staff` - Data staf perpustakaan
- `tb_pelatihan` - Data pelatihan staf
- `tb_sertifikasi` - Data sertifikasi
- `tb_skp` - Sistem penilaian kinerja
- `tb_ijazah` - Data ijazah
- `tb_transkrip` - Data transkrip nilai
- `tb_mou` - Memorandum of understanding
- `m_viscorner` & `m_vishistory` - Data kunjungan

## 🔐 Hak Akses

### Role Pengguna
- **Admin**: Akses penuh ke semua fitur
- **Staff**: Akses terbatas sesuai dengan peran
- **Guest**: Akses hanya untuk melihat laporan

## 📱 API Endpoints

### Authentication
- `POST /api/login` - Login pengguna
- `POST /api/register` - Registrasi pengguna
- `POST /api/logout` - Logout pengguna

### Data Management
- `GET /api/staff` - Mendapatkan data staf
- `POST /api/staff` - Menambah data staf
- `PUT /api/staff/{id}` - Update data staf
- `DELETE /api/staff/{id}` - Hapus data staf

## 🐳 Docker Support

### Build & Run with Docker
```bash
# Build image
docker build -t akreditasi-app .

# Run container
docker-compose up -d
```

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper mail settings
- [ ] Set up SSL certificate
- [ ] Configure backup strategy
- [ ] Set up monitoring
- [ ] Optimize application
- [ ] Configure queue workers

### Server Requirements
- Ubuntu 20.04+ / CentOS 8+
- PHP 8.2+
- MySQL 8.0+
- Redis
- Supervisor (for queue workers)
- Nginx/Apache

## 🤝 Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## 👥 Tim Pengembang

- **Lead Developer**: Muhammad Asharul Maali
- **UI/UX Designer**: Ammar Miftahudin Anshori
- **Database Admin**: Khoiruddin Nur Wahid

## 📞 Kontak

Untuk pertanyaan atau dukungan, silakan hubungi:
- Email: perpus@ums.ac.id
- Phone: +62813 2685 9003
- Website: https://library.ums.ac.id

## 📝 Changelog

### v1.0.0 (2024-01-01)
- Initial release
- Basic CRUD functionality
- Authentication system
- Dashboard analytics

---

**Dikembangkan dengan ❤️ untuk mendukung akreditasi perpustakaan universitas**
