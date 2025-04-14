<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chấm Công Nhân Viên</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .table thead {
            background-color: #343a40;
            color: white;
        }

        .card {
            border-radius: 12px;
        }

        .qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .qr-container img {
            max-width: 80%;
            border: 5px solid #007bff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .messageDiv {
            background-color: #333;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            border-radius: 5px;
            font-size: 1em;
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                display: none;
            }
        }

        /*video*/
        canvas {
            position: absolute;

        }

        .video-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #video {
            border-radius: 10px;
            box-shadow: #000;
        }
    </style>
</head>

<body class="container mt-4">

    <h2 class="text-center text-primary mb-4">Chấm Công Nhân Viên</h2>

    <div class="row">
        <!-- Cột trái: QR Code -->
        <div class="col-md-4">
            <div class="card shadow p-3 text-center">
                <h4 class="text-success">Mã QR</h4>
                <div class="qr-container">
                    <img id="qrImage" class="img-fluid" alt="QR Code">
                </div>
                <!-- <button class="btn btn-primary mt-3" onclick="refreshQR()">Làm mới QR</button> -->
            </div>
        </div>

        <!-- Cột phải: Danh sách nhân viên -->
        <div class="col-md-8">
            <div class="card shadow p-3">
                <h4 class="text-danger text-center">Danh Sách Chấm Công</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Tên Nhân Viên</th>
                                <th>Loại Chấm Công</th>
                                <th>Thời Gian</th>
                                <th>Phương Thức</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            require_once '../../config/config.php';
                            $query = "SELECT 
                                e.employee_code,
                                e.full_name, 
                                a.check_in_time, 
                                a.check_out_time, 
                                a.check_type_in, 
                                a.check_type_out
                            FROM attendance_logs a
                            JOIN employees e ON a.employee_id = e.id
                            ORDER BY 
                                GREATEST(IFNULL(a.check_in_time, '0000-00-00 00:00:00'), IFNULL(a.check_out_time, '0000-00-00 00:00:00')) DESC,
                                a.check_out_time DESC, 
                                a.check_in_time DESC";

                            $result = $conn->query($query);

                            while ($row = $result->fetch_assoc()) {
                                // Xác định phương thức chấm công
                                $methodIn = ($row['check_type_in'] == 'QR') ? '<span class="badge bg-primary">QR</span>' : '<span class="badge bg-secondary">FaceID</span>';
                                $methodOut = ($row['check_type_out'] == 'QR') ? '<span class="badge bg-primary">QR</span>' : (($row['check_type_out']) ? '<span class="badge bg-secondary">FaceID</span>' : '<span class="text-muted">Chưa có</span>');

                                // Hiển thị dòng "Check-in"
                                echo "<tr>
                                        <td>{$row['employee_code']}</td>
                                        <td>{$row['full_name']}</td>
                                        <td><span class='badge bg-success'>Check-in</span></td>
                                        <td>{$row['check_in_time']}</td>
                                        <td>{$methodIn}</td>
                                    </tr>";

                                // Nếu có thời gian Check-out thì hiển thị dòng "Check-out"
                                if ($row['check_out_time']) {
                                    echo "<tr>
                                            <td>{$row['employee_code']}</td>
                                            <td>{$row['full_name']}</td>
                                            <td><span class='badge bg-danger'>Check-out</span></td>
                                            <td>{$row['check_out_time']}</td>
                                            <td>{$methodOut}</td>
                                        </tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Chức năng làm mới mã QR -->
    <script>
        $(document).ready(function() {
            refreshQR();
        });

        function refreshQR() {
            $("#qrImage").attr("src", "../../api/controllers/Qr.php?" + new Date().getTime());
        }

        // Tự động cập nhật mã QR mỗi 2 phút
        setInterval(refreshQR, 120000);
    </script>

    <!-- websocket -->
    <script>
        const socket = new WebSocket("ws://localhost:8080/");

        socket.onopen = function() {
            console.log("WebSocket kết nối thành công!");
        };

        socket.onmessage = function(event) {
            console.log("Nhận dữ liệu từ server:", event.data);

            // Tạo một phần tử hàng mới (tr) từ dữ liệu HTML nhận được
            let newRow = document.createElement("tr");
            newRow.innerHTML = event.data;

            // Chọn phần tử tbody của bảng
            let tbody = document.querySelector("tbody");

            // Thêm hàng mới vào đầu bảng
            tbody.prepend(newRow);
            refreshQR();
        };


        socket.onclose = function() {
            console.log("WebSocket đã đóng!");
        };
    </script>


    <!-- websocket -->
</body>

</html>