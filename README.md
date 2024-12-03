# WHMCS Mailtrap Mail Provider

Modul mail provider WHMCS untuk mengirim email menggunakan Mailtrap.

## Fitur

- Kirim email menggunakan API Mailtrap
- Mendukung penggunaan email sistem WHMCS
- Mendukung attachment
- Mendukung CC dan BCC
- Test koneksi langsung dari WHMCS
- Logging untuk debugging

## Instalasi

1. Download atau clone repository ini
2. Upload folder `modules` ke root direktori WHMCS Anda
3. Masuk ke admin area WHMCS
4. Pergi ke Setup > General Settings > Mail
5. Pilih "Mailtrap" dari dropdown Mail Provider
6. Masukkan API Token dan konfigurasi lainnya
7. Klik "Test Connection" untuk memastikan konfigurasi sudah benar

## Konfigurasi

- **API Token**: Token API dari Mailtrap (wajib)
- **Gunakan Email WHMCS**: Opsi untuk menggunakan email dari pengaturan WHMCS
- **From Email**: Alamat email pengirim (wajib jika tidak menggunakan email WHMCS)
- **From Name**: Nama pengirim (opsional)

## Penggunaan Email WHMCS

Modul ini mendukung penggunaan email yang sudah dikonfigurasi di WHMCS:
1. Aktifkan opsi "Gunakan Email WHMCS"
2. Email akan diambil dari Setup > General Settings > General
3. Nama pengirim akan menggunakan nama perusahaan dari WHMCS

## Debug & Troubleshooting

Log dapat ditemukan di:
- WHMCS_ROOT/logs/mailtrap.log
- WHMCS_ROOT/logs/error.log

## Changelog

### v1.0.0
- Rilis awal
- Dukungan untuk email sistem WHMCS
- Perbaikan format CC dan BCC
- Logging untuk debugging

## Lisensi

MIT License

## Author

Rohmat Ali Wardani
- LinkedIn: [Rohmat Ali Wardani](https://www.linkedin.com/in/rohmat-ali-wardani/)
