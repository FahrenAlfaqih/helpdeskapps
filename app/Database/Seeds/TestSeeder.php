<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TestSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Insert pegawai
        $db->table('pegawai')->insert([
            'id_pegawai'        => 'PG_1011',
            'nik'               => 12345678,
            'nik_lama'          => 0,
            'nama'              => 'Pegawai 11',
            'nm_pgl'            => 'Pegawai 11',
            'gelar1'            => '',
            'gelar2'            => '',
            'jenis_kelamin'     => 'Pria',
            'tpt_lahir'         => 'Jakarta',
            'tgl_lahir'         => '1990-01-01',
            'gol_dar'           => 'A',
            'tinggi'            => 170,
            'berat'             => 70,
            'agama'             => 'Islam',
            'pend_terakhir'     => 'S1',
            'no_ktp'            => '1234567890123456',
            'no_kk'             => '1234567890123456',
            'alamat_ktp'        => 'Jl. Merdeka No. 1',
            'alamat_dom'        => 'Jl. Merdeka No. 1',
            'telpon1'           => '08123456789',
            'telpon2'           => '08123456789',
            'no_telp_keluarga'  => '08123456789',
            'email'             => 'pg11@rsbtkrmn.com',
            'status_aktif'      => 1,
            'alasan_keluar'     => '',
            'ket_keluar'        => '',
            'status_pegawai'    => 'Aktif',
            'fungsi'            => 'Staff',
            'status_kwn'        => 'Kawin',
            'no_bpjs_kes'       => '1234567890',
            'no_bpjs_tkerja'    => '1234567890',
            'tgl_kerja'         => '2010-01-01',
            'tgl_diangkat_pwtt' => '2010-02-01',
            'tgl_cuti'          => null,
            'id_medis'          => 'MED200',
            'no_strsip'         => '',
            'tgl_strsip'        => null,
            'gol'               => 'III/b',
            'sgt'               => 'SGT1',
            'id_eselon'         => 'ESL1',
            'tmt_sgt'           => '2020-01-01',
            'tmt_gol'           => '2020-01-01',
            'tmt_eselon'        => '2020-01-01',
            'stat_pajak'        => 'Normal',
            'tk_pajak'          => 'TK0',
            'pjk_mulai'         => '2020-01-01',
            'pjk_akhir'         => '2020-12-31',
            'npwp'              => 'NPWP1234567890',
            'tgl_npwp'          => '2020-01-01',
            'id_bank'           => 'BCA',
            'no_rek'            => '1234567890',
            'atas_nm'           => 'Pegawai 11',
            'image'             => 'default.jpg',
            'jatah_cuti'        => '12',
            'bar_code'          => 'barcode_budi.jpg',
            'qr_code'           => 'qrcode_budi.png',
        ]);


        // Insert user dengan password hash (password: password123)
        $db->table('user')->insert([
            'user_id'        => 'USER_PG_1011',
            'id_pegawai'     => 'PG_1011',
            'id_vendor'      => 0,
            'nama'           => 'Pegawai 11',
            'email'          => 'pg11@rsbtkrmn.com',
            'password'       => password_hash('password123', PASSWORD_BCRYPT),
            'image'          => 'default.jpg',
            'is_active'      => 1,
            'role_id'        => '4',  // contoh role staff
            'id_application' => 'EYAB',
        ]);
    }
}
