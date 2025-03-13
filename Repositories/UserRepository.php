<?php
require_once __DIR__ . '/BaseRepository.php';
require_once  '../Models/User.php';

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct("users", User::class);
    }
}
