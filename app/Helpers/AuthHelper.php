<?php

use Config\Services;

if (!function_exists('isSeniorOfficer')) {
    function isSeniorOfficer(): bool
    {
        $session = Services::session();
        return $session->get('unit_level_id') === 'A13';
    }
}

if (!function_exists('getUserUnitUsaha')) {
    function getUserUnitUsaha(): ?string
    {
        $session = Services::session();
        return $session->get('unit_usaha') ?? null;
    }
}
