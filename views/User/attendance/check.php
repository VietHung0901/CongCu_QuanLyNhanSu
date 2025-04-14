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

    <div id="messageDiv" class="messageDiv" style="display:none"> </div>

    <div id="statusMessage">
        <div class="attendance-button">
            <button id="startButton">Launch Facial Recognition</button>
            <button id="endButton" style="display:none">End Attendance Process</button>
            <button id="endAttendance">END Attendance Taking</button>
        </div>

        <div class="video-container" style="display:none;">
            <video id="video" width="600" height="450" autoplay></video>
            <!-- <canvas id="overlay"></canvas> -->
        </div>
    </div>

    <script defer src="/QuanLyNhanSu/views/Admin/bootstrap/styles/face-api.min.js"></script>
    <script defer src="/QuanLyNhanSu/views/User/attendance_script/script.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>

</html>