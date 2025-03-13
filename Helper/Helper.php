<?php
class Helper
{
    private static $instance = null;

    // Ngăn chặn tạo mới từ bên ngoài
    private function __construct()
    {}

    // Ngăn chặn clone instance
    private function __clone()
    {}

    // Ngăn chặn unserialize
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function validateRequired($value)
    {
        // if ($value !== trim($value)) {
        //     return false;
        // }

        if (empty(trim($value))) {
            return false;
        }
        return true;
    }

    public function validateEmail($email)
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return true;
    }

    public function validatePassword($password, $minLength = 6)
    {
        if (strlen($password) < $minLength) {
            return false;
        }
        return true;
    }

    public function generateFileName($file)
    {
        // Kiểm tra nếu file không tồn tại hoặc không có tên
        if (empty($file) || empty($file['name'])) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        // Nếu không có phần mở rộng (người dùng gửi file lỗi), trả về null
        if (empty($extension)) {
            return null;
        }

        return time() . '_' . uniqid() . '.' . $extension;
    }

    public function saveAvatar($fileName, $file)
    {
        $uploadDir  = '/home/nvhai272/Desktop/GitHub/Intern_PHP/Public/Assets/Images/'; // Sửa đường dẫn
        $uploadPath = $uploadDir . $fileName;

        // Kiểm tra và tạo thư mục nếu chưa có
        if (! file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        print_r($file);
        print_r($fileName);
        var_dump(value: $uploadPath);

        // Di chuyển file vào thư mục
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            return true;
        }

        return false;
    }

    public function deleteAvatar($fileName)
    {
        $uploadDir = __DIR__ . '/../Public/Assets/Images/';
        $filePath  = $uploadDir . $fileName;

        // Kiểm tra nếu tệp tồn tại
        if (file_exists($filePath)) {
            // Xóa tệp
            if (unlink($filePath)) {
                return true;
            }
        }

        return false;
    }

    public function sanitizeSortOrder($sort, $order, $allowedColumns = ['id', 'name', 'status', 'role_type'])
    {
        if (! in_array($sort, $allowedColumns)) {
            $sort = 'id';
        }
        $order = ($order === 'desc') ? 'DESC' : 'ASC';

        return [$sort, $order];
    }

}
