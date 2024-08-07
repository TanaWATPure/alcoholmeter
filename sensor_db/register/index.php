<?php
session_start();
include_once "../db.php";

// Check if form is submitted
if (isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $identification = $_POST['identification'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $rfid = $_POST['rfid']; 

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if username, email, or RFID already exist
    $check_duplicate = "SELECT * FROM users WHERE username='$username' OR email='$email' OR rfid='$rfid'";
    $result = $conn->query($check_duplicate);

    if ($result->num_rows > 0) {
        // Fetch the existing data to provide a specific error message
        $existingData = $result->fetch_assoc();

        if ($existingData['username'] == $username) {
            $_SESSION['register_message'] = "Please change Username.";
        } elseif ($existingData['email'] == $email) {
            $_SESSION['register_message'] = "Please change Email.";
        } elseif ($existingData['rfid'] == $rfid) {
            $_SESSION['register_message'] = "Please change Rfid.";
        } 
    } else {
        // If no duplicates are found, proceed with registration
        $sql = "INSERT INTO users (username, password, name, identification, department, email, rfid) 
                VALUES ('$username', '$hashed_password', '$name', '$identification', '$department', '$email', '$rfid')";


        if ($conn->query($sql) === TRUE) {
            $_SESSION['register_message'] = "success-Registration successful!"; 
        } else {
            $_SESSION['register_message'] = "error-Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="https://i.ibb.co/stFW6JB/removebg-preview.png" type="image/png">
   
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: url('https://i.ibb.co/VQmtgjh/6845078.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-box {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 10px; /* ลด padding จาก 40px เหลือ 30px */
            max-width: 350px; /* ลด max-width จาก 450px เหลือ 350px */
            width: 100%;
        }

        .register-box h3 {
            text-align: center;
            color: #59238F;
            margin-bottom: 20px; /* ลด margin-bottom จาก 30px เหลือ 20px */
        }

        .form-group {
            margin-bottom: 15px; /* ลด margin-bottom จาก 20px เหลือ 15px */
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da; 
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary {
            background-color: #59238F;
            border-color: #59238F;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 600;
            width: 100%; 
        }

        .btn-primary:hover {
            background-color: #4d1f7d;
            border-color: #4d1f7d;
        }

        .register-box a {
            color: #59238F;
            text-decoration: none;
            font-weight: 600;
        }

        .register-box a:hover {
            color: #4d1f7d;
        }

        .register-message {
            margin-top: 15px;
            text-align: center;

        }
        .alert-safe {
         background-color: #28a745; /* Green */
            color: white;
        }

        .alert-success {
            background-color: #28a745; /* Green */
            color: white;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h3><i class="fas fa-user-plus"></i> SIGN UP</h3>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="rfid"><i class="fas fa-id-card"></i> RFID:</label>
                <input type="text" class="form-control" id="rfid" name="rfid" placeholder="Enter RFID" required>
            </div>
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username:</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Name:</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <label for="identification"><i class="fas fa-id-card"></i> Identification:</label>
                <input type="text" class="form-control" id="identification" name="identification" placeholder="Enter your identification" required>
            </div>
            <div class="form-group">
                <label for="department"><i class="fas fa-users"></i> Department:</label>
                <input type="text" class="form-control" id="department" name="department" placeholder="Enter your department" required>
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn btn-primary">SIGN UP</button>
        </form>
        <div class="text-center mt-3">
            <a href="../login">Already have an account? Login</a>
        </div>
        <?php if (isset($_SESSION['register_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['register_message'] === "Registration successful!" ? 'success' : 'safe'; ?> register-message" role="alert">
                <?php echo $_SESSION['register_message']; ?>
            </div>
            <?php unset($_SESSION['register_message']); ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <script>
    <?php if(isset($_SESSION['register_message'])): ?>
            <?php 
                // Split the message into type and content
                list($messageType, $messageContent) = explode('-', $_SESSION['register_message'], 2); 
            ?>

            // Display SweetAlert based on message type
            Swal.fire({
                icon: '<?php echo $messageType; ?>', // 'success' or 'error'
                title: '<?php echo $messageContent; ?>',
                showConfirmButton: false,
                timer: 1500 // Adjust timer as needed
            }).then(function() {
                <?php if($messageType === 'success'): ?>
                    window.location.href = '../login.php'; // Redirect on success
                <?php endif; ?>
            });

            <?php unset($_SESSION['register_message']); ?> 
        <?php endif; ?>
    </script>
</body>
</html>
