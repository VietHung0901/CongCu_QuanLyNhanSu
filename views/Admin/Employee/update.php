<?php
$title = "Cập Nhật Nhân Viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>

<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4 text-center">✏️ Cập Nhật Nhân Viên</h2>

        <div class="card shadow-lg">
            <div class="card-header bg-warning text-white">
                <h4 class="text-center">Chỉnh Sửa Thông Tin Nhân Viên</h4>
            </div>
            <div class="card-body">
                <!-- Hiển thị lỗi nếu có -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="<?= base_url_admin_controller('EmployeeController.php?action=update&id=' . $id) ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Họ Tên:</label>
                        <input type="text" class="form-control" name="full_name"
                            value="<?= htmlspecialchars($full_name); ?>" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" class="form-control" name="email"
                            value="<?= htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Số Điện Thoại:</label>
                        <input type="text" class="form-control" name="phone"
                            value="<?= htmlspecialchars($phone); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phòng Ban:</label>
                        <select class="form-select" name="department_id">
                            <?php
                            require_once "../../config/config.php";
                            $sql = "SELECT id, name FROM departments";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($department_id == $row['id']) ? 'selected' : '';
                                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                                }
                            } else {
                                echo "<option value=''>Không có phòng ban</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">Lưu Cập Nhật</button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <a href="<?= base_url_admin_controller('EmployeeController.php?action=list') ?>" class="btn btn-secondary">⬅ Quay lại danh sách</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include $bootstrapPath . 'footer.php'; ?>