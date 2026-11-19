# Project Seleksi Full-Stack Developer Intern di Talenavi

dibuat oleh: Jonathan Vincent Hartono

---

## BACKEND

### A. Setup Database & Konfigurasi MySQL

1. **Konfigurasi `.env`**
   Rename file `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi database berikut:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=seleksi_talenavi
   DB_USERNAME=
   DB_PASSWORD=
   ```
   *(Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan MySQL lokal Anda).*

2. **Install Dependensi & Generate App Key**
   ```bash
   composer install
   php artisan key:generate
   ```

3. **Jalankan Migration**
   ```bash
   php artisan migrate
   ```
   *(Jika muncul pertanyaan apakah ingin membuat database baru, pilih yes).*

4. **Jalankan Local Server**
   ```bash
   php artisan serve
   ```
   *(API siap diakses pada `http://localhost:8000`).*

### B. File yang Dibuat dan Dimodifikasi

Berikut adalah file-file yang dibuat dan dimodifikasi untuk mengimplementasikan fitur backend seleksi ini:

1. **`database/migrations/2026_11_19_080044_create_todos_table.php`**

   Mendefinisikan struktur tabel `todos` dengan kolom:

   - `title` (string, wajib diisi)
   - `assignee` (string, opsional/nullable)
   - `due_date` (date, wajib diisi)
   - `time_tracked` (decimal 8,2, default: 0)
   - `status` (enum: `pending`, `open`, `in_progress`, `completed`, default: `pending`)
   - `priority` (enum: `low`, `medium`, `high`, wajib diisi)
   - `timestamps` (`created_at` & `updated_at`)
2. **`app/Models/Todo.php`**

   - Mengatur properti `$fillable` untuk keamanan pengisian massal.
   - Menambahkan `$casts` agar kolom `due_date` dikonversi ke format `Y-m-d` dan `time_tracked` menjadi `float`.
3. **`routes/api.php`**

   Mendefinisikan endpoint REST API:

   - `POST /api/todos`: Mengarah ke method `store` di `TodoController`.
   - `GET /api/todos/export`: Mengarah ke method `export` di `TodoController`.
4. **`app/Http/Requests/StoreTodoRequest.php`**

   Menangani validasi input saat membuat Todo baru:

   - `title`: Wajib diisi, string, maksimal 255 karakter.
   - `assignee`: Opsional, string, maksimal 255 karakter.
   - `due_date`: Wajib diisi, format `Y-m-d`, tidak boleh di masa lalu (`after_or_equal:today`).
   - `time_tracked`: Opsional, tipe numerik, minimal 0.
   - `status`: Opsional, bernilai salah satu dari `pending`, `open`, `in_progress`, atau `completed`.
   - `priority`: Wajib diisi, bernilai salah satu dari `low`, `medium`, atau `high`.
5. **`app/Http/Controllers/Api/TodoController.php`**

   Controller utama yang menangani request API:

   - **`store()`**: Menerima data yang sudah divalidasi oleh `StoreTodoRequest`. Memastikan nilai default untuk `status` (`pending`) dan `time_tracked` (`0`) tetap terisi jika request kosong, menyimpan data ke database lewat model `Todo`, dan mengembalikan response JSON dengan HTTP status `201 Created`.
   - **`export()`**: Memanggil class `TodosExport` melalui library `maatwebsite/excel` untuk mengunduh file Excel bernama `todos_report.xlsx`.
6. **`app/Exports/TodosExport.php`**

   Logika export Excel menggunakan `Maatwebsite\Excel`:

   - **Query & Dynamic Filter**: Menangani filter data berdasarkan query parameters:
     - `title`: Pencarian parsial (`LIKE %...%`).
     - `assignee`: Multi-value dipisah koma (`whereIn`).
     - `start` & `end`: Filter rentang tanggal tenggat (`due_date`).
     - `min` & `max`: Filter rentang durasi waktu (`time_tracked`).
     - `status`: Multi-value dipisah koma (`whereIn`).
     - `priority`: Multi-value dipisah koma (`whereIn`).
   - **Headings & Mapping**: Mengatur urutan header kolom (`title`, `assignee`, `due_date`, `time_tracked`, `status`, `priority`) dan format data baris.
   - **Summary Row**: Melalui event `AfterSheet`, menambahkan baris ringkasan paling bawah yang menampilkan **Total Todos** (`count()`) dan **Total Time Tracked** (`sum()`), disertai styling font tebal (*bold*) dan lebar kolom otomatis (*auto-size*).

### C. Kesesuaian Hasil Pengerjaan dengan Instruksi

Seluruh kebutuhan backend telah dipenuhi sesuai instruksi:

1. **API Create Todo List (`POST /api/todos`)**:

   - Kolom lengkap sesuai tipe data (`title`, `assignee`, `due_date`, `time_tracked`, `status`, `priority`).
   - Validasi `due_date` tidak boleh tanggal lampau (`after_or_equal:today`).
   - `status` otomatis bernilai default `pending` dan `time_tracked` default `0`.
   - Mengembalikan response JSON objek Todo baru dengan HTTP Status Code `201 Created`.
2. **API Export Excel Report (`GET /api/todos/export`)**:

   - Menghasilkan file Excel dengan urutan kolom: `title`, `assignee`, `due_date`, `time_tracked`, `status`, `priority`.
   - Terdapat baris ringkasan di bagian bawah: total jumlah todo dan total waktu pengerjaan.
   - Mendukung semua kriteria filter:
     - `title`: pencarian sebagian kata (`?title=xxx`)
     - `assignee`: filter beberapa nama, dipisah koma (`?assignee=xxx,yyy`)
     - `due_date`: rentang tanggal (`?start=YYYY-MM-DD&end=YYYY-MM-DD`)
     - `time_tracked`: rentang durasi waktu (`?min=X&max=Y`)
     - `status`: filter beberapa status, dipisah koma (`?status=pending,in_progress`)
     - `priority`: filter beberapa prioritas, dipisah koma (`?priority=low,high`)
