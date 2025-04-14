<?php
$title = "Thêm Nhân Viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>
<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Thêm Nhân Viên</h2>

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="text-center">Nhập Thông Tin Nhân Viên</h4>
            </div>
            <div class="card-body">
                <!-- Hiển thị lỗi nếu có -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="registerForm">
                    <div class="mb-3">
                        <label for="formControlInput2" class="form-label">Full name</label>
                        <input name="full_name" required class="form-control" type="text" id="formControlInput2" placeholder="Your name..." aria-label="fullname input">
                    </div>
                    <div class="mb-3">
                        <label for="formControlInput1" class="form-label">Email address</label>
                        <input name="email" required type="email" class="form-control" id="formControlInput1" placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="formControlInput3" class="form-label">Phone</label>
                        <input name="phone" required class="form-control" type="number" maxlength="10" id="formControlInput3" placeholder="0332..." aria-label="phone input">
                    </div>
                    <div class="mb-3">
                        <label for="formControlInput4" class="form-label">Department</label>
                        <select name="department_id" class="form-select" id="formControlInput4" aria-label="Department select">
                            <?php
                            require_once "../../config/config.php";
                            $sql = "SELECT id, name FROM departments";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = (isset($_POST['department_id']) && $_POST['department_id'] == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                }
                            } else {
                                echo "<option value=''>Không có phòng ban</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="formControlInput5" class="form-label">Quyền</label>
                        <select name="role" class="form-select" id="formControlInput5" required>
                            <option value='Admin'>Admin</option>
                            <option value='Employee'>Employee</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <h1>Ảnh quét Face_ID</h1>
                        <input name="files[]" type="file" id="file-input" accept="image/png, image/jpeg" onchange="preview()" multiple>
                        <label for="file-input" id="image-label">
                            <i class="fa-solid fa-arrow-up-from-bracket"></i> &nbsp; Choose A Photo
                        </label>
                        <p id="num-of-files" style="display: none;">No Files Chosen</p>
                        <div id="images"></div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Tạo tài khoản</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="<?= base_url_admin_controller('EmployeeController.php?action=list') ?>" class="btn btn-secondary">⬅ Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script defer src="/QuanLyNhanSu/views/Admin/bootstrap/styles/face-api.min.js"></script>
<script defer src="/QuanLyNhanSu/views/Admin/bootstrap/styles/scriptCreateEmployee.js"></script>

<?php include $bootstrapPath . 'footer.php'; ?>