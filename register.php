<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = new mysqli("localhost", "root", "", "expenseTracker");
if($con->connect_error){
    die("Connection failed: " . $con->connect_error);
}

$username = $_POST['username'] ?? '';
$email    = $_POST['email'] ?? '';
$mobileNo = $_POST['mobile'] ?? '';
$password = $_POST['password'] ?? '';

$sql = "SELECT username FROM register WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0){
    echo "<script>alert('Email is already registered. Please use a different one.');
    window.location.href = 'register.html';
    </script>";

}
else{
	$sql1 = "INSERT INTO register (username, email, mobile, password) VALUES ('$username', '$email', '$mobileNo', '$password')";

	if($con->query($sql1) === TRUE){
	    echo "Registered successfully";
	}
	else{
	    echo "Error: " . $con->error;
	}
}

$con->close();
?>

