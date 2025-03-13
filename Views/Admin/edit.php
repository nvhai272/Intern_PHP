<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Edit</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include __DIR__ . '/../nav.php'; ?>
    <div class="container">
        <h3>Edit Admin</h3>

        <form class="border p-4" action="/admin/edit-admin" method="POST" enctype="multipart/form-data">
            <div class="d-flex mb-3">

                <input type="hidden" name="current_ava" class="form-control mb-2"
                    value="<?php echo htmlspecialchars($admin['avatar'] ?? $_SESSION['old_data_update']['current_ava'] ?? ''); ?>">


                <div class="me-3">
                    <img src="/Assets/Images/<?php echo htmlspecialchars($admin['avatar'] ?? $_SESSION['old_data_update']['current_ava'] ?? ''); ?>"
                        alt="Avatar" class="rounded-circle" width="120" height="120">
                </div>
                <div class="flex-grow-1">
                    <input type="text" name="id" class="form-control mb-2" readonly
                        value="<?php echo htmlspecialchars($admin['id'] ?? $_SESSION['old_data_update']['id'] ?? ''); ?>">


                    <label for="adminName" class="form-label">Name</label>
                    <input type="text" name="name" class="form-control mb-2"
                        value="<?php echo htmlspecialchars($admin['name'] ?? $_SESSION['old_data_update']['name'] ?? ''); ?>">

                    <?php if (isset($_SESSION['errors_update']['name'])): ?>
                    <div class="text-danger">
                        <?php echo $_SESSION['errors_update']['name']; ?>
                    </div>
                    <?php endif; ?>

                    <label for="adminEmail" class="form-label">Email</label>
                    <input type="text" name="email" class="form-control mb-2"
                        value="<?php echo htmlspecialchars($admin['email'] ?? $_SESSION['old_data_update']['email'] ?? ''); ?>">

                    <?php if (isset($_SESSION['errors_update']['email'])): ?>
                    <div class="text-danger">
                        <?php echo $_SESSION['errors_update']['email']; ?>
                    </div>
                    <?php endif; ?>

                    <label for="adminPassword" class="form-label">Password - Không nhập gì thì vẫn dùng mật khẩu
                        cũ</label>
                    <input type="password" name="password" class="form-control mb-2" placeholder="Enter new password">

                    <?php if (isset($_SESSION['errors_update']['password'])): ?>
                    <div class="text-danger">
                        <?php echo $_SESSION['errors_update']['password']; ?>
                    </div>
                    <?php endif; ?>

                    <label for="adminRole" class="form-label">Role Type</label>
                    <select name="role_type" class="form-control mb-2">
                        <option value="1"
                            <?php echo($admin['role_type'] == "1" || $_SESSION['old_data']['role_type'] == "1") ? 'selected' : ''; ?>>
                            Super Admin
                        </option>
                        <option value="2"
                            <?php echo($admin['role_type'] == "2" || $_SESSION['old_data']['role_type'] == "2") ? 'selected' : ''; ?>>
                            Admin</option>
                    </select>

                    <?php if (isset($_SESSION['errors_update']['role_type'])): ?>
                    <div class="text-danger">
                        <?php echo $_SESSION['errors_update']['role_type']; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <label for="adminAvatar" class="form-label">Change Avatar</label>
            <input type="file" name="uploadFileAvatar" class="form-control mb-3" accept="image/*">

            <?php if (isset($_SESSION['errors_update']['avatar'])): ?>
            <div class="text-danger">
                <?php echo $_SESSION['errors_update']['avatar']; ?>
            </div>
            <?php endif;

                unset($_SESSION['errors_update']); // Xóa lỗi sau khi hiển thị để không hiển thị lại

            ?>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="/admin/search-admin" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php include __DIR__ . '/../footer.php'; ?>

</body>

</html>