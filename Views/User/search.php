<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

    <?php include __DIR__ . '/../nav.php'; ?>

    <!-- Hiển thị thông báo lỗi -->
    <?php if (! empty($error)): ?>
    <div class="alert alert-danger text-center mt-3">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <!-- Search Form -->
    <div class="container mt-4">
        <form class="border p-4" method="GET" action="">
        <div class="row">
                <!-- Name Input -->
                <div class="col-md-6">
                    <label for="teamName" class="form-label">Name</label>
                    <input type="text" id="teamName" name="name" class="form-control"
                        value="<?php echo isset($_GET['name']) ? htmlspecialchars($_GET['name']) : ''; ?>">
                </div>

                <!-- Email Input -->
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" id="email" name="email" class="form-control"
                        value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                </div>
            </div>
            <div class="mt-3">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="/admin/search-user" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="container mt-4">
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <!-- <th>Action</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($data)): ?>
<?php foreach ($data as $admin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['id']); ?></td>
                    <td><?php echo htmlspecialchars($admin['name']); ?></td>
                    <!-- <td>
                        <a href="edit.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete.php?id=<?php echo $admin['id']; ?>" class="btn btn-sm btn-danger">Delete</a>
                    </td> -->
                </tr>
                <?php endforeach; ?>
<?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">No data found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php
                    $nameParam = ! empty($name) ? '&name=' . urlencode($name) : '';
                ?>

                <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1 . $nameParam; ?>">Prev</a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item<?php echo($i == $currentPage) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i . $nameParam; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1 . $nameParam; ?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>

        </nav>
    </div>

    <!-- footer -->
    <?php include __DIR__ . '/../footer.php'; ?>

</body>

</html>