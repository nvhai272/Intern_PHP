<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <?php
        // print_r($old_data);
        include __DIR__ . '/../nav.php';
    ?>

    <div class="container mt-2">
        <h3>Create New User</h3>

        <form class="border p-4" action="/admin/create-user" method="POST" enctype="multipart/form-data">
            <label for="adminName" class="form-label">Name</label>
            <input type="text" name="name" class="form-control mb-1" placeholder="Enter name"
                value="<?php echo isset($old_data['name']) ? htmlspecialchars($old_data['name']) : ''; ?>">
            <?php if (! empty($errors['name'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['name']); ?> </div>
            <?php endif; ?>

            <label for="adminEmail" class="form-label">Email</label>
            <input type="email" name="email" class="form-control mb-1" placeholder="Enter email"
                value="<?php echo isset($old_data['email']) ? htmlspecialchars($old_data['email']) : ''; ?>">
            <?php if (! empty($errors['email'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['email']); ?> </div>
            <?php endif; ?>

            <label for="adminPassword" class="form-label">Password</label>
            <input type="password" name="password" class="form-control mb-1" placeholder="Enter password">
            <?php if (! empty($errors['password'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['password']); ?> </div>
            <?php endif; ?>

            <!-- avatar -->
            <label for="adminAvatar" class="form-label">Avatar File</label>
            <input type="file" name="uploadFileAvatar" class="form-control mb-1" accept="image/*">

            <?php if (! empty($errors['avatar'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['avatar']); ?> </div>
            <?php endif; ?>


            <!-- FB_ID -->
            <label for="adminPassword" class="form-label">Facebook_id</label>
            <input type="text" name="facebook_id" class="form-control mb-1" placeholder="Enter fb_id">
            <?php if (! empty($errors['facebook_id'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['facebook_id']); ?> </div>
            <?php endif; ?>
            <!-- Status -->

            <label for="adminRole" class="form-label">Status</label>
            <select name="status" class="form-control mb-1">
                <option value="1"
                    <?php echo(isset($old_data['status']) && $old_data['status'] == "1") ? 'selected' : ''; ?>>
                    Active</option>
                <option value="2"
                    <?php echo(isset($old_data['status']) && $old_data['status'] == "2") ? 'selected' : ''; ?>>
                    Banned
                </option>
            </select>
            <?php if (! empty($errors['status'])): ?>
            <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['status']); ?> </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-success">Create</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </form>
    </div>

    <?php include __DIR__ . '/../footer.php'; ?>

</body>

</html>