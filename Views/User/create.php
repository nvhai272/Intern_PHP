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


            <?php
                $defaultAvatar = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBwgHBgkIBwgKCgkLDRYPDQwMDRsUFRAWIB0iIiAdHx8kKDQsJCYxJx8fLT0tMTU3Ojo6Iys/RD84QzQ5OjcBCgoKDQwNGg8PGjclHyU3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3Nzc3N//AABEIAJQAtQMBIgACEQEDEQH/xAAbAAEAAgMBAQAAAAAAAAAAAAAAAQIEBQYDB//EADEQAQABAwEFBwIFBQAAAAAAAAABAgMRBAUSITFBExQiUWFxkSNTJDNCQ3IVNGKSwf/EABYBAQEBAAAAAAAAAAAAAAAAAAABAv/EABYRAQEBAAAAAAAAAAAAAAAAAAARAf/aAAwDAQACEQMRAD8A+iANIAAAAAAJE26arlW7RTNU+UQCMDOt7Lv1xmqYo93p/R7nS7T8FGtQzruy9RRxiaavZh10VW6t2uN2fUFRKAAAAAAAAAAAAAAAAP8AoPbS6evU3oop4RzmXQabT27FO7RGPXzeOzLHY2InHiq4zLMRUoxCRBGGPq9Lb1NOKoje6SyUSDmNRZq09yaK49peTe7VsdrY34jxU8c+jRNYgAAAAAAAAAAAAACXpZo7S9bo86nm99DP4y17oOjojFMRHRZEJRQAAAFLtO9bqpnlMS5aqN2qafKXVy5nVRHersRyipcHiJQqAAAAAAAAAAAAJeukqinVWqp5RU8kTmJiYmeHEiusicwljaG9F/T01xPHHFkwyAAAAK1zu0zM8ocvdq3r1dUdZb7ad/sNNV51cIc/C4IEoVAAAAAAAAAAAAAAGZs7Vzp7sRVP055+jf0VU1UxVTOYnq5XnwZOj11zTTNM+K35T0SK6PIwrG0NPcpjx7s+UvfvNmP3aflB7KXblNuiaqpxEMS/tLT2v1b3pS1Wt1lzVTiZiKOkQsEbQ1NWpvf4RyY4NIgBAAAAAAAAAAASgzgEoZem0F+/xxuUz1lsbWy7NMRv5rnrmRWjzHU4ebpY0enj9qF+72ft0/CUcv4fM4ejqO72vt0f6nd7X26Pgo5fh5p4dHT93tfbo+Ed2sz+3R8FHM5hDpatHYqjjap+GLe2VZrjNEzRK0aQZWp0N6xx3d6nzhigACAAAAAACUCi1NE1TimJmZ6NxoNnxbiK73irnp0hGydJu0xeuRO9PJs2VRER0ThIgAAAAAAIwkBWaYmMTGWr2hs7P1LEcY50tsiQcnxicTwkbbaukx9a3H8oarC4IAVAAAAB76K12+oijHCJzLwbTYlEZuV9eQNtERTERTwiOicqzzEipyZVCC+TKoQWyZVCC2TKoQTkyqEFspyokgVxFdE01RmJc5qLXZX66PXg6Np9s0bt+muP1QYNegFQAAAAbjZMfh6vdp232T/bz7gz8mVcgLZMqmVFsmVcmQWyZVMgtkyrkyC2TKuTILZMq5AWy122fy7c9c4Z7A2v+Vb/AJINUAAAAAA2myKp3K46RIA2EoAAAAAAAAAAAAABrdr1T9KPWZQA18cgAAAf/9k="; // Ảnh avatar mặc định
                $avatarSrc     = isset($old_data['avatar']) && ! empty($old_data['avatar']) ? htmlspecialchars($old_data['avatar']) : $defaultAvatar;
            ?>
            <!-- Avatar Upload + Preview -->
            <div class="mb-3">
                <label for="adminAvatar" class="form-label">Avatar File</label>

                <div class="row align-items-center">
                    <!-- Ô nhập file -->
                    <div class="col-md-6">
                        <input type="file" name="uploadFileAvatar" id="uploadFileAvatar" class="form-control"
                            accept="image/*" onchange="previewAvatar()">
                    </div>

                    <!-- Ảnh xem trước -->
                    <div class="col-md-6 d-flex justify-content-end">
                        <!-- đẩy ảnh về cuối col dùng text-end cũng được -->
                        <img id="avatarPreview"
                            src="<?php echo isset($avatarSrc) ? htmlspecialchars($avatarSrc) : ''; ?>"
                            alt="Avatar Preview" class="img-thumbnail" style="max-width: 150px;">
                    </div>
                </div>

                <?php if (! empty($errors['avatar'])): ?>
                <div class="text-danger mb-1"><?php echo htmlspecialchars($errors['avatar']); ?> </div>
                <?php endif; ?>
            </div>


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

    <script>
    function previewAvatar() {
        const fileInput = document.getElementById('uploadFileAvatar');
        const preview = document.getElementById('avatarPreview');

        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    }

    function resetPreview() {
        document.getElementById('avatarPreview').src = "<?php echo $avatarSrc; ?>";
    }
    </script>

</body>

</html>