<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class FileServe extends \CodeIgniter\Controller
{
    public function image($filename)
    {
        $path = WRITEPATH . 'uploads/' . $filename;

        if (!file_exists($path)) {
            throw PageNotFoundException::forPageNotFound("File not found: $filename");
        }

        $mime = mime_content_type($path);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));

        readfile($path);
        exit;
    }
}
