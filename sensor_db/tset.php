<?php
    include_once '../db.php';
    $sql = "SELECT alcohol.alcoholvalue as `value`,users.name as `name` FROM `users` INNER JOIN `alcohol` ON users.rfid = alcohol.rfid";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo '<pre>';
        print_r($row);
        echo '</pre>';
    }
    
    
    // Handle delete alcohol record
    if (isset($_POST['delete_alcohol'])) {
        $alcohol_id = $_POST['alcohol_id'];
        $sql = "DELETE FROM alcohol WHERE id=$alcohol_id";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['admin_message'] = "Alcohol record deleted successfully.";
        } else {
            $_SESSION['admin_message'] = "Error deleting alcohol record: " . $conn->error;
        }
        header("Location: ../admin");
        exit();
    }
    
    // Handle delete users
    if (isset($_POST['delete_user'])) {
        $rfid = isset($_POST['rfid']) ? $_POST['rfid'] : '';
    
        if (!empty($rfid)) {
            $sql = "DELETE FROM users WHERE rfid = ?";
            $stmt = $conn->prepare($sql);
    
            if ($stmt) {
                $stmt->bind_param("s", $rfid); 
    
                if ($stmt->execute()) {
                    $_SESSION['admin_message'] = "User deleted successfully.";
                } else {
                    // More specific error message
                    $_SESSION['admin_message'] = "Error deleting user: " . $stmt->error; 
                }
    
                $stmt->close();
            } else {
                $_SESSION['admin_message'] = "Error preparing statement: " . $conn->error;
            }
        } else {
            $_SESSION['admin_message'] = "RFID not provided.";
        }
    
        header("Location: ../admin");
        exit();
    }
    
    // Handle adding a new user
    if (isset($_POST['add_user'])) {
        $rfid = mysqli_real_escape_string($conn, $_POST['rfid']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $identification = mysqli_real_escape_string($conn, $_POST['identification']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $admin = isset($_POST['admin']) ? 1 : 0;
    
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "INSERT INTO users (rfid, username, password, name, email, identification,department, admin) VALUES ('$rfid', '$username', '$hashedPassword', '$name', '$email', '$identification','$department', $admin)";
    
        if ($conn->query($sql) === TRUE) {
            $_SESSION['admin_message'] = "New user added successfully.";
        } else {
            $_SESSION['admin_message'] = "Error adding user: " . $conn->error;
        }
        header("Location: ../admin"); 
        exit();
    }
    
    // Handle adding a new alcohol record
    if (isset($_POST['add_alcohol'])) {
        $rfid = $_POST['rfid']; 
        $alcoholvalue = $_POST['alcoholvalue'];
    
        // Check if the RFID exists in the 'users' table
        $checkSql = "SELECT rfid FROM users WHERE rfid = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $rfid);
        $checkStmt->execute();
        $checkStmt->store_result(); // Important to get the result count
    
        if ($checkStmt->num_rows > 0) { 
    
            $sql = "INSERT INTO alcohol (rfid, alcoholvalue) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
    
            if ($stmt) {
                $stmt->bind_param("sd", $rfid, $alcoholvalue); 
    
                if ($stmt->execute()) {
                    $_SESSION['admin_message'] = "New alcohol record added successfully.";
                } else {
                    $_SESSION['admin_message'] = "Error adding alcohol record: " . $stmt->error;
                }
                $stmt->close(); 
            } else {
                $_SESSION['admin_message'] = "Error preparing statement: " . $conn->error;
            } 
    
        } else {
            // RFID does NOT exist, handle this case (e.g., show an error)
            $_SESSION['admin_message'] = "Please enter username and rfid correctly ";     }
    
    
    
        header("Location: ../admin"); 
        exit();
    }
    
    
    // Handle editing alcohol record
    if (isset($_POST['edit_alcohol'])) {
        $alcohol_id = $_POST['alcohol_id'];
        $rfid = mysqli_real_escape_string($conn, $_POST['rfid']);
        $alcoholvalue = mysqli_real_escape_string($conn, $_POST['alcoholvalue']);
    
        $sql = "UPDATE alcohol SET rfid='$rfid', alcoholvalue='$alcoholvalue' WHERE id=$alcohol_id";
    
        if ($conn->query($sql) === TRUE) {
            $_SESSION['admin_message'] = "Alcohol record updated successfully.";
        } else {
            $_SESSION['admin_message'] = "Error updating alcohol record: " . $conn->error;
        }
        header("Location: ../admin");
        exit();
    }
    
    
    
    // Handle editing user record
    if (isset($_POST['edit_user'])) {
        // Sanitize input values 
        $rfidToUpdate = mysqli_real_escape_string($conn, $_POST['rfid']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $identification = mysqli_real_escape_string($conn, $_POST['identification']);
        $department = mysqli_real_escape_string($conn, $_POST['department']);
        $admin = isset($_POST['admin']) ? 1 : 0; 
    
        // Use prepared statements to prevent SQL injection
        $sql = "UPDATE users SET 
                    username = ?,
                    name = ?,
                    email = ?,
                    identification = ?,
                    department = ?,
                    admin = ?
                WHERE rfid = ?"; // Update based on RFID
    
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            // Bind parameters 
            $stmt->bind_param("sssssis", $username, $name, $email, $identification, $department, $admin, $rfidToUpdate);
    
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "User record updated successfully.";
            } else {
                $_SESSION['admin_message'] = "Error updating user record: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['admin_message'] = "Error preparing statement: " . $conn->error;
        }
    
        header("Location: ../admin");
        exit();
    }
    
    // Fetch alcohol data
    $sql = "SELECT alcohol.id, alcohol.rfid, alcohol.alcoholvalue, users.name, users.identification, users.department, alcohol.created_at, alcohol.updated_at
            FROM alcohol
            INNER JOIN users ON alcohol.rfid = users.rfid";
    $alcohol_result = $conn->query($sql);
    
    
    
    // Fetch users data
    $users_sql = "SELECT * FROM users";
    $users_result = $conn->query($users_sql);
    
    $conn->close();
    ?>
    
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link rel="icon" href="https://i.ibb.co/stFW6JB/removebg-preview.png" type="image/png">
        <style>
            body {
                background-color: #f8f9fa;
            }
    
            .container {
                margin-top: 30px; /* ลด margin บน */
            }
    
            h1, h2 {
                text-align: center;
                margin-bottom: 20px; /* ลด margin ล่าง */
            }
    
            table {
                background-color: #fff;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                border-radius: 8px;
            }
    
            th {
                background-color: #007bff;
                color: #fff;
            }
    
            .btn-primary {
                background-color: #28a745;
                border-color: #28a745; 
            }
    
            .btn-primary:hover {
                background-color: #218838;
                border-color: #1e7e34;
            }
    
            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
            }
    
            .btn-danger:hover {
                background-color: #c82333;
                border-color: #bd2130;
            }
    
            .alert {
                margin-top: 20px;
            }
    
            .btn-logout {
                position: relative; /* เปลี่ยนเป็น relative */
                top: 0;
                right: 0;
                margin-bottom: 10px; /* เพิ่มระยะห่างด้านล่าง */
            }
    
            .form-container {
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
    
            table th {
                color: black;
            }
    
            .user-welcome {
                font-size: 18px;
                margin-bottom: 0;
                font-weight: 600;
            }
    
            .username {
                color: #007bff;
            }
    
            .high-alcohol {
                background-color: #f8d7da;
            }
    
            .low-alcohol {
                background-color: #d4edda;
            }
            #searchInput {
                border-radius: 5px 0 0 5px;
            }
            #searchButton {
                border-radius: 0 5px 5px 0;
            }
            /* Responsive design */
            @media (max-width: 768px) {  /* สำหรับ iPad และขนาดเล็กกว่า */
    
                h1, h2 {
                    font-size: 1.5rem; /* ลดขนาดตัวอักษร h1, h2 */
                }
    
                table {
                    font-size: 12px; /* ลดขนาดตัวอักษรในตาราง */
                }
    
                .btn, .btn-sm {
                    font-size: 12px; /* ลดขนาดตัวอักษรบนปุ่ม */
                    padding: 5px 10px; /* ลด padding บนปุ่ม */
                }
    
                .form-control {
                    font-size: 12px; /* ลดขนาดตัวอักษรใน input */
                }
    
                .table-responsive {
                    overflow-x: auto;
                }
    
                .modal-dialog {
                    max-width: 95%; /* ปรับ Modal ให้เกือบเต็มหน้าจอ */
                }
    
                .modal-body {
                    font-size: 14px; /* ปรับขนาดตัวอักษรใน Modal */
                }
            }
            .error-message {
                background-color: red; /* Set the background color to red */
                color: white; /* Set text color to white for contrast */
                padding: 10px; /* Add some padding */
                border-radius: 5px; /* Optional: Round the corners */
            }
        </style>
    </head>
    <body>
     
        <div class="container">
            <div class="row mb-3">
                <div class="col-12 text-end">
                    <?php if (isset($_SESSION['username'])): ?>
                        <p class="user-welcome">Welcome, <span class="username"><?php echo $_SESSION['username']; ?>!</span></p>
                    <?php endif; ?>
                    <a href="../logout.php" class="btn btn-danger btn-logout">ออกจากระบบ</a>
                </div>
            </div>
    
            <h1><i class=""></i> Admin Panel</h1> 
    
             <!-- search bar and download buttons
            <div class="row mb-3">
                <div class="col-12"> 
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="ค้นหาข้อมูล..." id="searchInput">
                        <button class="btn btn-dark" type="button" id="searchButton">
                            <i class="fas fa-search"></i> ค้นหา
                        </button>
                    </div>
                </div>
            </div> -->
    
            <div class="row mb-3">
                <div class="col-12 text-end"> 
                    <a href="../csv" class="btn btn-success">
                        <i class="fas fa-download"></i> ดาวน์โหลดข้อมูลเป็น CSV
                    </a>
                    <a href="../chart" class="btn btn-info">
                        <i class="fas fa-chart-pie"></i> แผนภูมิ
                    </a>
                </div>
            </div>
    
            <?php if (isset($_SESSION['admin_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['admin_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['admin_message']); ?>
            <?php endif; ?>
    
            <div class="row">
                <div class="col-md-6">
                    <div class="form-container">
                        <h2><i class="fas fa-user-plus"></i> เพิ่มผู้ใช้งานใหม่</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="rfid" class="form-label">RFID:</label>
                                <input type="text" name="rfid" id="rfid" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" name="username" id="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name:</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="identification" class="form-label">Identification:</label>
                                <input type="text" name="identification" id="identification" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="department" class="form-label">Department:</label>
                                <input type="text" name="department" id="department" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="add_user" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> เพิ่มผู้ใช้
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-container">
                        <h2><i class="fas fa-wine-bottle"></i> เพิ่มข้อมูลแอลกอฮอล์</h2>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="rfid" class="form-label">RFID:</label>
                                <input type="text" name="rfid" id="rfid" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="alcoholvalue" class="form-label">Alcohol Value:</label>
                                <input type="text" name="alcoholvalue" id="alcoholvalue" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="add_alcohol" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> เพิ่มข้อมูล
                            </button>
                        </form>
                    </div>
                </div>
            </div>
    
            <div class="row">
                <div class="col-md-12">
                    <h2><i class="fas fa-users"></i> รายชื่อผู้ใช้งาน</h2>
                    <div class="table-responsive"></div>
                    <table id="usersTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>RFID</th>
                                <th>ชื่อ</th>
                                <th>เลขประจำตัว</th>
                                <th>Username</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Admin</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['rfid']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['identification']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['admin'] == 1 ? 'ใช่' : 'ไม่ใช่'; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm edit-user-btn" data-bs-toggle="modal" data-bs-target="#editUserModal" data-user-id="" data-rfid="<?php echo $row['rfid']; ?>" data-username="<?php echo $row['username']; ?>" data-name="<?php echo $row['name']; ?>" data-email="<?php echo $row['email']; ?>" data-identification="<?php echo $row['identification']; ?>" data-admin="<?php echo $row['admin']; ?>" data-department="<?php echo $row['department']; ?>">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button>
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display: inline-block;">
                                            <input type="hidden" name="rfid" value="<?php echo $row['rfid']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i> ลบ
                                            </button>
                                        </form>
                                        
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
    
                    </table>
                </div>
            </div>
    
            <div class="row mt-4">
                <div class="col-md-12">
                    <h2><i class="fas fa-database"></i> ข้อมูลแอลกอฮอล์</h2>
                    <div class="table-responsive"></div>
                    <table id="alcoholTable" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>RFID</th>
                                <th>ค่าแอลกอฮอล์</th>
                                <th>ชื่อ</th>
                                <th>เลขประจำตัว</th>
                                <th>เเผนก</th>
                                <th>สร้างเมื่อ</th>
                                <th>อัปเดตเมื่อ</th>
                                <th>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $alcohol_result->fetch_assoc()): ?>
                                <tr class="alcohol-row <?php echo ($row['alcoholvalue'] > 20) ? 'high-alcohol' : 'low-alcohol'; ?>">
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['rfid']; ?></td>
                                    <td><?php echo $row['alcoholvalue']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td><?php echo $row['identification']; ?></td>
                                    <td><?php echo $row['department']; ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td><?php echo $row['updated_at']; ?></td>
                                    <td>
                                        <!-- <button type="button" class="btn btn-primary btn-sm edit-alcohol-btn" data-bs-toggle="modal" data-bs-target="#editAlcoholModal" data-alcohol-id="<?php echo $row['id']; ?>" data-rfid="<?php echo $row['rfid']; ?>" data-alcoholvalue="<?php echo $row['alcoholvalue']; ?>" data-name="<?php echo $row['name']; ?>"  data-identification="<?php echo $row['identification']; ?>">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button> -->
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="text-align: center;">
                                            <input type="hidden" name="alcohol_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_alcohol" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
    
        </div>
    
       <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">แก้ไขข้อมูลผู้ใช้</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUserForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="editUserId">
                    
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username:</label>
                            <input type="text" name="username" id="editUsername" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editName" class="form-label">Name:</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
    
                        <div class="mb-3">
                            <label for="editIdentification" class="form-label">Identification:</label>
                            <input type="text" name="identification" id="editIdentification" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDepartment" class="form-label">Department:</label>
                            <input type="text" name="department" id="editDepartment" class="form-control" required>
                        </div> 
                        
                        
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email:</label>
                            <input type="email" name="email" id="editEmail" class="form-control" required>
                        </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" name="edit_user" class="btn btn-primary">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
        <!-- Edit Alcohol Modal -->
        <div class="modal fade" id="editAlcoholModal" tabindex="-1" aria-labelledby="editAlcoholModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAlcoholModalLabel">แก้ไขข้อมูลแอลกอฮอล์</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editAlcoholForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="alcohol_id" id="editAlcoholId">
                            <div class="mb-3">
                                <label for="editRfid" class="form-label">RFID:</label>
                                <input type="text" name="rfid" id="editRfid" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="editAlcoholValue" class="form-label">Alcohol Value:</label>
                                <input type="text" name="alcoholvalue" id="editAlcoholValue" class="form-control" required>
                            </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            <button type="submit" name="edit_alcohol" class="btn btn-primary">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
        <script>
            $(document).ready(function() {
                
                // Initialize DataTables with Thai language
                var userTable = $('#usersTable').DataTable({
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                        "infoEmpty": "ไม่พบข้อมูล",
                        "infoFiltered": "(กรองจาก _MAX_ รายการทั้งหมด)",
                        "search": "ค้นหา:",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
    
                var alcoholTable = $('#alcoholTable').DataTable({
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน",
                        "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                        "infoEmpty": "ไม่พบข้อมูล",
                        "infoFiltered": "(กรองจาก _MAX_ รายการทั้งหมด)",
                        "search": "ค้นหา:",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    }
                });
    
                 // ฟังก์ชันค้นหาเมื่อคลิกปุ่มค้นหา
                $('#searchButton').on('click', function() {
                    var searchTerm = $('#searchInput').val();
                    dataTable.search(searchTerm).draw();
                });
    
                 // ฟังก์ชันค้นหาเมื่อพิมพ์ในช่องค้นหาแล้วกด Enter
                  $('#searchInput').on('keypress', function(e) {
                    if (e.which == 13) { // Enter key code
                    var searchTerm = $('#searchInput').val();
                    dataTable.search(searchTerm).draw();
                    }
                 });
                    
                    
                 // Handle edit user button click
                 $('.edit-user-btn').on('click', function() {
                    $('#editRfid').val($(this).data('rfid'));
                    $('#editUsername').val($(this).data('username'));
                    $('#editName').val($(this).data('name'));
                    $('#editEmail').val($(this).data('email'));
                    $('#editIdentification').val($(this).data('identification'));
                    $('#editDepartment').val($(this).data('department'));
                    $('#editAdmin').prop('checked', $(this).data('admin') == 1);
                });
    
                // Handle edit alcohol button click
                $('.edit-alcohol-btn').on('click', function() {
                    $('#editAlcoholId').val($(this).data('alcohol-id'));
                    $('#editRfid').val($(this).data('rfid'));
                    $('#editAlcoholValue').val($(this).data('alcoholvalue'));
                    $('#editDepartment').val($(this).data('department'));
    
                   
                });
                function updateAlcoholTable() {
                $.ajax({
                    url: '../get_alcohol.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            alcoholTable.clear(); 
                            $('.modal-container').empty(); // Clear previous modals 

                            // Loop through each row of data received
                            $.each(data, function(index, row) {
                                var rowClass = row.alcoholvalue > 20 ? 'high-alcohol' : 'low-alcohol';
                                var modalId = 'modalDetails' + row.id; 

                                // Create the Modal HTML 
                                var modalHtml = `
                                    <div class="modal fade" id="${modalId}" tabindex="-1" aria-labelledby="modalDetailsLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalDetailsLabel">รายละเอียดข้อมูล</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>ID:</strong> ${row.id}</p>
                                                    <p><strong>RFID:</strong> ${row.rfid}</p>
                                                    <p><strong>Alcohol Value:</strong> ${row.alcoholvalue}</p>
                                                    <p><strong>Name:</strong> ${row.name}</p>
                                                    <p><strong>Identification:</strong> ${row.identification}</p>
                                                    <p><strong>Department:</strong> ${row.department}</p>
                                                    <p><strong>Timestamp:</strong> ${row.updated_at}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`;

                                // Append modal to the container
                                $('.modal-container').append(modalHtml); 

                                // Add a new row to the alcoholTable (without modal HTML)
                                alcoholTable.row.add([
                                    row.id,
                                    row.rfid,
                                    row.alcoholvalue,
                                    row.name,
                                    row.identification,
                                    row.department,
                                    row.updated_at,
                                    `<button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#${modalId}">
                                        <i class="fas fa-info-circle"></i> รายละเอียด
                                    </button>`
                                ]).draw(); // Redraw the table 
                            });
                        }
                    },
                        error: function() {
                            console.error('Error fetching alcohol data.');
                        }
                    });
                }
                
        updateAlcoholTable();
        setInterval(updateAlcoholTable, 5000); // อัปเดตข้อมูลทุก ๆ 5 วินาที
    });
    </script>
    </body>
    </html>