<?php
require_once __DIR__ . '/../Repositories/AdminRepository.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

require_once __DIR__ . '/../Helper/Helper.php';
require_once __DIR__ . '/../Models/Admin.php';

require_once __DIR__ . '/../DTO/response/ServiceResponse.php';

class AdminService
{
    private $adminRepo;
    private $help;
    public function __construct()
    {
        $this->adminRepo = new AdminRepository();
        $this->help      = Helper::getInstance();
    }

    public function getAdminById($id)
    {
        return $this->adminRepo->findById($id);
    }

    public function getAllAdmins($sort, $order)
    {
        return $this->adminRepo->fetchAll($sort, $order);

    }

    public function createAdmin(NewAdmin $data)
    {
        $errors = $this->validateAdminData($data);

        if (! empty($errors)) {
            return new ServiceResponse(false, $errors);
        }

        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

        try {
            $admin = Admin::fromNewAdmin($data);
            $admin->setPassword($hashedPassword);

            $newAd = $this->adminRepo->create($admin->toArray());
            
            if ($newAd && !empty($data->avatar) && isset($_FILES['uploadFileAvatar'])) {

                $this->help->saveAvatar($data->avatar, $_FILES['uploadFileAvatar']);
            }

            return new ServiceResponse(true, [], $newAd);
        } catch (Exception $e) {
            return new ServiceResponse(false, ['exception' => "Failed to create admin: " . $e->getMessage()]);
        }
    }

    private function validateAdminData(NewAdmin $data): array
    {
        $errors = [];

        if (! $this->help->validateRequired($data->name)) {
            $errors['name'] = "Name is required.";
        }

        if (! $this->help->validateRequired($data->email)) {
            $errors['email'] = "Email is required.";
        } elseif (! $this->help->validateEmail($data->email)) {
            $errors['email'] = "Invalid email format.";
        } elseif ($this->adminRepo->checkEmailExist($data->email)) {
            $errors['email'] = "Email already exists.";
        }

        if (! $this->help->validateRequired($data->password)) {
            $errors['password'] = "Password is required.";
        } elseif (! $this->help->validatePassword($data->password, 6)) {
            $errors['password'] = "Password must be at least 6 characters.";
        }

        if (! $this->help->validateRequired($data->avatar)) {
            $errors['avatar'] = "Avatar is required.";
        }

        if (! $this->help->validateRequired($data->role_type)) {
            $errors['role_type'] = "Role Admin is required.";
        }

        return $errors;
    }

    public function updateAdmin(array $data)
    {
        $errs       = self::validateAdminUpdate($data);
        $avatarName = '';

        // Nếu có lỗi, trả về ServiceResponse với lỗi
        if (! empty($errs)) {
            return new ServiceResponse(false, $errs);
        }

        // Password không nhập thì không đổi mật khẩu (nếu không nhập thì vẫn là mật khẩu cũ)
        if (! empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Kiểm tra file, nếu nó không được tải lên nó vẫn có giá trị chứ không null
        if (isset($_FILES['uploadFileAvatar']) && $_FILES['uploadFileAvatar']['error'] === UPLOAD_ERR_OK) {
            $avatarName = $this->help->generateFileName($_FILES['uploadFileAvatar']);
        }

        $dataUpdate = [
            'id'           => $data['id'],
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => $data['password'] ?? '',
            'role_type'    => $data['role_type'],
            'avatar'       => $avatarName ?? '',

            // bo sung
            'upd_datetime' => date('Y-m-d H:i:s'),
            'upd_id'       => $_SESSION['account']['id'],

        ];

        $up = $this->adminRepo->update($dataUpdate['id'], $dataUpdate);

        if ($up) {
            // Nếu cập nhật thành công và có ảnh mới, lưu ảnh và xóa ảnh cũ
            if (! empty($avatarName)) {
                $this->help->saveAvatar($avatarName, $_FILES['uploadFileAvatar']);
                $this->help->deleteAvatar($data['current_ava']);
            }
            return new ServiceResponse(true, [], $dataUpdate);
        } else {
            return new ServiceResponse(false, ['Cập nhật thất bại, vui lòng thử lại']);
        }
    }

    private function validateAdminUpdate(array $data): array
    {
        $errors = [];

        if (! $this->help->validateRequired($data['name'])) {
            $errors['name'] = "Name is required";
        }

        if (! $this->help->validateRequired($data['email'])) {
            $errors['email'] = "Email is required";
        } elseif (! $this->help->validateEmail($data['email'])) {
            $errors['email'] = "Invalid email format";
        } elseif (! $this->adminRepo->isEmailAvailable($data['email'], $data['id'], 'admin')) {
            $errors['email'] = "Email already exists";
        }

        if (! empty($data['password'])) { // Nếu password không rỗng thì mới kiểm tra độ dài
            if (! $this->help->validatePassword($data['password'], 6)) {
                $errors['password'] = "Password must be at least 6 characters";
            }
        }

        // if (! $this->help->validateRequired($data->avatar)) {
        //     $errors['avatar'] = "Avatar is required.";
        // }

        if (! $this->help->validateRequired($data['role_type'])) {
            $errors['role_type'] = "Role Admin is required.";
        }

        return $errors;
    }

    public function deleteAdmin($id)
    {
        try {
            $admin = $this->adminRepo->findById($id);
            if (! $admin) {
                return false; // Không tìm thấy admin
            }
            return $this->adminRepo->delete($id);
        } catch (Exception $e) {
            throw new Exception("Error deleting admin: " . $e->getMessage());
        }
    }

    public function fetchAllWithPagination($limit, $page)
    {
        try {
            $offset      = ($page - 1) * $limit;
            $data        = $this->adminRepo->fetchAllWithPagination($limit, $offset);
            $totalAdmins = $this->adminRepo->getTotal();
            $totalPages  = max(ceil($totalAdmins / $limit), 1);

            return [
                'data'         => $data,
                'total_pages'  => $totalPages,
                'current_page' => $page,
            ];
        } catch (Exception $e) {
            throw new Exception("Lỗi lấy danh sách admin: " . $e->getMessage());
        }
    }

    public function searchByNameOrEmailWithPagination($name, $email, $limit, $page)
    {
        try {
            $offset      = ($page - 1) * $limit;
            $data        = $this->adminRepo->searchByNameOrEmailWithPagination($name, $email, $limit, $offset);
            $totalAdmins = $this->adminRepo->getTotalByNameOrEmail($name, $email);
            $totalPages  = max(ceil($totalAdmins / $limit), 1);

            return [
                'data'         => $data,
                'total_pages'  => $totalPages,
                'current_page' => $page,
            ];
        } catch (Exception $e) {
            throw new Exception("Lỗi tìm kiếm admin: " . $e->getMessage());
        }
    }

}
