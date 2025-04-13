<?php 
$title = "Cập Nhật Lịch Làm Việc";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';


require_once __DIR__ . "/../../../models/WorkScheduleModel.php";
$model = new WorkScheduleModel($GLOBALS['conn']);
$employees = $model->getAllEmployees();
$date = $_GET['date'] ?? '';
$displayDate = $date ?: 'Chưa chọn ngày';
// Biến $schedule và $details được truyền từ Controller
$error = isset($error) ? $error : '';
?>


<main id="main" class="main">
    <div class="content">
        <div class="container-fluid mt-5">
            <h2>Cập nhật lịch làm việc - <?= $date ?></h2>
            <a href="<?= base_url_admin_controller("WorkScheduleController.php?action=detail&id={$schedule['id']}&month={$schedule['month']}&year={$schedule['year']}") ?>" class="btn btn-outline-dark btn-sm mb-3">Quay lại</a>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= base_url_admin_controller("WorkScheduleController.php?action=update&id={$workScheduleId}&date={$date}") ?>">
                <div class="mb-3">
                    <label class="form-label">Nhân viên:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($schedule['full_name'] ?? '') ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ngày:</label>
                    <input type="text" class="form-control" value="<?= $date ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Chọn ca:</label>
                    <select name="shift_id" class="form-select"> 
                        <?php 
                        $currentShiftId = isset($details[$date]['shift_id']) ? (int)$details[$date]['shift_id'] : 0; // Ép kiểu để đồng bộ
                        foreach ($shifts as $shift): 
                            $shiftName = $shift['name'] === 'Không làm' ? 'Hủy ca' : $shift['name'];
                            $timeDisplay = ($shift['shift_start'] && $shift['shift_end']) ? " ({$shift['shift_start']} - {$shift['shift_end']})" : '';
                        ?>
                            <option value="<?= $shift['id'] ?>" <?= (int)$shift['id'] === $currentShiftId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($shiftName . $timeDisplay) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Cập nhật</button>
            </form>
        </div>
    </div>
</main>

<style>
.card {
    max-width: 500px;
    margin: 0 auto;
    border-radius: 10px;
}
.form-label {
    font-weight: bold;
}
</style>


<?php include $bootstrapPath . 'footer.php'; ?>