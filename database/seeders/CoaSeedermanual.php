<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coa;
use App\Models\Project;
use Illuminate\Support\Facades\Schema;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Coa::truncate();

        Project::firstOrCreate(
            ['id' => 1],
            ['nama_project' => 'Proyek Utama']
        );

        $data = [
            ['no_akun' => '9999', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'AYAT SILANG', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1101', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'KAS KECIL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1102', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'KAS BESAR', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1103', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'BANK', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1104', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => '-', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1201', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'PIUTANG USAHA', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1202', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'PIUTANG KARYAWAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1203', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'PIUTANG PIHAK 3', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1299', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'PIUTANG LAIN-LAIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1301', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'INVESTASI LAHAN DAN IZIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1302', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'RUMAH SIAP DIJUAL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1303', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'WIP MATERIAL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1304', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'WIP TENAGA KERJA', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1305', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'WIP OVERHEAD', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1306', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'WIP FASUM', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1307', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'PERSEDIAAN MATERIAL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1308', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'BIAYA DIBAYAR DIMUKA', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1399', 'kategori_akun' => 'ASET LANCAR', 'nama_akun' => 'UANG MUKA LAIN-LAIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1401', 'kategori_akun' => 'PAJAK', 'nama_akun' => 'PPN MASUKAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1402', 'kategori_akun' => 'PAJAK', 'nama_akun' => 'UM PPH NON FINAL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1403', 'kategori_akun' => 'PAJAK', 'nama_akun' => 'UM PPH FINAL', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],

            ['no_akun' => '1501', 'kategori_akun' => 'ASET TETAP', 'nama_akun' => 'TANAH', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1502', 'kategori_akun' => 'ASET TETAP', 'nama_akun' => 'BANGUNAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1503', 'kategori_akun' => 'ASET TETAP', 'nama_akun' => 'MESIN & PERALATAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1504', 'kategori_akun' => 'ASET TETAP', 'nama_akun' => 'KENDARAAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1505', 'kategori_akun' => 'ASET TETAP', 'nama_akun' => 'INVENTARIS KANTOR', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],

            ['no_akun' => '1601', 'kategori_akun' => 'AKM PENYUSUTAN DAN AMORTISASI', 'nama_akun' => 'AKM PENYUSUTAN BANGUNAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1602', 'kategori_akun' => 'AKM PENYUSUTAN DAN AMORTISASI', 'nama_akun' => 'AKM PENYUSUTAN MESIN & PERALATAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1603', 'kategori_akun' => 'AKM PENYUSUTAN DAN AMORTISASI', 'nama_akun' => 'AKM PENYUSUTAN KENDARAAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '1604', 'kategori_akun' => 'AKM PENYUSUTAN DAN AMORTISASI', 'nama_akun' => 'AKM PENYUSUTAN INVENTARIS KANTOR', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],

            ['no_akun' => '2001', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'BOOOKING FEE', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2002', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'PENDAPATAN DITERIMA DIMUKA', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2003', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG USAHA', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2004', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG BIAYA', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2005', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG PIHAK 3', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2006', 'kategori_akun' => 'HUTANG PEMILIK', 'nama_akun' => 'HUTANG PEMILIK', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2007', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'PPN KELUARAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2008', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG PPH NON FINAL', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2009', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG PPH FINAL', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '2099', 'kategori_akun' => 'HUTANG LANCAR', 'nama_akun' => 'HUTANG LAIN-LAIN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],

            ['no_akun' => '3001', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'MODAL DISETOR', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3002', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'SALDO LABA', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3003', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'TAMBAHAN MODAL DISETOR', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3004', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'LABA TAHUN LALU', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3005', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'PRIVE', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3006', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'DIVIDEN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Neraca'],
            ['no_akun' => '3007', 'kategori_akun' => 'EKUITAS', 'nama_akun' => 'LABA TAHUN BERJALAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Neraca'],

            ['no_akun' => '4001', 'kategori_akun' => 'PENJUALAN', 'nama_akun' => 'PENJUALAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5000', 'kategori_akun' => 'PENJUALAN', 'nama_akun' => 'POTONGAN PENJUALAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5001', 'kategori_akun' => 'BEBAN PENJUALAN', 'nama_akun' => 'HARGA POKOK TANAH', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5002', 'kategori_akun' => 'BEBAN PENJUALAN', 'nama_akun' => 'HARGA POKOK BANGUNAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5003', 'kategori_akun' => 'BEBAN PENJUALAN', 'nama_akun' => 'BY RETENSI BANGUNAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5004', 'kategori_akun' => 'BEBAN PENJUALAN', 'nama_akun' => 'BY PENJUALAN LAIN-LAIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],

            ['no_akun' => '5100', 'kategori_akun' => 'BIAYA PEMASARAN', 'nama_akun' => 'KOMISI PENJUALAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5101', 'kategori_akun' => 'BIAYA PEMASARAN', 'nama_akun' => 'BY MARKETING', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5102', 'kategori_akun' => 'BIAYA PEMASARAN', 'nama_akun' => 'BY ENTERTAINTMENT', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],

            ['no_akun' => '5201', 'kategori_akun' => 'BEBAN KARYAWAN', 'nama_akun' => 'GAJI', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5202', 'kategori_akun' => 'BEBAN KARYAWAN', 'nama_akun' => 'BONUS DAN TUNJANGAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5203', 'kategori_akun' => 'BEBAN KARYAWAN', 'nama_akun' => 'BPJS', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5204', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'PELATIHAN DAN SERTIFIKASI', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5205', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BY PENGIRIMAN DOKUMEN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5206', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'MAKAN DAN MINUM', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5207', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BEBAN UTILITAS', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5208', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BEBAN TRANSPORT', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5209', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BEBAN SEWA', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5210', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BY INSTALASI', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5211', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'PARKIR', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5212', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'ALAT TULIS KANTOR', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5213', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'KEPERLUAN KANTOR LAIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5214', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'PERJALANAN DINAS', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5215', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BEBAN PAJAK', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5216', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'SUMBANGAN DAN IURAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5217', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'BY TENAGA AHLI', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5218', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'PEMELIHARAAN ASET', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5219', 'kategori_akun' => 'BIAYA OPERASIONAL KANTOR', 'nama_akun' => 'ADMIN DAN TRANSFER', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],

            ['no_akun' => '4003', 'kategori_akun' => 'PENDAPATAN LAIN-LAIN', 'nama_akun' => 'PENDAPATAN LAIN-LAIN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '4004', 'kategori_akun' => 'PENDAPATAN LAIN-LAIN', 'nama_akun' => 'PENDAPATAN IURAN LINGKUNGAN', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '4005', 'kategori_akun' => 'PENDAPATAN LAIN-LAIN', 'nama_akun' => 'JASA GIRO', 'posisi_normal' => 'Kredit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '6001', 'kategori_akun' => 'BIAYA DILUAR USAHA', 'nama_akun' => 'PAJAK GIRO', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '6002', 'kategori_akun' => 'BIAYA DILUAR USAHA', 'nama_akun' => 'PEMELIHARAAN FASUM', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '6003', 'kategori_akun' => 'BIAYA DILUAR USAHA', 'nama_akun' => 'BIAYA LAIN-LAIN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],

            ['no_akun' => '7000', 'kategori_akun' => 'PAJAK PENGHASILAN', 'nama_akun' => 'PAJAK PENGHASILAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5301', 'kategori_akun' => 'BEBAN PENYUSUTAN', 'nama_akun' => 'BEBAN PENYUSUTAN', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
            ['no_akun' => '5302', 'kategori_akun' => 'BEBAN PENYUSUTAN', 'nama_akun' => 'BEBAN AMORTISASI', 'posisi_normal' => 'Debit', 'jenis_laporan' => 'Laba Rugi'],
        ];

        foreach ($data as $item) {
            $item['project_id'] = 1;
            Coa::create($item);
        }

        Schema::enableForeignKeyConstraints();
    }
}
