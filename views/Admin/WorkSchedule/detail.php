<?php
$title = "Chi Tiết Lịch Làm Việc";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';

$error = $error ?? '';
?>

<main id="main" class="main">
    <div class="content">
        <div class="container-fluid mt-5">
        <h2>Lịch Làm Việc <?= isset($employeeName) ? '- ' . htmlspecialchars($employeeName) : '' ?> - <?= sprintf("%02d/%d", $month, $year) ?></h2>
            <a href="<?= base_url_admin_controller('WorkScheduleController.php?action=list') ?>"
                class="btn btn-outline-dark btn-sm mb-3">Quay lại</a>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="schedule-grid">
                <?php
                // Kiểm tra nếu $daysInMonth không tồn tại hoặc bằng 0 thì không hiển thị lưới
                if (!isset($daysInMonth) || $daysInMonth == 0) {
                    echo "<p>Không có dữ liệu lịch để hiển thị.</p>";
                } else {
                    for ($day = 1; $day <= $daysInMonth; $day++):
                        $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                        $dayData = $scheduleData[$date] ?? [];
                        $shiftType = $dayData['shift_type'] ?? 'Hủy ca';
                        $employees = $dayData['employees'] ?? [];
                        $isWorkingDay = $shiftType !== 'Hủy ca';
                        $isWeekend = date('N', strtotime($date)) >= 6;
                        $class = $isWorkingDay ? ($isWeekend ? 'weekend-working' : 'working-day') : ($isWeekend ? 'weekend-off' : 'day-off');
                        ?>
                        <div class="day-card <?= $class ?>">
                            <div class="day-header">
                                <span class="day-num"><?= $day ?></span>
                                <span class="day-name"><?= date('D', strtotime($date)) ?></span>
                            </div>
                            <div class="day-content">
                                <?php if ($isWorkingDay && !empty($employees)): ?>
                                    <div class="shift-info">
                                        <span
                                            class="shift-time"><?= "$dayData[shift_start] - $dayData[shift_end] ($shiftType)" ?></span>
                                        <?php foreach ($employees as $emp): ?>
                                            <span class="full_name"><?= htmlspecialchars($emp['full_name']) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Nghỉ</span>
                                <?php endif; ?>

                                <?php if (!empty($employees)): ?>
                                    <a href="<?= base_url_admin_controller("WorkScheduleController.php?action=update&id=" . ($employees[0]['work_schedule_id'] ?? '') . "&date=$date") ?>"
                                        class="edit-btn" title="Chỉnh sửa lịch">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endfor;
                } ?>
            </div>
        </div>
    </div>
</main>


<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    .schedule-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
    }

    .day-card {
        border: 1px solid #ddd;
        padding: 10px;
        border-radius: 5px;
        position: relative;
    }

    .day-header {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .day-num {
        font-size: 1.2em;
    }

    .day-name {
        font-size: 0.9em;
        color: #666;
        margin-left: 5px;
    }

    .day-content {
        font-size: 0.9em;
    }

    .shift-info {
        margin-bottom: 5px;
    }

    .shift-time {
        display: block;
        font-weight: bold;
    }

    .check-time {
        display: block;
        font-size: 0.85em;
        color: #555;
    }

    .full_name {
        display: block;
    }

    .edit-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        color: #007bff;
        text-decoration: none;
    }

    .edit-btn:hover {
        color: #0056b3;
    }

    .working-day {
        background-color: #e6f3ff;
    }

    .weekend-working {
        background-color: #fff3e6;
    }

    .weekend-off {
        background-color: #f8f9fa;
    }

    .day-off {
        background-color: #fff;
    }
</style>

<?php include $bootstrapPath . 'footer.php'; ?>