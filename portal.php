<?php
session_start();
if (!isset($_SESSION['cust_id'])) { header('Location: login.php'); exit(); }
$conn = new mysqli("localhost", "root", "", "mydb");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
$msg = "";
// Order placement - manual ord_id increment if needed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_now'])) {
$prod_id = intval($_POST['prod_id']);
$cust_id = $_SESSION['cust_id'];
$date = date('Y-m-d');
// MANUAL ORD_ID: Get max(old) and increment
$res = $conn->query("SELECT MAX(ord_id) AS max_ord_id FROM orders");
$row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : ['max_ord_id'=>0];
$order_id = intval($row['max_ord_id']) + 1;
// Get product info
$pq = $conn->query("SELECT price, date as prod_date FROM product WHERE
prod_id=$prod_id");
$product = ($pq && $pq->num_rows > 0) ? $pq->fetch_assoc() : null;
if ($product) {
$amount = $product['price'];
$prod_date = $product['prod_date'];
$confirm_cutoff = "2025-11-06";
$status = ($prod_date < $confirm_cutoff) ? 'Confirmed' : 'Pending';
$ins = "INSERT INTO orders (ord_id, cust_id, ord_date, total_amount, status) VALUES
($order_id, $cust_id, '$date', '$amount', '$status')";
if ($conn->query($ins)) {
$conn->query("INSERT INTO product_has_orders (orders_ord_id, prod_id, cust_id)
VALUES ($order_id, $prod_id, $cust_id)");
$msg = "Order placed!";
  } else {
$msg = "Order insert error: " . $conn->error;
}
} else {
$msg = "Error: Product not found!";
}
}
$cust_id = $_SESSION['cust_id'];
$res_cust = $conn->query("SELECT * FROM customer WHERE cust_id=$cust_id");
$cust = $res_cust->fetch_assoc();
$products = $conn->query("SELECT * FROM product");
$orders = $conn->query("
SELECT o.ord_id, o.ord_date, o.total_amount, o.status, p.prod_name, c.cust_id, c.cust_name
FROM orders o
INNER JOIN product_has_orders pho ON o.ord_id = pho.orders_ord_id
INNER JOIN product p ON p.prod_id = pho.prod_id
INNER JOIN customer c ON c.cust_id = o.cust_id
WHERE o.cust_id = $cust_id
ORDER BY o.ord_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Best Shopping Portal</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
rel="stylesheet">
<style>
body { background: linear-gradient(120deg,#89f7fe,#66a6ff); min-height:100vh;}
.custinfo {margin-bottom:25px;}
.main-card { background: #fff; border-radius: 1.5rem; box-shadow: 0 0 2rem #d0e6fb;
margin:auto; max-width:900px;padding:2rem;}
.btn-warning { font-weight:bold;}
.table thead {background: #003366!important; color:#fff;}
.table td,.table th{vertical-align:middle!important;}
</style>
</head>
<body>
<div class="container py-5">
<div class="main-card p-4">
<h2 class="mb-4 text-center text-primary" style="font-weight:bold;">Mridu Shopping
Portal</h2>
<div class="custinfo card p-3 mb-4" style="background: #ebf6ff;">
<h5 class="fw-bold text-success mb-2">Logged-in Customer</h5>
<div><b>ID:</b> <?= $cust['cust_id'] ?></div>
<div><b>Name:</b> <?= htmlspecialchars($cust['cust_name']) ?></div>
<div><b>Phone:</b> <?= htmlspecialchars($cust['phone_no']) ?></div>
<div><b>Address:</b> <?= htmlspecialchars($cust['address']) ?></div>
</div>
<?php if ($msg) echo "<div class='alert alert-info'>$msg</div>"; ?>
<h4 class="text-info">Product List</h4>
<table class="table table-striped shadow mb-5 bg-white">
<thead>
<tr>
<th>Product ID</th>
<th>Name</th>
<th>Price</th>
<th>Date</th>
<th>Order Now</th>
</tr>
</thead>
<tbody>
<?php while($row = $products->fetch_assoc()) { ?>
<tr>
<td><?= $row['prod_id'] ?></td>
<td><?= htmlspecialchars($row['prod_name']) ?></td>
<td><?= $row['price'] ?></td>
<td><?= $row['date'] ?></td>
<td>
<form method="post" style="display:inline;">
<input type="hidden" name="order_now" value="1">
<input type="hidden" name="prod_id" value="<?= $row['prod_id'] ?>">
<button type="submit" class="btn btn-warning btn-sm">Order Now</button>
</form>
</td>
</tr>
                                              <?php } ?>
</tbody>
</table>
<h4 class="text-info">My Orders</h4>
<table class="table table-striped shadow bg-white">
<thead>
<tr>
<th>Order ID</th>
<th>Customer ID</th>
<th>Customer Name</th>
<th>Product Name</th>
<th>Amount</th>
<th>Date</th>
<th>Status</th>
</tr>
</thead>
<tbody>
<?php while($order = $orders->fetch_assoc()) { ?>
<tr>
<td><?= $order['ord_id'] ?></td>
<td><?= $order['cust_id'] ?></td>
<td><?= htmlspecialchars($order['cust_name']) ?></td>
<td><?= htmlspecialchars($order['prod_name']) ?></td>
<td><?= $order['total_amount'] ?></td>
<td><?= $order['ord_date'] ?></td>
                                              <td><?= $order['status'] ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<a href="login.php" class="btn btn-secondary mt-3">Logout</a>
</div>
</div>
</body>
</html>
<?php $conn->close(); ?>
