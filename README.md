# Modal Rakyat - Kelompok 4

## Deskripsi Kasus

Sebuah aplikasi Fintech (misal P2P Lending) "ModalRakyat" mengharuskan pengguna baru mengunggah foto KTP dan Slip Gaji untuk verifikasi (proses Know Your Customer). Proses ini harus super aman.

## Data Stream (In-Transit)

Proses Unggah (Upload): Amankan proses file upload (HTTP POST) dari aplikasi mobile/web ke backend.
Pemisahan Aliran Data: Saat file diterima backend, file harus divalidasi (tipe, ukuran) dan metadata (e.g., "User A upload KTP") dikirim ke database, sementara file-nya sendiri dikirim ke storage serta amankan aliran data

## Data at Rest

Penyimpanan File Sensitif (KTP): Ini adalah inti tugasnya. Simpan file KTP di object Implementasikan Enkripsi Sisi Server
Kontrol Akses: Buktikan bahwa file KTP tersebut tidak bisa diakses publik, dan hanya bisa diakses oleh layanan internal yang memiliki credential khusus.

## Kontribusi

- Clone repositori

  ```sh
  git clone https://github.com/bukanberuangsr/modalrakyat-K4.git
  ```

- Masuk ke direktori

  ```sh
  cd path/to/modalrakyat-K4
  ```

- Copy file .env.example dan buat file .env
- jalankan container dan cek container yang berjalan

  ```sh
  docker-compose up -d
  docker-compose ps
  ```

- Masuk ke container laravel, migrasi, install dependensi, serta generate JWT secret

  ```sh
  docker exec -it modalrakyat-app-1 bash
  php artisan migrate
  composer install
  php artisan jwt:secret
  exit
  ```

- Untuk mematikan container jalankan perintah berikut

  ```sh
  docker-compose down
  ```
