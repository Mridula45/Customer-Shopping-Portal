<?php
session_start();
$conn = new mysqli("localhost", "root", "", "mydb");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
if (isset($_POST['login'])) {
$cust_id = intval($_POST['cust_id']);
$cust_name = $conn->real_escape_string($_POST['cust_name']);
$chk = $conn->query("SELECT * FROM customer WHERE cust_id=$cust_id AND
cust_name='$cust_name'");
if ($chk && $chk->num_rows == 1) {
$_SESSION['cust_id'] = $cust_id;
header("Location: portal.php");
  exit();
} else {
$msg = "Invalid ID or name!";
}
}
elseif (isset($_POST['signup'])) {
$cust_name = $conn->real_escape_string($_POST['cust_name']);
$phone_no = $conn->real_escape_string($_POST['phone_no']);
$address = $conn->real_escape_string($_POST['address']);
$ins = "INSERT INTO customer (cust_name, phone_no, address) VALUES ('$cust_name',
'$phone_no', '$address')";
if ($conn->query($ins)) {
$cust_id = $conn->insert_id;
$_SESSION['cust_id'] = $cust_id;
$msg = "Signup successful! Logging in...";
header("Refresh:2; url=portal.php");
} else {
$msg = "Signup error: " . $conn->error;
}
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Best Shopping - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">
<style> body { background: #f8fafc; } .page { max-width:450px; margin:auto; margintop:70px;} </style>
</head>
<body>
<div class="page bg-white p-4 shadow rounded">
<h2 class="mb-4 text-center">Customer Login</h2>
<?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
<form method="post" class="mb-3">
<input type="hidden" name="login" value="1">
<div class="mb-2">
<label>Customer ID</label>
<input class="form-control" type="number" name="cust_id" required>
</div>
<div class="mb-2">
<label>Name</label>
<input class="form-control" type="text" name="cust_name" required>
</div>
<button type="submit" class="btn btn-primary w-100">Login</button>
</form>
<hr>
<h4 class="mb-3">New Customer? Sign Up</h4>
<form method="post">
<input type="hidden" name="signup" value="1">
<div class="mb-2">
<label>Name</label>

<input class="form-control" type="text" name="cust_name" required>
</div>
<div class="mb-2">
<label>Phone No</label>
<input class="form-control" type="text" name="phone_no" required>
</div>
<div class="mb-2">
<label>Address</label>
<input class="form-control" type="text" name="address" required>
</div>
<button type="submit" class="btn btn-success w-100">Sign Up</button>
</form>
</div>
</body>
</html>
<?php $conn->close(); ?>
  
