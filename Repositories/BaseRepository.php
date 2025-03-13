<?php
require_once '../Config/Database.php';
require_once dirname(__DIR__) . "/Interfaces/IRepository.php";

abstract class BaseRepository implements IRepository
{
    protected PDO $db;
    protected $table;
    protected $model;

    public function __construct($table = null, $model = null)
    {
        $this->table = $table;
        $this->model = $model;
        $this->db    = Database::getInstance()->getConnection();
    }

private function sanitizeSortOrder($sort, $order)
    {
        $allowedColumns = ['id', 'name', 'status', 'role_type'];
        if (!in_array($sort, $allowedColumns)) {
            $sort = 'id';
        }
        $order = ($order === 'desc') ? 'DESC' : 'ASC';

        return [$sort, $order];
    }

    public function fetchAll($sort, $order)
    {
        try {

            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE del_flag = 0 ORDER BY $sort COLLATE utf8mb4_unicode_ci $order");
            $stmt->execute();
            // $result = $stmt->fetchAll();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (! $results) {
                return [];
            }

            // trả về cái này không tối ưu
            // return array_map(fn($data) => new $this->model($data), $results);

            return $results;
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return [];
        } catch (Throwable $e) {
            error_log('Lỗi không xác định: ' . $e->getMessage());
            return [];
        }
    }

    public function findById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND del_flag = 0");
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            // return $data ? new $this->model($data) : null;
            return $data ? $data : null;
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return null;
        } catch (Throwable $e) {
            error_log('Lỗi không xác định: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $data): bool
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values  = ":" . implode(", :", array_keys($data));

            $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Lỗi không xác định: ' . $e->getMessage());
            return false;
        }
    }

    public function update($id, array $data): bool
    {
        if (empty($id) || empty($data)) {
            error_log("Lỗi: ID hoặc dữ liệu cập nhật bị rỗng.");
            return false;
        }

        // Lọc dữ liệu rỗng hoặc null
        $filteredData = array_filter($data, fn($value) => ! is_null($value) && ! (is_string($value) && $value === ''));

        try {
            // Tạo danh sách fields chỉ với các giá trị hợp lệ
            $fields = implode(", ", array_map(fn($key) => "{$key} = :{$key}", array_keys($filteredData)));

            $stmt = $this->db->prepare("UPDATE {$this->table} SET {$fields} WHERE id = :id");

            foreach ($filteredData as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Lỗi không xác định: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET del_flag = 1 WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return false;
        }
    }

    // check email exist create
    public function checkEmailExist(string $email): bool
    {
        try {
            $sql = "SELECT EXISTS (
                        -- SELECT 1 FROM users WHERE email = :email AND del_flag = 0
                        SELECT 1 FROM users WHERE email = :email

                    )
                    OR EXISTS (
                        -- SELECT 1 FROM admins WHERE email = :email AND del_flag = 0
                        SELECT 1 FROM admins WHERE email = :email
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return (bool) $stmt->fetchColumn(); // Trả về true nếu email tồn tại ở một trong hai bảng
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Message' . $e->getMessage());
            return false;
        }
    }

    public function isEmailAvailable(string $email, int $id, string $table): bool
    {
        try {

            if ($table === 'admin') {
                $sql = "SELECT NOT EXISTS (
                SELECT 1 FROM users WHERE email = :email
            ) AND NOT EXISTS (
                SELECT 1 FROM admins WHERE email = :email AND id != :id
            )";
            } else {
                $sql = "SELECT NOT EXISTS (
                SELECT 1 FROM users WHERE email = :email AND id != :id
            ) AND NOT EXISTS (
                SELECT 1 FROM admins WHERE email = :email
            )";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return (bool) $stmt->fetchColumn(); // Trả về true nếu email hợp lệ
        } catch (PDOException $e) {
            error_log('Lỗi database: ' . $e->getMessage());
            return false;
        } catch (Throwable $e) {
            error_log('Message' . $e->getMessage());
            return false;
        }
    }

   
    // Lấy danh sách có phân trang
    public function fetchAllWithPagination($limit, $offset)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE del_flag = 0 LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByNameOrEmailWithPagination($name, $email, $limit, $offset)
    {
        $query  = "SELECT * FROM {$this->table} WHERE del_flag = 0";
        $params = [];

        if (! empty($name)) {
            $query .= " AND name LIKE :name";
            $params[':name'] = "%$name%";
        }

        if (! empty($email)) {
            $query .= " AND email LIKE :email";
            $params[':email'] = "%$email%";
        }

        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

// Lấy tổng số để tính số trang
    public function getTotal()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE del_flag = 0";
        return $this->db->query($sql)->fetchColumn();
    }

    public function getTotalByNameOrEmail($name, $email)
    {
        $query  = "SELECT COUNT(*) as total FROM {$this->table} WHERE del_flag = 0";
        $params = [];

        if (! empty($name)) {
            $query .= " AND name LIKE :name";
            $params[':name'] = "%$name%";
        }

        if (! empty($email)) {
            $query .= " AND email LIKE :email";
            $params[':email'] = "%$email%";
        }

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

}
