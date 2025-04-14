<?php 
$title = "Danh sách lịch làm việc";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';


$grouped_schedules = [];
foreach ($schedules as $schedule) {
    $employee_code = $schedule['employee_code']; // Dùng employee_code để nhóm
    $full_name = htmlspecialchars($schedule['full_name']);
    $month = sprintf("%02d", $schedule['month']);

    if (!isset($grouped_schedules[$employee_code])) {
        $grouped_schedules[$employee_code] = [
            'full_name' => $full_name,
            'schedules' => []
        ];
    }

    $grouped_schedules[$employee_code]['schedules'][] = [
        'id' => $schedule['id'],
        'month' => $month,
        'year' => $schedule['year']
    ];
}

// Sắp xếp danh sách nhân viên theo employee_id (hoặc tên nếu muốn)
ksort($grouped_schedules); // Sắp xếp theo employee_id

// Sắp xếp lịch theo tháng/năm trong mỗi nhân viên
foreach ($grouped_schedules as &$employee_schedules) {
    usort($employee_schedules['schedules'], function($a, $b) {
        return strcmp($a['month'] . $a['year'], $b['month'] . $b['year']);
    });
}
unset($employee_schedules); // Hủy tham chiếu sau khi dùng &

?>

<link rel="stylesheet" href="/QuanLyNhanSu/assets/css/list_workschedule.css">
<main id="main" class="main">
    <div class="content">
        <div class="container mt-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">Danh Sách Lịch Làm Việc</h2>
                <a href="<?= base_url_admin_controller('WorkScheduleController.php?action=create') ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm lịch làm
                </a>
            </div>

            <div class="row">
                <?php if (empty($grouped_schedules)): ?>
                    <p>Không có lịch làm việc nào để hiển thị.</p>
                <?php else: ?>
                    <?php foreach ($grouped_schedules as $employee_code => $data): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><?= $data['full_name'] ?> (Mã NV: <?= htmlspecialchars($employee_code) ?>)</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($data['schedules'] as $schedule): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>Tháng <?= $schedule['month'] ?>/<?= $schedule['year'] ?></span>
                                                <div>
                                                    <!-- them id  -->
                                                    <a href="<?= base_url_admin_controller('WorkScheduleController.php?action=detail&id=' . $schedule['id'] . '&month=' . $schedule['month'] . '&year=' . $schedule['year']) ?>"
                                                        class="btn btn-info btn-sm">
                                                            <i class="fas fa-calendar"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
<?php include $bootstrapPath . 'footer.php'; ?>