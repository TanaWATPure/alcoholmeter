<?php
session_start();
include_once "../db.php";

// Check if form is submitted
if (isset($_POST["reset"])) {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $_SESSION['reset_message'] = "Password and Confirm Password do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Update the password in the database using prepared statement
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE username=?");
        $stmt->bind_param("ss", $hashed_password, $username);

        if ($stmt->execute()) {
            $_SESSION['reset_message'] = "Password reset successfully.";
        } else {
            $_SESSION['reset_message'] = "Error updating password: " . $stmt->error;
        }

        // Close statement 
        $stmt->close();
    }

    header("Location: ../resetpassword");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="https://i.ibb.co/stFW6JB/removebg-preview.png" type="image/png">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: url('https://i.ibb.co/VQmtgjh/6845078.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }

        .card {
            background-color: rgba(255, 255, 255, 0.8); 
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 400px;
            width: 100%;
        }

        .card-header {
            background-color: #59238F;
            color: white;
            border-radius: 10px 10px 0 0;
            padding: 15px;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #59238F;
            border-color: #59238F;
            border-radius: 5px;
            padding: 10px 20px; 
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #4d1f7d;
            border-color: #4d1f7d;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 600;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .alert {
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Reset Password</div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['reset_message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['reset_message'] === "Password reset successfully." ? 'success' : 'info'; ?>" role="alert">
                            <?php echo $_SESSION['reset_message']; ?>
                        </div>
                        <?php unset($_SESSION['reset_message']); ?>
                        <?php endif; ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary" name="reset">Reset Password</button>
                            <a href="../login" class="btn btn-secondary">Back to Login</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
