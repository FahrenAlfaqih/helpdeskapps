<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class EmailSender extends Controller
{
    public function sendTestEmail()
    {
        $email = \Config\Services::email();

        $email->setTo('fahren66@gmail.com');
        $email->setFrom('fahren21ti@mahasiswa.pcr.ac.id', 'Help Desk System');
        $email->setSubject('Perubahan Status Tiket');
        $email->setMessage('Pegawai Dafa Yudistira melakukan pengalihan tiket dengan tiket id 10.');

        if (! $email->send()) {
            echo $email->printDebugger(['headers']);
        } else {
            echo "Email berhasil dikirim!";
        }
    }
}
