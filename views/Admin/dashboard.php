<?php
// Định nghĩa đường dẫn tuyệt đối nếu chưa có
$bootstrapPath = __DIR__ . "/bootstrap/";

$title = "Trang chủ";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">

        <div class="row">
            <div class="col-lg-4">
                <div class="card bg-primary text-white p-3">
                    <h5>Nhân viên</h5>
                    <h2>150</h2>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card bg-success text-white p-3">
                    <h5>Phòng ban</h5>
                    <h2>10</h2>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card bg-warning text-dark p-3">
                    <h5>Công việc đang xử lý</h5>
                    <h2>25</h2>
                </div>
            </div>
        </div>
    </section>

</main><!-- End #main -->

<?php include $bootstrapPath . 'footer.php'; ?>