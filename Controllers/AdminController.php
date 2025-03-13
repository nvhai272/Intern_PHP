<?php

require_once '../Services/AdminService.php';
require_once '../Services/UserService.php';
require_once '../Helper/Helper.php';

require_once '../DTO/request/NewUser.php';

class AdminController
{
    private $help;
    private $adminService;
    private $userService;

    public function __construct()
    {
        $this->userService  = new UserService();
        $this->adminService = new AdminService();
        $this->help         = Helper::getInstance();
    }

    public function showAllAdmins(): void
    {
        // $allowedColumns = ['id', 'name', 'status', 'role_type','email'];
        // [$sort, $order] = $this->help->sanitizeSortOrder($_GET['sort'] ?? 'id', $_GET['order'] ?? 'asc', $allowedColumns);
        $sort           = $_GET['sort'] ?? 'id';
        $order          = $_GET['order'] ?? 'asc';
        $data           = $this->adminService->getAllAdmins($sort, $order);
        $danhSachDuLieu = 'admin';
        include '../Views/Admin/list.php';
    }

    public function showAllUsers(): void
    {
        $sort           = $_GET['sort'] ?? 'id';
        $order          = $_GET['order'] ?? 'asc';
        $data           = $this->userService->getAllUsers($sort, $order);
        $danhSachDuLieu = 'user';
        include '../Views/Admin/list.php';
    }

    public function showDetailsAdmin(): void
    {
        // session_start();
        $admin   = [];
        $adminId = $_GET['id'] ?? $_SESSION['account']['id'] ?? null;

        if ($adminId) {
            $admin = $this->adminService->getAdminById($adminId);
        }
        include '../Views/Admin/details-admin.php';
    }

    public function showCreatePageAdmin(): void
    {
//        session_start();

        if (! empty($_SESSION['errors']) || ! empty($_SESSION['old_data'])) {
            $errors   = $_SESSION['errors'];
            $old_data = $_SESSION['old_data'];

            unset($_SESSION['errors'], $_SESSION['old_data']);
            extract(['errors' => $errors, 'old_data' => $old_data]); // Biến đổi thành biến
        }

        require __DIR__ . '/../Views/Admin/create.php';
    }

    public function showEditAdmin(): void
    {
        // $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        // if ($id <= 0) {
        //     die("Invalid admin ID");
        // }

        $id = $_GET['id'];

        try {
            $admin = $this->adminService->getAdminById($id);

            if (! $admin) {
                $send = 'Admin not found or cannot be update';
                header("Location: /admin/list-admin?&message=$send");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['errors'] = ['exception' => "Failed to delete admin: " . $e->getMessage()];
        }

        // unset($_SESSION['errors'], $_SESSION['old_data_update']);
        // var_dump($admin);
        include '../Views/Admin/edit.php';
    }

    public function editAdmin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id'          => $_POST['id'],
                'name'        => $_POST['name'] ?? '',
                'email'       => $_POST['email'] ?? '',
                'password'    => $_POST['password'] ?? '',
                'role_type'   => $_POST['role_type'],

                // tên ảnh hiện tại
                'current_ava' => $_POST['current_ava'] ?? '',

                //  $_FILES['uploadFileAvatar'],
            ];

            try {
                $result = $this->adminService->updateAdmin($data);

                if ($result->isSuccess()) {
                    $send = 'Updated success!';
                    header("Location: /admin/list-admin?message=$send");
                    exit;
                }

                // Lưu lỗi và data cũ vào session để hiển thị cho người dùng
                $_SESSION['errors_update'] = $result->getErrors();
                // $_SESSION['old_data_update'] = $_POST;
                header("Location: /admin/edit-admin?id=" . $_POST['id']);
                exit;
            } catch (Exception $e) {
                $_SESSION['errors_update'] = ['general' => $e->getMessage()];
                // $_SESSION['old_data_update'] = $_POST;
                header("Location: /admin/edit-admin?id=" . $_POST['id']);
                exit;
            }
        }
    }

    public function createAdmin(): void
    {
        session_start();
        try {

            if (! empty($_FILES['uploadFileAvatar']['tmp_name'])) {
                $file             = $_FILES['uploadFileAvatar'];
                $stringNameAvatar = $this->help->generateFileName($file);
            } else {
                $stringNameAvatar = null;
            }

            // form post ban đầu k có att avatar, chỗ này là thêm nè hehehe
            // thằng file đi riêng k nằm trong thằng POST
            $_POST['avatar'] = $stringNameAvatar;
            $data            = $_POST;
            // $data['avatar']  = $stringNameAvatar;
            $dataObj = new NewAdmin($data);
            $result  = $this->adminService->createAdmin($dataObj);

            if (! empty($result->getErrors())) {
                $_SESSION['errors']   = $result->getErrors();
                $_SESSION['old_data'] = $_POST;
                // $_SESSION['old_data']['uploadFileAvatar'] = $_FILES['uploadFileAvatar'];

                header("Location: /admin/create-admin");
                exit();
            }

            $send = 'Created success!';
            header("Location: /admin/list-admin?message=$send");

            exit();
        } catch (Exception $e) {
            $_SESSION['errors']   = ['general' => $e->getMessage()];
            $_SESSION['old_data'] = $_POST;
            header("Location: /admin/create-admin");
            exit();
        }
    }

    public function deleteAdmin(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $id = $_POST['id'];

                $result = $this->adminService->deleteAdmin($id);

                if ($result) {
                    $send = 'Deleted success!';
                    header("Location: /admin/list-admin?&message=$send");
                    exit();
                }
                // $_SESSION['errorsDelete'] = ['error' => 'Admin not found or cannot be deleted'];

                $send = 'Admin not found or cannot be deleted';
                header("Location: /admin/list-admin?&message=$send");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['errorsDelete'] = ['exception' => "Failed to delete admin: " . $e->getMessage()];
        }

        header("Location: /admin/list-admin");
        exit();
    }

    public function searchAdmin(): void
    {
        $page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $page  = max($page, 1);
        $limit = 5;
        $name  = isset($_GET['name']) ? trim($_GET['name']) : '';
        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        $error = '';

        try {
            if (isset($_GET['name']) || isset($_GET['email'])) { // Khi nhấn nút Search
                if ($name === '' && $email === '') {
                    $error  = "Nhập ít nhất một thông tin (Name hoặc Email) để tìm kiếm!";
                    $result = $this->adminService->fetchAllWithPagination($limit, $page);
                } else {
                    $result = $this->adminService->searchByNameOrEmailWithPagination($name, $email, $limit, $page);
                }
            } else { // Khi vào trang lần đầu tiên
                $result = $this->adminService->fetchAllWithPagination($limit, $page);
            }

            $data        = $result['data'];
            $totalPages  = $result['total_pages'];
            $currentPage = $result['current_page'];
        } catch (Exception $e) {
            $error       = "Lỗi hệ thống: " . $e->getMessage();
            $data        = [];
            $totalPages  = 1;
            $currentPage = 1;
        }

        $danhSachDuLieu = 'admin';
        include '../Views/Admin/search.php';
    }

//  USER
    public function showDetailsUser(): void
    {
        $u  = [];
        $id = $_GET['id'] ?? $_SESSION['account']['id'] ?? null;

        if ($id) {
            $u = $this->userService->getById($id);
        }
        include '../Views/User/details.php';
    }

    public function showEditUser(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            die("Invalid user ID");
        }

        try {
            $admin = $this->userService->getById($id);

            if (! $admin) {
                die("User not found");
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }

        // unset($_SESSION['errors'], $_SESSION['old_data_update']);
        // var_dump($admin);
        include '../Views/User/edit.php';
    }

    public function editUser(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id'          => $_POST['id'],
                'name'        => $_POST['name'] ?? '',
                'email'       => $_POST['email'] ?? '',
                'password'    => $_POST['password'] ?? '',
                'status'      => $_POST['status'],
                'facebook_id' => $_POST['facebook_id'] ?? '',

                // tên ảnh hiện tại
                'current_ava' => $_POST['current_ava'] ?? '',
                // file ảnh mới được tải lên, sử dụng biến cục bộ để lấy không cần phải gán
                //  $_FILES['uploadFileAvatar'],
            ];

            try {
                $result = $this->userService->updateUser($data);

                if ($result->isSuccess()) {
                    $send = 'Updated success!';
                    header("Location: /admin/list-user?message=$send");
                    exit;
                }

                $_SESSION['errors_update'] = $result->getErrors();
                // $_SESSION['old_data_update'] = $_POST;
                header("Location: /admin/edit-user?id=" . $_POST['id']);
                exit;
            } catch (Exception $e) {
                // Nếu có lỗi bất ngờ, lưu lỗi chung vào session
                $_SESSION['errors_update'] = ['general' => $e->getMessage()];
                // $_SESSION['old_data_update'] = $_POST;
                header("Location: /admin/edit-user?id=" . $_POST['id']);
                exit;
            }
        }
    }

    public function showCreateUser(): void
    {
        session_start();

        if (! empty($_SESSION['errors']) || ! empty($_SESSION['old_data'])) {
            $errors   = $_SESSION['errors'];
            $old_data = $_SESSION['old_data'];

            unset($_SESSION['errors'], $_SESSION['old_data']);
            extract(['errors' => $errors, 'old_data' => $old_data]); // Biến đổi thành biến
        }

        require __DIR__ . '/../Views/User/create.php';
    }

    public function createUser(): void
    {
        session_start();
        try {
            if (! empty($_FILES['uploadFileAvatar']['tmp_name'])) {
                $file             = $_FILES['uploadFileAvatar'];
                $stringNameAvatar = $this->help->generateFileName($file);
            } else {
                $stringNameAvatar = null;
            }

            $_POST['avatar'] = $stringNameAvatar;
            $data            = $_POST;
            // $data['avatar']  = $stringNameAvatar;
            $dataObj = new NewUser($data);

            $result = $this->userService->createUser($dataObj);

            if (! empty($result->getErrors())) {
                $_SESSION['errors']   = $result->getErrors();
                $_SESSION['old_data'] = $_POST;
                // $_SESSION['old_data']['uploadFileAvatar'] = $_FILES['uploadFileAvatar'];

                header("Location: /admin/create-user");
                exit();
            }

            $send = 'Created success!';
            header("Location: /admin/list-user?message=$send");
            // header("Location: /admin/search-admin?success=1");

            exit();
        } catch (Exception $e) {
            $_SESSION['errors']   = ['general' => $e->getMessage()];
            $_SESSION['old_data'] = $_POST;
            header("Location: /admin/create-user");
            exit();
        }
    }

    public function searchUser(): void
    {
        $page  = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $page  = max($page, 1);
        $limit = 5;
        $name  = isset($_GET['name']) ? trim($_GET['name']) : '';
        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        $error = '';

        try {
            if (isset($_GET['name']) || isset($_GET['email'])) { // Khi nhấn nút Search
                if ($name === '' && $email === '') {
                    $error  = "Nhập ít nhất một thông tin (Name hoặc Email) để tìm kiếm!";
                    $result = $this->adminService->fetchAllWithPagination($limit, $page);
                } else {
                    $result = $this->adminService->searchByNameOrEmailWithPagination($name, $email, $limit, $page);
                }
            } else { // Khi vào trang lần đầu tiên
                $result = $this->adminService->fetchAllWithPagination($limit, $page);
            }

            $data        = $result['data'];
            $totalPages  = $result['total_pages'];
            $currentPage = $result['current_page'];
        } catch (Exception $e) {
            $error       = "Lỗi hệ thống: " . $e->getMessage();
            $data        = [];
            $totalPages  = 1;
            $currentPage = 1;
        }

        // $danhSachDuLieu = 'user';
        include '../Views/User/search.php';
    }

    public function deleteUser(): void
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
                $id = $_POST['id'];

                $result = $this->userService->deleteUser($id);

                if ($result) {
                    $send = 'Deleted success!';
                    header("Location: /admin/list-user?&message=$send");
                    exit();
                }
                $_SESSION['errors'] = ['error' => 'USer not found or cannot be deleted'];
            }
        } catch (Exception $e) {
            $_SESSION['errors'] = ['exception' => "Failed to delete admin: " . $e->getMessage()];
        }

        header("Location: /admin/list-user");
        exit();
    }
}
