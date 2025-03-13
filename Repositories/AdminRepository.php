<?php
require_once __DIR__ . '/BaseRepository.php';
require_once '../Models/Admin.php';

class AdminRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct('admins', Admin::class);
    }   


}