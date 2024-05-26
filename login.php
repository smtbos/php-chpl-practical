<?php
include '_config.php';
guest();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get the form data
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Escape the form data
        $email = mysqli_real_escape_string($conn, $email);

        // Check if the email exists
        $sql = "SELECT * FROM admin WHERE admin_email_id = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 0) {
            throw new Exception('Invalid email or password.');
        }

        // Get the admin
        $admin = mysqli_fetch_assoc($result);

        // Check if the password is correct
        if ($admin['admin_password'] !== $password) {
            throw new Exception('Invalid email or password.');
        }

        // Set the session
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['admin_name'];
        $_SESSION['admin_email_id'] = $admin['admin_email_id'];

        // Return the success response
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful.'
        ]);
        exit();
    } catch (\Throwable $th) {
        // Return the error response 403
        http_response_code(403);
        echo json_encode([
            'status' => 'error',
            'message' => $th->getMessage()
        ]);
        exit();
    }
}
?>
<?php include '_header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-lg-4 col-md-6 offset-md-3 offset-lg-4 mt-5">
            <h1 class="text-center">Login</h1>
            <form id="login" action="login.php" method="post">
                <div class="form-group pb-3">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group pb-3">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</div>
<?php
function scripts()
{
?>
    <script>
        $(document).ready(function() {
            $('#login').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'login.php',
                    method: 'POST',
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            window.location.href = 'index.php';
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
<?php
}
?>
<?php include '_footer.php'; ?>