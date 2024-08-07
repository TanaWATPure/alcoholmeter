<?php
session_start();
include_once "../db.php";


if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // ตรวจสอบรหัสผ่าน
            if (password_verify($password, $row['password'])) {
                // เก็บข้อมูลผู้ใช้ในเซสชัน
                $_SESSION['username'] = $username;

                // ตรวจสอบว่าเป็น admin หรือไม่
                if ($row['admin'] == 1) {
                    // ส่งกลับ response สำหรับ AJAX 
                    echo json_encode(array("success" => true, "redirect" => "../admin"));
                    exit;
                } else {
                    // ส่งกลับ response สำหรับ AJAX 
                    echo json_encode(array("success" => true, "redirect" => "../display"));
                    exit;
                }
            } else {
                // ส่งกลับ response สำหรับ AJAX 
                echo json_encode(array("success" => false, "message" => "Invalid username or password!"));
                exit;
            }
        } else {
            // ส่งกลับ response สำหรับ AJAX 
            echo json_encode(array("success" => false, "message" => "Invalid username or password!"));
            exit;
        }
       
    } else {
        // ส่งกลับ response สำหรับ AJAX 
        echo json_encode(array("success" => false, "message" => "Database error: " . $conn->error));
        exit;
    }
   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="https://i.ibb.co/stFW6JB/removebg-preview.png" type="image/png">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: url(https://i.ibb.co/VQmtgjh/6845078.png) no-repeat center center fixed;
            height: 100vh;
            font-family: sans-serif;
            background-size: cover;
            overflow: hidden;
        }
        @media screen and (max-width: 600px) {
            body {
                background-size: cover;
            }
        }
        .loginBox {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            min-height: 200px;
            background: #000000;
            border-radius: 10px;
            padding: 40px;
            box-sizing: border-box;
        }
        .user {
            margin: 0 auto;
            display: block;
            margin-bottom: 20px;
            border-radius: 50%;
        }
        h3 {
            margin: 0;
            padding: 0 0 20px;
            color: #59238F;
            text-align: center;
        }
        .loginBox input {
            width: 100%;
            margin-bottom: 20px;
        }
        .loginBox input[type="text"], .loginBox input[type="password"] {
            border: none;
            border-bottom: 2px solid #262626;
            outline: none;
            height: 40px;
            color: #fff;
            background: transparent;
            font-size: 16px;
            padding-left: 20px;
            box-sizing: border-box;
        }
        .loginBox input[type="text"]:hover, .loginBox input[type="password"]:hover {
            color: #42F3FA;
            border: 1px solid #42F3FA;
            box-shadow: 0 0 5px rgba(0, 255, 0, .3), 0 0 10px rgba(0, 255, 0, .2), 0 0 15px rgba(0, 255, 0, .1), 0 2px 0 black;
        }
        .loginBox input[type="text"]:focus, .loginBox input[type="password"]:focus {
            border-bottom: 2px solid #42F3FA;
        }
        .loginBox input[type="submit"] {
            border: none;
            outline: none;
            height: 40px;
            font-size: 16px;
            background: #59238F;
            color: #fff;
            border-radius: 20px;
            cursor: pointer;
        }
        .loginBox a {
            color: #262626;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        a:hover {
            color: #00ffff;
        }
        .loginBox p {
            text-align: center;
            color: red;
        }
        .login-success {
            animation: fadeOut 2s ease-in-out;
        }
        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="loginBox">
        <img class="user" src="https://i.ibb.co/0j8pzqt/327747162-3358096534404258-4873526037921598716-n.jpg" height="100px" width="100px">
        <h3>LOGIN</h3>
        <form id="loginForm" method="post">
            <div class="inputBox">
                <input id="uname" type="text" name="username" placeholder="Username" required>
                <input id="pass" type="password" name="password" placeholder="Password" required>
            </div>
            <input type="submit" value="LOGIN">
        </form>
        <a href="../resetpassword">Forget Password<br></a>
        <div class="text-center">
            <a href="../register" style="color: #59238F;">Sign-Up</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();

            var username = document.getElementById("uname").value;
            var password = document.getElementById("pass").value;

            if (username === "" || password === "") {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please fill out both fields!',
                });
                return;
            }

            // ใช้ AJAX ส่งข้อมูลไปยัง server
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);

                    if (response.success) {
                        // Login สำเร็จ
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful!',
                            showConfirmButton: false,
                            timer: 1500 // แสดง SweetAlert เป็นเวลา 1.5 วินาที
                        }).then(function() {
                            // เปลี่ยนเส้นทางหลังจาก SweetAlert หายไป
                            window.location.href = response.redirect;
                        });
                    } else {
                        // Login ล้มเหลว
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed!',
                            text: response.message,
                        });
                    }
                }
            };
            xhr.send("username=" + username + "&password=" + password);
        });
    </script>
</body>
</html>