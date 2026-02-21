<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$username = $_SESSION['username'];

$con = new mysqli("localhost", "root", "", "expenseTracker");
if($con->connect_error){
    die("Connection failed: " . $con->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['form_type'] ?? '') === 'add_expense'){
    $category = $_POST['category'] ?? '';
    $expense_name = $_POST['name'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $date = $_POST['date'] ?? '';
    $today = date('Y-m-d');

    $sql = "INSERT INTO expenseTable (username, category, expense_name, amount, date, expenseAddedDate) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ssssss", $username, $category, $expense_name, $amount, $date, $today);
    $stmt->execute();
    echo "<script>alert('Expense details added successfully!');</script>";
    $stmt->close();
}

$currentMonth = date("Y-m");
$sql = "SELECT username, category, expense_name, amount, date, expenseAddedDate FROM expenseTable WHERE username = ? AND DATE_FORMAT(date, '%Y-%m') = ? ORDER BY date ASC";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $username, $currentMonth);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Expense Tracker</title>
  <link rel="stylesheet" href="date.css">
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
    .update, .delete {
      width: 48%;
      padding: 5px;
      background: grey;
      border: none;
      color: white;
      font-size: 16px;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 5px;
    }
    .update{ 
      float: left; margin-right: 4%;
    }
    
    .delete{
      float: right;
     }
     
    input[type="text"] {
      width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>
<body>
  <header>
    <h1>Expense Tracker</h1>
    <div class="user-info"><?php echo "Logged as ". $username ?></div>
    <a href="login.html"><button class="logout">Logout</button></a>
    <nav>
      <a href="home.php">Home</a>
      <a href="date.php" class="active">Date wise expenses</a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div class="intro">
        <h2>Categories</h2>
        <p>Select the date to know your expenses.</p>
      </div>
    </section>

    <section class="quick-links">
      <button class="card" onclick="showSection('daily')"><h3>Daily</h3></button>
      <button class="card" onclick="showSection('monthly')"><h3>Monthly</h3></button>
      <button class="card" onclick="showSection('calendar')"><h3>Calendar</h3></button>
    </section><br><br>

    <section id="daily" class="section active">
      <h2 style="text-align:center;">Your Expenses for <?php echo date("F Y"); ?></h2>
      <table class="dataTable">
        <tr>
          <th>Category</th>
          <th>Expense Name</th>
          <th>Amount</th>
          <th>Date</th>
          <th>Expense Added Date</th>
          <th>Modify Expense</th>
        </tr>
        <?php
        while($row = $result->fetch_assoc()){
            echo "<tr data-username='{$row['username']}' 
                       data-category='{$row['category']}' 
                       data-expense_name='{$row['expense_name']}' 
                       data-amount='{$row['amount']}' 
                       data-date='{$row['date']}' 
                       data-expenseAddedDate='{$row['expenseAddedDate']}'>
                    <td>{$row['category']}</td>
                    <td>{$row['expense_name']}</td>
                    <td>₹{$row['amount']}</td>
                    <td>{$row['date']}</td>
                    <td>{$row['expenseAddedDate']}</td>
                    <td>
                      <button class='update'>update</button>
                      <button class='delete'>delete</button>
                    </td>
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

      echo "<h3 style='text-align:center;'>Total for " . date("F Y") . ": ₹" . $total . "</h3>";
      ?>
    </section>
    <section id="monthly" class="section">
      <h2 style="text-align:center;">Your Monthly Expenses Summary</h2>
      <table class="dataTable">
        <tr>
          <th>Month-Year</th>
          <th>Category</th>
          <th>Total Amount (₹)</th>
        </tr>
        <?php
        $sql = "SELECT DATE_FORMAT(date, '%M-%Y') AS monthYear, category, SUM(amount) AS totalAmount FROM expenseTable WHERE username = ? GROUP BY monthYear, category ORDER BY STR_TO_DATE(monthYear, '%M-%Y') DESC";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultMonthly = $stmt->get_result();

        while($row = $resultMonthly->fetch_assoc()){
            echo "<tr>
                    <td>{$row['monthYear']}</td>
                    <td>{$row['category']}</td>
                    <td>₹{$row['totalAmount']}</td>
                  </tr>";
        }
        ?>
      </table>
    </section>

    <section id="calendar" class="section">
      <center><form method="POST" class="container">
        <h2 style="text-align:center;">Select date</h2>
        <input type="date" name="selectDate" required>
        <input type="hidden" name="form_type" value="filter_date">
        <input type="submit" value="Filter">
      </form></center><br><br>

      <?php
      $selectDate = $_POST['selectDate'] ?? '';
      $exactDate = $selectDate ? date('Y-m-d', strtotime($selectDate)) : '';
      $calendarResult = null;

      if($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['form_type'] ?? '') === 'filter_date' && $exactDate){
          $sql = "SELECT username, category, expense_name, amount, date, expenseAddedDate FROM expenseTable WHERE username = ? AND date = ?";
          $stmt = $con->prepare($sql);
          $stmt->bind_param("ss", $username, $exactDate);
          $stmt->execute();
          $calendarResult = $stmt->get_result();
      }
      ?>

      <table class="dataTable">
        <tr>
          <th>Category</th>
          <th>Expense Name</th>
          <th>Amount</th>
          <th>Date</th>
          <th>Expense Added Date</th>
          <th>Modify Expense</th>
        </tr>
        <?php
        if(!empty($calendarResult)){
            while($row = $calendarResult->fetch_assoc()){
                echo "<tr data-username='{$row['username']}' 
                           data-category='{$row['category']}' 
                           data-expense_name='{$row['expense_name']}' 
                           data-amount='{$row['amount']}' 
                           data-date='{$row['date']}' 
                           data-expenseAddedDate='{$row['expenseAddedDate']}'>
                        <td>{$row['category']}</td>
                        <td>{$row['expense_name']}</td>
                        <td>₹{$row['amount']}</td>
                        <td>{$row['date']}</td>
                        <td>{$row['expenseAddedDate']}</td>
                        <td>
                          <button class='update'>update</button>
                          <button class='delete'>delete</button>
                        </td>
                      </tr>";
            }
        }
        ?>
      </table>

      <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST['form_type'] ?? '') === 'filter_date' && $exactDate) {
          $sql = "SELECT SUM(amount) AS total FROM expenseTable WHERE username = ? AND date = ?";
          $stmt = $con->prepare($sql);
          $stmt->bind_param("ss", $username, $exactDate);
          $stmt->execute();
          $resultTotal = $stmt->get_result();
          $row = $resultTotal->fetch_assoc();
          $total = $row['total'] ?? 0;

          echo "<h3 style='text-align:center;'>Total for " . date("d M Y", strtotime($selectDate)) . ": ₹" . $total . "</h3>";
      }
      ?>
    </section>

  </main>

  <script src="date.js"></script>
</body>
</html>
