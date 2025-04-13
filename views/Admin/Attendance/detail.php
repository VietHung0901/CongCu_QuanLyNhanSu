<?php
$title = "Chi Tiết Điểm Danh Nhân Viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>

<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4">Chi Tiết Điểm Danh</h2>
        <a href="AttendanceController.php?action=list&month=<?= $month ?>&year=<?= $year ?>" class="btn btn-secondary mb-3">⬅ Quay Lại</a>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Check-in Time</th>
                    <th>Check-in Type</th>
                    <th>Check-out Time</th>
                    <th>Check-out Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($employeeAttendance)): ?>
                    <?php foreach ($employeeAttendance as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['check_in_time']) ?></td>
                            <td><?= htmlspecialchars($record['check_type_in']) ?></td>
                            <td style="color: <?= ($record['check_out_time'] == 'Chưa check-out') ? 'red' : 'black' ?>">
                                <?= htmlspecialchars($record['check_out_time']) ?>
                            </td>
                            <td style="color: <?= ($record['check_type_out'] == 'Chưa có dữ liệu') ? 'red' : 'black' ?>">
                                <?= htmlspecialchars($record['check_type_out']) ?>
                            </td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có dữ liệu</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include $bootstrapPath . 'footer.php'; ?>