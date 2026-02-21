<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['username'])){
    echo "Unauthorized";
    exit();
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if(!$data){
    echo "Invalid data received";
    exit();
}

$required_fields = ['category', 'expense_name', 'amount', 'date', 'expenseAddedDate'];
foreach($required_fields as $field){
    if(!isset($data[$field])){
        echo "Missing field: $field";
        exit();
    }
}

$con = new mysqli("localhost", "root", "", "expenseTracker");
if($con->connect_error){
    die("Connection failed: " . $con->connect_error);
}

$session_username = $_SESSION['username'];

$sql = "DELETE FROM expenseTable 
        WHERE username = ? 
        AND category = ? 
        AND expense_name = ? 
        AND amount = ? 
        AND date = ? 
        AND expenseAddedDate = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param(
    "ssssss", 
    $session_username,
    $data['category'],
    $data['expense_name'],
    $data['amount'],
    $data['date'],
    $data['expenseAddedDate']
);

if($stmt->execute()){
    if($stmt->affected_rows > 0){
        echo "Expense deleted successfully!";
    } else {
        echo "No expense found to delete. Please check if the expense exists.";
    }
} else {
    echo "Delete failed: " . $stmt->error;
}

$stmt->close();
$con->close();
?>
