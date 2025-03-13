<?php
require_once __DIR__ . '/BaseRepository.php';

class AuthRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    // tìm kiếm email và lấy password để check đăng nhập
    public function findByEmail(string $email): ?array
    {
        try {
            $sql = "SELECT 'admins' AS source, id, password, role_type
            FROM admins
            WHERE email = :email AND del_flag = 0
            UNION
            SELECT 'users' AS source, id, password, NULL AS role_type
            FROM users
            WHERE email = :email AND del_flag = 0
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return null;
        }
    }

}
