let employee_id;
let isRecognized;
var labeledDescriptors = [];
let videoStream;
let webcamStarted = false;
let intervalId;
let animationFrameId;
//Lấy các điểm ảnh của nhân viên từ db về
async function getEmployeeFaceDescriptor() {
  try {

    const response = await fetch("/QuanLyNhanSu/views/User/api/get_emp_info.php"); // Điều chỉnh đường dẫn nếu cần
    const resdata = await response.json();

    if (resdata.status === "success") {
      console.log("Full Name:", resdata.data.full_name);
      console.log("Employee_code:", resdata.data.employee_code);
      console.log("Face ID:", resdata.data.face_id);
      // Parse JSON thành mảng số
      const parsedArray = JSON.parse(resdata.data.face_id);

      // Chuyển từng mảng con thành Float32Array
      const descriptors = parsedArray.map(desc => new Float32Array(desc));
      labeledDescriptors.push(
        new faceapi.LabeledFaceDescriptors(resdata.data.full_name + "_" + resdata.data.employee_code, descriptors)
      );
      employee_id = resdata.data.id;
      return labeledDescriptors;
    } else {
      alert('Lỗi không thể lấy thông tin nhận diện người dùng: ' + resdata.message);
      return null;
    }
  } catch (error) {
    alert('Lỗi kết nối API: ' + error.message);
    return null;
  }
}

// Gọi API khi trang tải xong
document.addEventListener("DOMContentLoaded", getEmployeeFaceDescriptor);

async function executeCheckingAttendance() {
  const video = document.getElementById("video");
  const videoContainer = document.querySelector(".video-container");
  const startButton = document.getElementById("startButton");
  let modelsLoaded = false;

  await Promise.all([
    faceapi.nets.ssdMobilenetv1.loadFromUri("/QuanLyNhanSu/models_api"),
    faceapi.nets.faceRecognitionNet.loadFromUri("/QuanLyNhanSu/models_api"),
    faceapi.nets.faceLandmark68Net.loadFromUri("/QuanLyNhanSu/models_api"),
  ])
    .then(() => {
      modelsLoaded = true;
      console.log("models loaded successfully");
    })
    .catch(() => {
      alert("models not loaded, please check your model folder location");
    });

  startButton.addEventListener("click", async () => {
    videoContainer.style.display = "flex";
    console.log("webcamStarted", webcamStarted, "modelsLoaded", modelsLoaded);
    if (!webcamStarted && modelsLoaded) {
      await startWebcam();
      webcamStarted = true;
    }
    startButton.disabled = true;
  });

  //Bật cái webcam của trình duyệt lên và xử lý 1 số case của webcam
  async function startWebcam() {
    await navigator.mediaDevices
      .getUserMedia({
        video: true,
        audio: false,
      })
      .then((stream) => {
        video.srcObject = stream;
        videoStream = stream;
      })
      .catch((error) => {
        if (error.name === "NotAllowedError") {
          console.error("Người dùng từ chối quyền truy cập vào webcam.");
          showMessage("Bạn đã từ chối quyền truy cập vào camera. Hãy cấp quyền để sử dụng tính năng này.");
        } else if (error.name === "NotFoundError") {
          console.error("Không tìm thấy webcam hoặc webcam không hoạt động.");
          showMessage("Không tìm thấy webcam hoặc webcam không hoạt động.");
        } else {
          console.error("Lỗi khi mở webcam: ", error);
          showMessage("Có lỗi xảy ra khi mở webcam. Vui lòng thử lại sau.");
        }
      });
  }

  var canvas;

  //Nhận diện điểm ảnh của người dùng qua hình ảnh lấy được từ camera
  video.addEventListener("canplay", () => {
    if (!document.getElementById("faceCanvas")) {
      canvas = faceapi.createCanvasFromMedia(video);
      canvas.id = "faceCanvas";
      videoContainer.appendChild(canvas);
    }
  });

  video.addEventListener("play", async () => {
    const labeledFaceDescriptors = labeledDescriptors;
    if (labeledFaceDescriptors === null) {
      console.log("Lỗi khi lấy face descriptor");
      return;
    }

    const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, 0.5);

    const displaySize = { width: video.width, height: video.height };
    faceapi.matchDimensions(canvas, displaySize);

    intervalId = setInterval(async () => {
      const detections = await faceapi
        .detectAllFaces(video, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.9 }))
        .withFaceLandmarks()
        .withFaceDescriptors();

      const resizedDetections = faceapi.resizeResults(detections, displaySize);

      canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

      const results = resizedDetections.map((d) => {
        return faceMatcher.findBestMatch(d.descriptor);
      });
      isRecognized = results.some(face => face.label !== "unknown");
      results.forEach((result, i) => {
        const box = resizedDetections[i].detection.box;
        const drawBox = new faceapi.draw.DrawBox(box, {
          label: result.label,
        });
        drawBox.draw(canvas);
      });
    }, 200);
  });
}

document.addEventListener("DOMContentLoaded", executeCheckingAttendance);

function sendAttendanceDataToServer() {
  return new Promise((resolve, reject) => {
    if (!isRecognized) {
      showMessage("Error: You are not recognized by the system. Please try again.");
      resolve(false);
      return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/QuanLyNhanSu/views/User/api/handle_attendance.php", true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function () {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          try {
            const response = JSON.parse(xhr.responseText);

            if (response.status === "success") {
              showMessage(response.message || "Attendance recorded successfully.");
              resolve(true);
            } else {
              showMessage(response.message || "An error occurred while recording attendance.");
              resolve(false);
            }
          } catch (e) {
            showMessage("Error: Failed to parse the response from the server.");
            console.error(e);
            resolve(false);
          }
        } else {
          showMessage("Error: Unable to record attendance. HTTP Status: " + xhr.status);
          console.error("HTTP Error", xhr.status, xhr.statusText);
          resolve(false);
        }
      }
    };

    const data = JSON.stringify({ employee_id: employee_id });
    xhr.send(data);
  });
}

function showMessage(message) {
  var messageDiv = document.getElementById("messageDiv");
  messageDiv.style.display = "block";
  messageDiv.innerHTML = message;
  console.log(message);
}

document.getElementById("endAttendance").addEventListener("click", async function () {
  const startButton = document.getElementById("startButton");
  let isSuccess = await sendAttendanceDataToServer();
  console.log(isSuccess);
  if (isSuccess) {
    console.log("Vô disable");
    this.disabled = true;
  }
  stopWebcam();
  startButton.disabled = false;
});

function stopWebcam() {
  const vid = document.getElementById("video");
  const videoContainer = document.querySelector(".video-container");
  const faceCanvas = document.getElementById('faceCanvas');
  clearInterval(intervalId);
  if (faceCanvas) {
    console.log("Xóa canvas");
    faceCanvas.remove();
  }
  if (videoStream) {
    const tracks = videoStream.getTracks();

    tracks.forEach((track) => {
      track.stop();
    });

    vid.srcObject = null;
    videoStream = null;
  }
  videoContainer.style.display = "none";
  webcamStarted = false;
}