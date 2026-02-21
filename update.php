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

$required_fields = [
    'original_username', 'original_category', 'original_expense_name', 
    'original_amount', 'original_date', 'original_expenseAddedDate',
    'category', 'expense_name', 'amount', 'date'
];

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

$sql = "UPDATE expenseTable SET category = ?, expense_name = ?, amount = ?, date = ? WHERE username = ? AND category = ? AND expense_name = ? AND amount = ? AND date = ? AND expenseAddedDate = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param(
    "ssssssssss", 
    $data['category'],
    $data['expense_name'],
    $data['amount'],
    $data['date'],
    $session_username,
    $data['original_category'],
    $data['original_expense_name'],
    $data['original_amount'],
    $data['original_date'],
    $data['original_expenseAddedDate']
);

if($stmt->execute()){
    if($stmt->affected_rows > 0){
        echo "Expense updated successfully!";
    } else {
        echo "No changes made or record not found.";
    }
} else {
    echo "Update failed: " . $stmt->error;
}

$stmt->close();
$con->close();
?>
