<?php
require_once __DIR__ . "/../Repositories/AuthRepository.php";
require_once __DIR__ . "/../DTO/response/ServiceResponse.php";
class AuthService
{
    private $authRepo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository();
    }

    public function login(string $email, string $password): ServiceResponse
    {
        try {
            $errors = [];

            if (empty(trim($email))) {
                $errors['email'] = 'Email không được để trống';
            }
            if (empty(trim($password))) {
                $errors['password'] = 'Mật khẩu không được để trống';
            }

            if (! empty($errors)) {
                return new ServiceResponse(false, $errors);
            }

            $user = $this->authRepo->findByEmail($email);
            if (! $user) {
                return new ServiceResponse(false, ['email' => 'Email không tồn tại']);
            }

            if (! password_verify($password, $user['password'])) {
                return new ServiceResponse(false, ['password' => 'Mật khẩu không đúng']);
            }

            $role = match (true) {
                $user["source"] === "admins" && $user['role_type'] == 1 => 'super_admin',
                $user["source"] === "admins" && $user['role_type'] == 2 => 'admin',
                default => 'user',
            };
            
            $data = ['id' => $user['id'], 'role' => $role];

            return new ServiceResponse(true, [], $data);
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return new ServiceResponse(false, ['database' => 'Lỗi kết nối database']);
        } catch (Throwable $e) {
            error_log('Lỗi hệ thống: ' . $e->getMessage());
            return new ServiceResponse(false, ['system' => 'Lỗi hệ thống']);
        }
    }

    public function logout()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();

        session_destroy();

    }

}