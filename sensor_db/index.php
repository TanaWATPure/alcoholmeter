<?php
session_start();
include_once "../db.php";

// เช็คว่ามี session ชื่อ username หรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: login.php"); // ถ้าไม่มีให้ Redirect ไปยังหน้า login.php
    exit();
}

// เชื่อมต่อกับฐานข้อมูล

// สร้างตัวแปรสำหรับเก็บข้อมูลที่ดึงมา
$data = [];

// ดึงข้อมูลจากตาราง alcohol
$sql = "SELECT alcohol.id, alcohol.rfid, alcohol.alcoholvalue, users.name, users.identification, users.department, alcohol.created_at, alcohol.updated_at
        FROM alcohol
        INNER JOIN users ON alcohol.rfid = users.rfid";

$alcohol_result = $conn->query($sql);

// Fetch users data
$users_sql = "SELECT * FROM users";
$users_result = $conn->query($users_sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Display</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 30px;
        }
        h1 {
            color: #343a40;
            margin-bottom: 20px;
            font-size: 1.8rem;
            text-align: center;
        }
        .high-alcohol {
            background-color: #f8d7da;
        }
        .low-alcohol {
            background-color: #d4edda;
        }
        .input-group {
            margin-bottom: 15px;
            width: 100%;
        }
        #searchInput {
            border-radius: 5px 0 0 5px;
        }
        #searchButton {
            border-radius: 0 5px 5px 0;
        }
        .btn-logout {
            position: relative; /* เปลี่ยนเป็น relative */
            top: 0; /* ลบ top */
            right: 0; /* ลบ right */
            margin-bottom: 10px; /* เพิ่มระยะห่างด้านล่าง */
        }
        #dataTable {
            font-size: 12px; 
        }
        .user-welcome {
            font-size: 14px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .username {
            color: #007bff; 
        }
        /* Responsive Design */
        @media (max-width: 768px) { /* สำหรับ iPad และขนาดเล็กกว่า */
            h1 {
                font-size: 1.5rem;
            }
            .table-responsive {
                overflow-x: auto; /* เพิ่ม scroll แนวนอนถ้าจำเป็น */
            }
            #dataTable {
                font-size: 10px; 
            }
            .modal-dialog {
                max-width: 95%; 
            }
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

    <h1><i class=""></i> Data Alcohol Panel </h1>

    <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="ค้นหา ชื่อ เลขประจำตัว" id="searchInput">
        <button class="btn btn-dark" type="button" id="searchButton">
            <i class="fas fa-search"></i> ค้นหา
        </button>
    </div>

    <div class="table-responsive"></div>
    <table id="alcoholTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">RFID</th>
                    <th scope="col">ALCOHOL VALUE</th>
                    <th scope="col">NAME</th>
                    <th scope="col">IDENTIFICATION</th>
                    <th scope="col">DEPARTMENT</th>
                    <th scope="col">TIMESTAMP</th>
                    <th scope="col">DETAILS</th>
                </tr>
            </thead>
                <tbody>
                <?php while ($row = $alcohol_result->fetch_assoc()): ?>
                    <tr class="<?php echo $row['alcoholvalue'] > 20 ? 'high-alcohol' : 'low-alcohol'; ?>">
                            <td><?php echo htmlspecialchars($row["id"]); ?></td>
                            <td><?php echo htmlspecialchars($row["rfid"]); ?></td>
                            <td><?php echo htmlspecialchars($row["alcoholvalue"]); ?></td>
                            <td><?php echo htmlspecialchars($row["name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["identification"]); ?></td>
                            <td><?php echo htmlspecialchars($row["department"]); ?></td>
                            <td><?php echo htmlspecialchars($row["updated_at"]); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modalDetails<?php echo $row["id"]; ?>">
                                    <i class="fas fa-info-circle"></i> รายละเอียด
                                </button>
                            </td>
                        </tr>

                        <!-- Modal for details -->
                        <div class="modal fade" id="modalDetails<?php echo $row["id"]; ?>" tabindex="-1" aria-labelledby="modalDetailsLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalDetailsLabel">รายละเอียดข้อมูล</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>ID:</strong> <?php echo htmlspecialchars($row["id"]); ?></p>
                                        <p><strong>RFID:</strong> <?php echo htmlspecialchars($row["rfid"]); ?></p>
                                        <p><strong>Alcohol Value:</strong> <?php echo htmlspecialchars($row["alcoholvalue"]); ?></p>
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($row["name"]); ?></p>
                                        <p><strong>Identification:</strong> <?php echo htmlspecialchars($row["identification"]); ?></p>
                                        <p><strong>Department:</strong> <?php echo htmlspecialchars($row["department"]); ?></p>
                                        <p><strong>Timestamp:</strong> <?php echo htmlspecialchars($row["updated_at"]); ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
            
           

            var dataTable = $('#alcoholTable').DataTable({
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
                
           
            // Function to update alcohol table
            function updateAlcoholTable() {
                $.ajax({
                    url: './get_alcohol.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                           alcoholTable.clear(); 

                            $.each(data, function(index, row) {
                                var rowClass = row.alcoholvalue > 20 ? 'high-alcohol' : 'low-alcohol';
                                var modalId = 'modalDetails' + row.id;

                                 // สร้าง Modal HTML
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



