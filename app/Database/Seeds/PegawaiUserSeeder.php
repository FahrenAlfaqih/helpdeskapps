<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use Config\Database;

class PegawaiUserSeeder extends Seeder
{
    public function run()
    {
        $db = Database::connect();

        $unitUsaha = [
            'C2' => ['name' => 'RSBT Pangkalpinang', 'emailDomain' => 'rsbtpkp.com'],
            'C3' => ['name' => 'RSMS Sungailiat', 'emailDomain' => 'rsbtsliat.com'],
            'C4' => ['name' => 'RSBT Karimun', 'emailDomain' => 'rsbtkrmn.com'],
            'C5' => ['name' => 'RSBT Muntok', 'emailDomain' => 'rsbtmkt.com'],
            'C6' => ['name' => 'Managed Care & Clinics (MCC)', 'emailDomain' => 'mcc.com']
        ];

        $unitKerja = [
            'E21' => 'General Affair & Tehnique',
            'E13' => 'Finance & IT',
            'E11' => 'Business & Marketing',
            'E20' => 'Human Capital',
            'E24' => 'Vice Director/Manager',
            'E26' => 'OSCM-Hiperkes',
            'E30' => 'Surgery, Anesthesia & CSSD',
        ];

        $pegawaiData = [];
        $userData = [];
        $penempatanData = [];

        for ($i = 1; $i <= 10; $i++) {
            $idPegawai = 'PG_10' . $i;

            $unitUsahaKeys = array_keys($unitUsaha);
            $unitKerjaKeys = array_keys($unitKerja);

            $randUnitUsahaKey = $unitUsahaKeys[array_rand($unitUsahaKeys)];
            $randUnitKerjaKey = $unitKerjaKeys[array_rand($unitKerjaKeys)];

            $emailDomain = $unitUsaha[$randUnitUsahaKey]['emailDomain'];

            $email = 'pg' . $i . '@' . $emailDomain;

            $pegawaiData[] = [
                'id_pegawai' => $idPegawai,
                'nama' => 'Pegawai ' . $i,
                'email' => $email,
                'tgl_lahir' => '1990-01-01',
                'jenis_kelamin' => 'Laki-laki',
                'status_aktif' => 1,
                'agama' => 'Islam',
                'alamat_ktp' => 'Alamat KTP Pegawai ' . $i,
                'alamat_dom' => 'Alamat Domisili Pegawai ' . $i,
                'no_ktp' => '1234567890' . $i,
                'no_kk' => '0987654321' . $i,
                'tgl_kerja' => date('Y-m-d'),
                'id_medis' => 'MED' . $i,
                'gol' => 'III/a',
                'sgt' => 'SGT' . $i,
                'id_eselon' => 'ESL' . $i,
                'stat_pajak' => 'TK',
                'tk_pajak' => '0',
                'pjk_mulai' => '2025-01-01',
                'pjk_akhir' => '2025-12-31',
                'npwp' => 'NPWP' . $i,
            ];

            $userData[] = [
                'user_id' => 'USER_' . $idPegawai,
                'id_pegawai' => $idPegawai,
                'id_vendor' => 0,
                'nama' => 'Pegawai ' . $i,
                'email' => $email,
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'image' => 'default.png',
                'is_active' => 1,
                'role_id' => 2,
                'id_application' => 'APP1',
                'date_created' => Time::now()->toDateTimeString(),
            ];

            $penempatanData[] = [
                'id_pengawai_penempatan' => 'PP_' . $idPegawai,
                'id_pegawai' => $idPegawai,
                'id_unit_level' => 'A15',
                'id_unit_bisnis' => 'B2',
                'id_unit_usaha' => $randUnitUsahaKey,
                'id_unit_organisasi' => 'D9',
                'id_unit_kerja' => $randUnitKerjaKey,
                'id_unit_kerja_sub' => 'F46',
                'id_unit_lokasi' => 'L1',
            ];
        }

        $db->table('pegawai')->insertBatch($pegawaiData);
        $db->table('user')->insertBatch($userData);
        $db->table('pegawai_penempatan')->insertBatch($penempatanData);

        echo "Seeder pegawai, user, dan penempatan selesai dengan domain email sesuai unit usaha.";
    }
}
