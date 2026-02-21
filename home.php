<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['username'])){
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];

$con = new mysqli("localhost", "root", "", "expenseTracker");
if($con->connect_error){
    die("Connection failed: " . $con->connect_error);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $category = $_POST['category'] ?? '';
    $expense_name = $_POST['name'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $date = $_POST['date'] ?? '';
    $today = date('Y-m-d');

    $sql = "INSERT INTO expenseTable (username, category, expense_name, amount, date, expenseAddedDate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssss", $username, $category, $expense_name, $amount, $date, $today);
    $stmt->execute();
    $message = "Expense details added successfully!";
    echo "<script type='text/javascript'>";
    echo "alert('$message');";
    echo "</script>";	
    $stmt->close();
}

$currentMonth = date("Y-m");
$sql = "SELECT category, expense_name, amount, date, expenseAddedDate FROM expenseTable WHERE username = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $username, $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expense Tracker</title>
  <link rel="stylesheet" href="home.css">
  <link rel="icon" type="image/x-icon" href="expenseTracker.svg">
  <style>
  	.user-info{
  	    	position: absolute;
  		top: 10px;
  		right: 20px;
  		background-color: #666;
  		color: white;
  		padding: 0.5rem 1rem;
  		border-radius: 4px;
  		font-size: 0.9rem;
  		z-index: 1000;
	}
	.logout{
		float: right;
  		top: 10px;
  		right: 20px;
  		background-color: #666;
  		color: white;
  		padding: 0.5rem 1rem;
  		border-radius: 4px;
  		font-size: 0.9rem;
  		z-index: 1000;
  		border: none;
  		cursor: pointer;
	}
</style>
</head>
<body>
  <header>
    <h1>Expense Tracker</h1>
    <div class="user-info"><?php echo "Logged as ". $username ?></div>
    <a href="login.html"><button class="logout">Logout</button></a>
    <nav>
      <a href="home.php" class="active">Home</a>
      <a href="date.php">Date wise expenses</a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div class="intro">
        <h2>Categories</h2>
        <p>Select the expenses that you want to know the total expenses done by you.</p>
      </div>
    </section>

    <section class="quick-links">
      <a class="card"><h3>Food & Drinks</h3></a>
      <a class="card"><h3>Groceries</h3></a>
      <a class="card"><h3>Shopping</h3></a>
      <a class="card"><h3>Transport</h3></a>
      <a class="card"><h3>Entertainment</h3></a>
      <a class="card"><h3>Utilities</h3></a>
      <a class="card"><h3>Health & Fitness</h3></a>
      <a class="card"><h3>Home</h3></a>
    </section><br><br>

    <h2 style="text-align:center;">Your Expenses for <?php echo date("F Y"); ?></h2>
    <table class="dataTable">
      <tr>
        <th>Category</th>
        <th>Expense Name</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Expense Added Date</th>
      </tr>
      <?php
      $currentDate = "";
      while ($row = $result->fetch_assoc()) {
          if ($currentDate != $row['date']) {
              $currentDate = $row['date'];
              
          }
          echo "<tr>
                  <td>{$row['category']}</td>
                  <td>{$row['expense_name']}</td>
                  <td>{$row['amount']}</td>
                  <td>{$row['date']}</td>
                  <td>{$row['expenseAddedDate']}</td>
                </tr>";
      }
      ?>
    </table>
    <?php
    	$sql = "SELECT SUM(amount) AS total FROM expenseTable WHERE username = ? AND DATE_FORMAT(date, '%Y-%m') = ?";
	$stmt = $con->prepare($sql);
	$stmt->bind_param("ss", $username, $currentMonth);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$total = $row['total'] ?? 0;

	echo "<h3 style='text-align:center;'>Total for ".date("F Y").": ₹".$total."</h3>";
   ?>
  </main>

  <div id="expenseModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal()">&times;</span>
      <h2>Expense details</h2>
      <form onsubmit="return validateForm()" action="home.php" method="post">
        <input type="hidden" id="selectedCategory" name="category">
        <input type="text" name="name" placeholder="Enter the name of expense" required>
        <input type="number" name="amount" id="amount" placeholder="Enter the full amount" required>
        <span id="aError" class="error"></span>
        <input type="date" name="date" id="date" required>
        <span id="dError" class="error"></span>
        <input type="submit" value="submit">
      </form>
    </div>
  </div>

  <script src="home.js"></script>
</body>
</html>

