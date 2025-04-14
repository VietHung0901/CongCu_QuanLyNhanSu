<?php
$title = "Danh sách nhân viên";
$bootstrapPath = __DIR__ . "/../bootstrap/";
include $bootstrapPath . 'header.php';
include $bootstrapPath . 'sidebar.php';
?>
<main id="main" class="main">
    <div class="container mt-5">
        <h2 class="mb-4">Danh Sách Nhân Viên</h2>
        <button class="btn btn-primary mb-3" type="button" class="btn btn-primary mb-3 d-block mx-auto" data-bs-toggle="modal"
            data-bs-target="#departmentModal" onclick="resetForm()">
            Thêm phòng ban
        </button>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên phòng ban</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody id="department-list">
                <?php
                if (!empty($departments)) {
                    foreach ($departments as $department) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($department['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($department['name']) . "</td>";
                        echo "<td><span class='action-btn' onclick='editDepartment(" . $department['id'] . ", \"" . htmlspecialchars($department['name']) . "\")'>Sửa</span></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>Không có phòng ban nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Modal chung cho thêm và sửa -->
        <div class="modal fade" id="departmentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Thêm phòng ban</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="department-form">
                            <input type="hidden" id="departmentId">
                            <div class="mb-3">
                                <label for="departmentName" class="form-label">Tên phòng ban</label>
                                <input type="text" class="form-control" id="departmentName" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" class="btn btn-primary" onclick="saveDepartment()">Lưu</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        loadDepartments(); // Tải danh sách khi trang được load
    });

    // Hàm tải danh sách phòng ban
    function loadDepartments() {
        $.get('DepartmentsController.php?action=list', function(data) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const newTbody = doc.querySelector('#department-list');
            $('#department-list').html(newTbody.innerHTML);
        });
    }

    // Reset form khi mở modal để thêm mới
    function resetForm() {
        $('#modalTitle').text('Thêm phòng ban');
        $('#departmentId').val('');
        $('#departmentName').val('');
    }

    // Mở modal để sửa phòng ban
    function editDepartment(id, name) {
        $('#modalTitle').text('Sửa phòng ban');
        $('#departmentId').val(id);
        $('#departmentName').val(name);
        $('#departmentModal').modal('show');
    }

    // Hàm lưu (thêm hoặc sửa)
    function saveDepartment() {
        let id = $('#departmentId').val();
        let name = $('#departmentName').val();

        if (!name) {
            alert('Vui lòng nhập tên phòng ban!');
            return;
        }

        let url = id ? 'DepartmentsController.php?action=update&id=' + id : 'DepartmentsController.php?action=add';
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                name: name
            },
            success: function(response) {
                console.log(response); // Kiểm tra phản hồi từ server
                if (response.status === "success") {
                    loadDepartments(); // Tải lại danh sách
                    $('#departmentModal').modal('hide'); // Ẩn modal
                    $('.modal-backdrop').remove(); // Xóa lớp overlay nếu có
                } else {
                    alert(response.message || "Lỗi không xác định!");
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Ghi log lỗi
                alert("Đã xảy ra lỗi khi lưu phòng ban!");
            }
        });
    }
</script>
<?php include $bootstrapPath . 'footer.php'; ?>