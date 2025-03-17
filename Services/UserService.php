<?php
require_once __DIR__ . '/../Repositories/UserRepository.php';

class UserService
{
    private $help;
    private $userRepo;
    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->help     = Helper::getInstance();

    }

    public function getById($id)
    {
        return $this->userRepo->findById($id);
    }

    public function getAllUsers($sort, $order)
    {
        return $this->userRepo->fetchAll($sort, $order);
    }

    private function validateUser(NewUser $data): array
    {
        $errors = [];

        if (! $this->help->validateRequired($data->name)) {
            $errors['name'] = "Name is required";
        }
        // if (! $this->help->validateRequired($data->facebook_id)) {
        //     $errors['facebook_id'] = "FB is required";
        // }

        if (! $this->help->validateRequired($data->email)) {
            $errors['email'] = "Email is required";
        } elseif (! $this->help->validateEmail($data->email)) {
            $errors['email'] = "Invalid email format";
        } elseif ($this->userRepo->checkEmailExist($data->email)) {
            $errors['email'] = "Email already exists";
        }

        // if (! $this->help->validateRequired($data->password)) {
        //     $errors['password'] = "Password is required.";
        // } elseif (! $this->help->validatePassword($data->password, 6)) {
        //     $errors['password'] = "Password must be at least 6 characters.";
        // }

        if (! $this->help->validateRequired($data->avatar)) {
            $errors['avatar'] = "Avatar is required";
        }

        if (! $this->help->validateRequired($data->status)) {
            $errors['status'] = "Status is required";
        }
        return $errors;
    }

    public function createUser(NewUser $data)
    {
        $errors = $this->validateUser($data);

        if (! empty($errors)) {
            return new ServiceResponse(false, $errors);
        }

        $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);

        try {
            $user = User::fromNewUser($data);
            $user->setPassword($hashedPassword);

            $newU = $this->userRepo->create($user->toArray());

            if ($newU && ! empty($data->avatar) && isset($_FILES['uploadFileAvatar'])) {
                $this->help->saveAvatar($data->avatar, $_FILES['uploadFileAvatar']);
            }

            return new ServiceResponse(true, [], $newU);
        } catch (Exception $e) {
            return new ServiceResponse(false, ['exception' => "Failed to create user: " . $e->getMessage()]);
        }
    }

    public function deleteUser($id)
    {
        try {
            $admin = $this->userRepo->findById($id);
            if (! $admin) {
                return false; 
            }
            return $this->userRepo->delete($id);
        } catch (Exception $e) {
            throw new Exception("Error deleting user: " . $e->getMessage());
        }
    }

    public function fetchAllWithPagination($limit, $page)
    {
        try {
            $offset      = ($page - 1) * $limit;
            $data        = $this->userRepo->fetchAllWithPagination($limit, $offset);
            $totalAdmins = $this->userRepo->getTotal();
            $totalPages  = max(ceil($totalAdmins / $limit), 1);

            return [
                'data'         => $data,
                'total_pages'  => $totalPages,
                'current_page' => $page,
            ];
        } catch (Exception $e) {
            throw new Exception("Lỗi lấy danh sách user: " . $e->getMessage());
        }
    }

    public function searchByNameOrEmailWithPagination($name, $email, $limit, $page)
    {
        try {
            $offset      = ($page - 1) * $limit;
            $data        = $this->userRepo->searchByNameOrEmailWithPagination($name, $email, $limit, $offset);
            $totalAdmins = $this->userRepo->getTotalByNameOrEmail($name, $email);
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

    public function updateUser(array $data)
    {
        $errs       = self::validateUserUpdate($data);
        $avatarName = '';

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
            'status'       => $data['status'],
            'avatar'       => $avatarName ?? '',
            'facebook_id'  => $data['facebook_id'],
            // bo sung
            'upd_datetime' => date('Y-m-d H:i:s'),
            'upd_id'       => $_SESSION['account']['id'],

        ];

        $up = $this->userRepo->update($dataUpdate['id'], $dataUpdate);

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

    private function validateUserUpdate(array $data): array
    {
        $errors = [];

        if (! $this->help->validateRequired($data['name'])) {
            $errors['name'] = "Name is required";
        }

        if (! $this->help->validateRequired($data['status'])) {
            $errors['status'] = "Status is required";
        }

        // if (! $this->help->validateRequired($data['facebook_id'])) {
        //     $errors['facebook_id'] = "FB is required";
        // }

        if (! $this->help->validateRequired($data['email'])) {
            $errors['email'] = "Email is required";
        } elseif (! $this->help->validateEmail($data['email'])) {
            $errors['email'] = "Invalid email format";
        } elseif (! $this->userRepo->isEmailAvailable($data['email'], $data['id'], 'user')) {
            $errors['email'] = "Email already exists";
        }

        if (! empty($data['password'])) { // Nếu password không rỗng thì mới kiểm tra độ dài
            if (! $this->help->validatePassword($data['password'], 6)) {
                $errors['password'] = "Password must be at least 6 characters";
            }
        }

        return $errors;
    }

}