<?php
$title = "Danh sách nhân viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>

<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4">Danh Sách Nhân Viên</h2>
        <a href="<?= base_url_admin_controller('EmployeeController.php?action=create') ?>" class="btn btn-primary mb-3">
            ➕ Thêm Nhân Viên
        </a>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Mã NV</th>
                    <th>Họ Tên</th>
                    <th>Email</th>
                    <th>SĐT</th>
                    <th>Phòng Ban</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['id']) ?></td>
                        <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                        <td><?= htmlspecialchars($emp['full_name']) ?></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                        <td><?= htmlspecialchars($emp['department']) ?></td>
                        <td>
                            <a href="<?= base_url_admin_controller('EmployeeController.php?action=update&id=' . $emp['id']) ?>"
                                class="btn btn-warning btn-sm">✏️ Cập Nhật</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include $bootstrapPath . 'footer.php'; ?>