<?php
session_start();

$host = "localhost";
$dbname = "myblog";
$dbUser = "root";
$dbPass = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Sanitize and get POST data
$usernameEmail = trim($_POST['usernameEmail'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (!$usernameEmail || !$password || !$role) {
    die("Please fill all fields. <a href='login.html'>Go back</a>");
}

// Fetch user by username/email and role
$stmt = $conn->prepare("SELECT * FROM users WHERE (username = :ue OR email = :ue) AND role = :role LIMIT 1");
$stmt->execute([
    'ue' => $usernameEmail,
    'role' => $role
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found or role mismatch. <a href='login.html'>Try again</a>");
}

// Verify password
if (!password_verify($password, $user['password'])) {
    die("Incorrect password. <a href='login.html'>Try again</a>");
}

// Set session variables
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['fullname'] = $user['fullname'];

// Redirect based on role
if ($user['role'] === 'admin') {
    header("Location: admin/dashboard.html");
} else {
    header("Location: index.html");
}
exit;
