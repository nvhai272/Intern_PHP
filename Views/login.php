<?php 
// session_start(); 

$errors = $_SESSION['errors'] ?? []; // Lấy lỗi từ session
$old_input = $_SESSION['old_input'] ?? []; // Lấy dữ liệu nhập trước đó
unset($_SESSION['errors'], $_SESSION['old_input']); // Chỉ xóa sau khi đã lấy dữ liệu
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="container mt-4">
    <h1 class="text-center text-primary">🔐 Login</h1>

    <form action="/login" method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="text" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email"
                name="email" value="<?= htmlspecialchars($old_input['email'] ?? $_COOKIE['remember_email'] ?? '') ?>"
                >
            <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                id="password" name="password" >
            <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback"><?= htmlspecialchars($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Save account</label>
        </div>

        <button type="submit" class="btn btn-danger">Login</button>
        <a href="/" class="btn btn-primary">🏠 Back Home</a>
    </form>
</body>

</html>