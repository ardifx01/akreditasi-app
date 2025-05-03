Konfigurasi untuk lingkungan production yang belum memiliki SSL:

Konfigurasi Nginx Production:

Menghapus redirect dari HTTP ke HTTPS (diubah menjadi komentar)
Server HTTPS dinonaktifkan (diubah menjadi komentar) agar bisa dikembalikan dengan mudah ketika SSL tersedia
Server HTTP dikonfigurasi sebagai server utama untuk sementara


Docker Compose Production:

Port 443 dinonaktifkan (diubah menjadi komentar)
Volume untuk SSL dinonaktifkan (diubah menjadi komentar)

Untuk menjalankan, perintahnya tetap sama:
# Untuk development
docker-compose up -d

# Untuk production
docker-compose -f docker-compose.prod.yml up -d

Dengan perubahan ini, konfigurasi production Anda sekarang dapat berjalan tanpa memerlukan sertifikat SSL. Ketika siap untuk mengimplementasikan SSL, Cukup menghapus tanda komentar pada bagian yang relevan.
Cara aktivasi kembali SSL di masa depan:

Buat folder ssl di root proyek dan tambahkan sertifikat SSL Anda
Hapus tanda komentar untuk port 443 dan volume ssl di docker-compose.prod.yml
Hapus tanda komentar di server HTTPS pada docker/nginx/conf.d/default-production.conf
Hapus tanda komentar redirect HTTP ke HTTPS jika diperlukan

Dengan konfigurasi ini, dapat memulai dengan deployment di lingkungan production tanpa SSL terlebih dahulu, dan dengan mudah mengaktifkan SSL di kemudian hari.
