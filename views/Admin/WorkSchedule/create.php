<?php
$title = "Thêm Lịch Làm Việc";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';

$error = $error ?? '';
$employees = $employees ?? [];
$month = isset($_POST['month']) ? (int)$_POST['month'] : date('m');
$year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
$monthYearDefault = sprintf("%04d-%02d", $year, $month);
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfMonth = date('N', strtotime("$year-$month-01"));
?>

<main id="main" class="main">
    <div class="content">
        <div class="container mt-5">
            <h2>Tạo Lịch Làm Việc</h2>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="<?= base_url_admin_controller('WorkScheduleController.php?action=create') ?>" method="POST" id="scheduleForm">
                <div class="mb-3">
                    <label class="form-label">Nhân viên:</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Chọn nhân viên</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= $employee['id'] ?>">
                                <?= htmlspecialchars($employee['full_name']) ?> (Mã NV: <?= htmlspecialchars($employee['employee_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tháng và Năm:</label>
                    <input type="month" name="month_year" id="monthYearInput" class="form-control" value="<?= $monthYearDefault ?>" required>
                    <input type="hidden" name="month" id="monthHidden" value="<?= $month ?>">
                    <input type="hidden" name="year" id="yearHidden" value="<?= $year ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lịch làm việc:</label>
                    <div class="calendar-grid" id="calendarGrid">
                        <?php for ($i = 1; $i < $firstDayOfMonth; $i++): ?>
                            <div class="calendar-day empty"></div>
                        <?php endfor; ?>

                        <?php for ($day = 1; $day <= $daysInMonth; $day++):
                            $date = sprintf("%04d-%02d-%02d", $year, $month, $day);
                            $selectedShift = $_POST['work_days'][$date] ?? 'canceled';
                        ?>
                            <div class="calendar-day" data-date="<?= $date ?>">
                                <div class="day-number"><?= $day ?></div>
                                <select name="work_days[<?= $date ?>]" class="shift-select form-select form-select-sm" onchange="updateDayStyle(this)">
                                    <?php foreach ($shifts as $shift): ?>
                                        <option value="<?= htmlspecialchars($shift['name']) ?>" <?= $selectedShift === $shift['name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($shift['name']) ?> (<?= $shift['shift_start'] ?> - <?= $shift['shift_end'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-info btn-sm" onclick="applyToSelected()">Áp dụng cho nhiều ngày</button>
                </div>

                <button type="submit" class="btn btn-primary">Tạo lịch</button>
                <a href="<?= base_url_admin_controller('WorkScheduleController.php?action=list') ?>" class="btn btn-secondary">Hủy</a>
            </form>
        </div>
    </div>
</main>

<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }

    .calendar-day {
        border: 1px solid #ddd;
        padding: 5px;
        text-align: center;
        min-height: 80px;
        cursor: pointer;
    }

    .calendar-day.empty {
        border: none;
    }

    .day-number {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .shift-select {
        width: 100%;
    }

    .canceled {
        background-color: #f8f9fa;
    }

    .morning {
        background-color: #e6f3ff;
    }

    .afternoon {
        background-color: #fff3e6;
    }

    .night {
        background-color: #f3e6ff;
    }

    .selected {
        border: 2px solid #007bff;
    }
</style>

<script>
    function updateDayStyle(select) {
        const dayDiv = select.closest('.calendar-day');
        // Xóa các class cũ
        dayDiv.classList.remove('canceled', 'casang', 'cachieu', 'catoi');
        // Chuẩn hóa giá trị từ select để thêm class
        const shiftName = select.value.toLowerCase().replace(/\s+/g, ''); // "Ca sáng" -> "casang", "Không làm" -> "khonglam"
        dayDiv.classList.add(shiftName === 'khônglàm' ? 'canceled' : shiftName);
    }

    function applyToSelected() {
        const selectedShift = prompt("Chọn ca cho các ngày đã chọn: \n(Không làm, Ca sáng, Ca chiều, Ca tối)");
        const validShifts = ['khônglàm', 'casáng', 'cachiều', 'catối'];
        const normalizedShift = selectedShift ? selectedShift.toLowerCase().replace(/\s+/g, '') : '';

        if (!validShifts.includes(normalizedShift)) {
            alert("Ca không hợp lệ! Vui lòng chọn: Không làm, Ca sáng, Ca chiều, Ca tối.");
            return;
        }

        // Ánh xạ giá trị từ prompt sang tên ca trong shifts
        const shiftMap = {
            'khônglàm': 'Không làm',
            'casáng': 'Ca sáng',
            'cachiều': 'Ca chiều',
            'catối': 'Ca tối'
        };

        document.querySelectorAll('.calendar-day.selected .shift-select').forEach(select => {
            select.value = shiftMap[normalizedShift];
            updateDayStyle(select);
        });
    }

    function updateCalendar(month, year) {
        const daysInMonth = new Date(year, month, 0).getDate();
        const firstDay = new Date(year, month - 1, 1).getDay(); // 0: Chủ nhật, 1: Thứ 2, ...
        const grid = document.getElementById('calendarGrid');
        grid.innerHTML = '';

        // Thêm ô trống
        for (let i = 0; i < firstDay; i++) {
            const emptyDiv = document.createElement('div');
            emptyDiv.className = 'calendar-day empty';
            grid.appendChild(emptyDiv);
        }

        // Thêm ngày với dữ liệu từ PHP ($shifts)
        for (let day = 1; day <= daysInMonth; day++) {
            const date = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const div = document.createElement('div');
            div.className = 'calendar-day';
            div.dataset.date = date;
            div.innerHTML = `
            <div class="day-number">${day}</div>
            <select name="work_days[${date}]" class="shift-select form-select form-select-sm" onchange="updateDayStyle(this)">
                <?php foreach ($shifts as $shift): ?>
                    <option value="<?= htmlspecialchars($shift['name']) ?>">
                        <?= htmlspecialchars($shift['name']) ?> (<?= $shift['shift_start'] ?> - <?= $shift['shift_end'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        `;
            grid.appendChild(div);
            updateDayStyle(div.querySelector('.shift-select')); // Áp dụng style ban đầu
        }

        // Gắn sự kiện click
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function(e) {
                if (e.target.tagName !== 'SELECT' && !e.target.classList.contains('form-select')) {
                    this.classList.toggle('selected');
                }
            });
        });
    }

    // Cập nhật lịch khi thay đổi tháng/năm
    document.getElementById('monthYearInput').addEventListener('change', function() {
        const [year, month] = this.value.split('-').map(Number);
        document.getElementById('monthHidden').value = month;
        document.getElementById('yearHidden').value = year;
        updateCalendar(month, year);
    });

    // Khởi tạo style ban đầu
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.shift-select').forEach(select => updateDayStyle(select));
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function(e) {
                if (e.target.tagName !== 'SELECT' && !e.target.classList.contains('form-select')) {
                    this.classList.toggle('selected');
                }
            });
        });
    });
</script>
<?php include $bootstrapPath . 'footer.php'; ?>