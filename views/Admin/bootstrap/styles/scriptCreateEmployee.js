let fileInput = document.getElementById("file-input");
let imageContainer = document.getElementById("images");
let numOfFiles = document.getElementById("num-of-files");

function preview() {
    imageContainer.innerHTML = "";
    numOfFiles.style.display = "";
    numOfFiles.textContent = `${fileInput.files.length} Files Selected`;

    for (i of fileInput.files) {
        let reader = new FileReader();
        let figure = document.createElement("figure");
        let figCap = document.createElement("figcaption");
        figCap.innerText = i.name;
        figure.appendChild(figCap);
        reader.onload = () => {
            let img = document.createElement("img");
            img.setAttribute("src", reader.result);
            figure.insertBefore(img, figCap);
        }
        imageContainer.appendChild(figure);
        reader.readAsDataURL(i);
    }
}

async function loadModels() {
    try {
        await Promise.all([
            faceapi.nets.ssdMobilenetv1.loadFromUri("/QuanLyNhanSu/models_api"),
            faceapi.nets.faceRecognitionNet.loadFromUri("/QuanLyNhanSu/models_api"),
            faceapi.nets.faceLandmark68Net.loadFromUri("/QuanLyNhanSu/models_api"),
        ]);
        console.log("✅ Models loaded successfully!");
    } catch (error) {
        console.error("❌ Models not loaded. Error:", error);
        alert("Models not loaded, please reload your page.");
    }
}

// Gọi hàm
loadModels();

document.getElementById("registerForm").addEventListener("submit", async function (event) {
    event.preventDefault(); // Ngăn hành động submit mặc định

    // Lấy danh sách file ảnh
    const files = document.getElementById("file-input").files;
    const maxSize = 1024*1024*5; // 5MB

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.size > maxSize) {
            alert(`❌ File "${file.name}" vượt quá giới hạn 5MB! Hãy chọn file có kích thước nhỏ hơn 5MB`);
            imageContainer.innerHTML = "";
            numOfFiles.style.display = "none";
            document.getElementById("file-input").value = "";
            return;
        }
    }
    if (files.length !== 5) {
        alert("Vui lòng chọn đúng 5 ảnh.");
        return;
    }

    // Lấy thông tin từ form
    const formData = new FormData();
    formData.append("email", document.getElementById("formControlInput1").value);
    formData.append("full_name", document.getElementById("formControlInput2").value);
    formData.append("phone", document.getElementById("formControlInput3").value);
    formData.append("department_id", document.getElementById("formControlInput4").value);
    formData.append("role", document.getElementById("formControlInput5").value);

    let descriptors = [];
    for (let i = 0; i < files.length; i++) {
        const img = await loadImage(files[i]);
        const detection = await faceapi.detectSingleFace(img)
            .withFaceLandmarks()
            .withFaceDescriptor();
        if (detection) {
            descriptors.push(Array.from(detection.descriptor)); // Convert `Float32Array` thành mảng JS
            formData.append("files[]", files[i]); // Gửi cả ảnh lên server
        } else {
            console.log(`Không phát hiện khuôn mặt trong ảnh ${files[i].name}`);
        }
    }

    if (descriptors.length < 5) {
        alert("Không phát hiện đủ khuôn mặt trong 5 ảnh.");
        return;
    }
    formData.append("descriptors", JSON.stringify(descriptors)); // Gửi descriptors dưới dạng JSON
    // Gửi dữ liệu lên server   
    const response = await fetch("/QuanLyNhanSu/controllers/Admin/CreateEmployeeController.php", {
        method: "POST",
        body: formData // Gửi dữ liệu dạng multipart/form-data
    });

    const result = await response.json();
    alert(result.message);
});

// Chuyển file ảnh thành đối tượng Image để xử lý với face-api.js
function loadImage(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const img = new Image();
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
}