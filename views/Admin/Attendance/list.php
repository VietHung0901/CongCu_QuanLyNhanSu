<?php
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$title = "Thống kê công làm của nhân viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>

<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4">Thống Kê Công Làm</h2>

        <form method="GET" action="">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="month" class="form-label">Tháng</label>
                    <select name="month" id="month" class="form-select">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= ($m == $selectedMonth) ? 'selected' : '' ?>><?= $m ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Năm</label>
                    <select name="year" id="year" class="form-select">
                        <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= ($y == $selectedYear) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">📊 Thống Kê</button>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Mã NV</th>
                    <th>Họ Tên</th>
                    <th>Tổng Ngày Công</th>
                    <th>Số Giờ Làm</th>
                    <th>Đi Trễ</th>
                    <th>Vắng Mặt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendanceRecords as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['employee_code']) ?></td>
                        <td><?= htmlspecialchars($record['full_name']) ?></td>
                        <td><?= htmlspecialchars($record['total_days']) ?></td>
                        <td><?= htmlspecialchars($record['total_hours']) ?></td>
                        <td><?= htmlspecialchars($record['late_count']) ?></td>
                        <td><?= htmlspecialchars($record['absent_count']) ?></td>
                        <td>
                            <a href="AttendanceController.php?action=detail&employee_id=<?= $record['employee_id'] ?>&month=<?= $month ?>&year=<?= $year ?>"
                                class="btn btn-info">📋 Chi Tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include $bootstrapPath . 'footer.php'; ?>