<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link " href="<?= base_url_admin_controller('DashboardController.php') ?>">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li><!-- End Dashboard Nav -->

        <!-- Quản lý nhân viên -->
        <li class="nav-item">
            <a class="nav-link collapsed" data-bs-target="#employee-nav" data-bs-toggle="collapse" href="#">
                <i class="bi bi-people"></i><span>Quản lý nhân viên</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="employee-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
                <li>
                    <a href="<?= base_url_admin_controller('EmployeeController.php?action=list') ?>">
                        <i class="bi bi-circle"></i><span>Danh sách nhân viên</span>
                    </a>
                </li>
                <li>
                    <a href="<?= base_url_admin_controller('EmployeeController.php?action=create') ?>">
                        <i class="bi bi-circle"></i><span>Thêm nhân viên</span>
                    </a>
                </li>
            </ul>
        </li><!-- End Quản lý nhân viên -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= base_url_admin_controller('WorkScheduleController.php?action=list')?>">
                <i class="bi bi-building"></i>
                <span>Xếp lịch làm</span>
            </a>
        </li><!-- End Quản lý phòng ban -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= base_url_admin_controller('AttendanceController.php?action=list')?>">
                <i class="bi bi-building"></i>
                <span>Thống kê công</span>
            </a>
        </li><!-- End Quản lý phòng ban -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= base_url_admin_controller('DepartmentsController.php?action=list')?>">
                <i class="bi bi-building"></i>
                <span>Quản lý phòng ban</span>
            </a>
        </li><!-- End Quản lý phòng ban -->

        <li class="nav-item">
            <a class="nav-link collapsed" href="<?= base_url_admin_controller('AuthController.php?action=logout') ?>">
                <i class="bi bi-box-arrow-right"></i>
                <span>Đăng xuất</span>
            </a>
        </li><!-- End Đăng xuất -->
    </ul>
</aside><!-- End Sidebar -->