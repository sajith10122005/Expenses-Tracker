<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$con = new mysqli("localhost", "root", "", "expenseTracker");
if($con->connect_error){
    die("Connection failed: " . $con->connect_error);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$tableCheck = $con->query("SELECT COUNT(*) as total FROM register");
$row = $tableCheck->fetch_assoc();

if($row['total'] == 0){
    die("No users found in database. Please register first.");
}

$sql = "SELECT * FROM register WHERE username = ? AND password = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $username, $password);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0){
    $_SESSION['username'] = $username;
    header("Location: home.php");
    exit();
} 
else{
    echo "Invalid username or password!";
}

$stmt->close();
$con->close();
?>

