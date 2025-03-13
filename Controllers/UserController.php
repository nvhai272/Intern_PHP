<?php
require_once '../Services/UserService.php';

// require_once(dirname(__DIR__) . "/dto/AdminRequest.php");
// require_once '../dto/UserRequest.php';

class UserController
{
    private $userService;
    public function __construct()
    {
        $this->userService = new UserService();
    }
    public function index()
    {
        require_once '../Views/User/details.php';
        exit;
    }
    public function getAllUsers()
    {
        $sort           = $_GET['sort'] ?? 'id';
        $order          = $_GET['order'] ?? 'asc';
        $data = $this->userService->getAllUsers($sort, $order);
        return $data;
       
    }

}