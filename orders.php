<?php
session_start();
if(!isset($_SESSION['username'])) {
    header('location: login.php');
    exit;
}

$msg = '';
$msg_type = '';
$search_html = '';
$all_orders_html = '';

require 'db_config.php';
$con = get_db_connection();

if(isset($_POST['add'])) {
    $ty = trim($_POST["ty"]);
    $na = trim($_POST["na"]);
    $pr = trim($_POST["pr"]);
    $qu = trim($_POST["qu"]);
    $am = trim($_POST["am"]);
    $st = trim($_POST["st"]);
    if($ty==''||$na==''||$pr==''||$qu==''||$am==''||$st=='') {
        $msg = "All fields are required."; $msg_type = 'error';
    } else {
        $query = mysqli_query($con,"INSERT INTO orders(type,name,product,quantity,amount,status) values('$ty','$na','$pr','$qu','$am','$st')");
        if($query) { $msg = "Order added for $na."; $msg_type = 'success'; }
        else { $msg = "Failed: " . mysqli_error($con); $msg_type = 'error'; }
    }
}

if(isset($_POST['search'])) {
    $na = trim($_POST['na']);
    if($na == '') { $msg = "Enter a name to search."; $msg_type = 'error'; }
    else {
        $query = mysqli_query($con,"SELECT * FROM orders WHERE name='$na'");
        $nor = mysqli_num_rows($query);
        if($nor == 0) { $msg = "No records found for \"$na\"."; $msg_type = 'error'; }
        else {
            $search_html = '<table class="res-table"><thead><tr><th>ID</th><th>Type</th><th>Name</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
            while($row = mysqli_fetch_array($query)) {
                $search_html .= '<tr><td>'.htmlspecialchars($row['id']).'</td><td>'.htmlspecialchars($row['type']).'</td><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['product']).'</td><td>'.htmlspecialchars($row['quantity']).'</td><td>Rs.'.htmlspecialchars($row['amount']).'</td><td>'.htmlspecialchars($row['status']).'</td></tr>';
            }
            $search_html .= '</tbody></table>';
        }
    }
}

if(isset($_POST['update'])) {
    $na = trim($_POST['na']);
    $pr = trim($_POST['pr']);
    $qu = trim($_POST['qu']);
    $am = trim($_POST['am']);
    $st = trim($_POST['st']);
    if($na==''||$pr==''||$qu==''||$am==''||$st=='') {
        $msg = "All fields are required."; $msg_type = 'error';
    } else {
        $query = mysqli_query($con,"UPDATE orders SET product='$pr', quantity='$qu', amount='$am', status='$st' WHERE name='$na'");
        if($query) { $msg = "Updated order for: $na"; $msg_type = 'success'; }
        else { $msg = "Update failed: " . mysqli_error($con); $msg_type = 'error'; }
    }
}

if(isset($_POST['delete'])) {
    $na = trim($_POST['na']);
    if($na == '') { $msg = "Enter a name to delete."; $msg_type = 'error'; }
    else {
        $query = mysqli_query($con,"DELETE FROM orders WHERE name='$na'");
        if($query) { $msg = "Deleted orders for: $na"; $msg_type = 'success'; }
        else { $msg = "Delete failed: " . mysqli_error($con); $msg_type = 'error'; }
    }
}

// Always load full orders table
$query = mysqli_query($con,"SELECT * FROM orders");
if(mysqli_num_rows($query) > 0) {
    $all_orders_html = '<table class="res-table"><thead><tr><th>ID</th><th>Type</th><th>Name</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th></tr></thead><tbody>';
    while($row = mysqli_fetch_array($query)) {
        $all_orders_html .= '<tr><td>'.htmlspecialchars($row['id']).'</td><td>'.htmlspecialchars($row['type']).'</td><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['product']).'</td><td>'.htmlspecialchars($row['quantity']).'</td><td>Rs.'.htmlspecialchars($row['amount']).'</td><td>'.htmlspecialchars($row['status']).'</td></tr>';
    }
    $all_orders_html .= '</tbody></table>';
}

if(isset($_POST['Logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

mysqli_close($con);

$active = 'report';
if(isset($_GET['action'])) $active = $_GET['action'];
elseif(isset($_POST['add'])) $active = 'add';
elseif(isset($_POST['search'])) $active = 'search';
elseif(isset($_POST['update'])) $active = 'update';
elseif(isset($_POST['delete'])) $active = 'delete';
elseif(isset($_POST['report'])) $active = 'report';
?>
<html>
<head>
    <title>Orders Panel – Ceylon Coconut Mill</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body { background: #f5f5f0; font-family: Arial, sans-serif; margin: 0; }
        .shell { display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: #1a3a1a; padding: 28px 0; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar-brand { color: #fff; font-size: 17px; font-weight: 800; padding: 0 22px 24px; border-bottom: 1px solid #2d5a2d; }
        .sidebar-brand span { color: #7ec820; }
        .sidebar-user { font-size: 12px; color: #aaa; padding: 16px 22px 12px; border-bottom: 1px solid #2d5a2d; }
        .sidebar-user strong { display: block; color: #fff; font-size: 13px; }
        .sidebar-nav { padding: 14px 0; }
        .sidebar-nav-title { font-size: 10px; color: #5a7a5a; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 0 22px 8px; }
        .nav-link { display: block; padding: 10px 22px; color: #ccc; font-size: 13px; font-weight: 500; cursor: pointer; border: none; background: none; width: 100%; text-align: left; text-decoration: none; }
        .nav-link:hover { color: #fff; background: #234523; }
        .nav-link.active { color: #7ec820; background: #1f401f; border-left: 3px solid #7ec820; font-weight: 700; }
        .main { flex: 1; padding: 36px 40px; }
        .toprow { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .toprow h1 { font-size: 22px; font-weight: 800; color: #1a3a1a; margin: 0; }
        .panel { background: #fff; border: 1px solid #e3e3e3; border-radius: 10px; padding: 24px 26px; margin-bottom: 18px; }
        .panel h3 { font-size: 15px; font-weight: 700; color: #1a3a1a; margin: 0 0 18px; }
        .frow { margin-bottom: 14px; }
        .frow label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .frow input, .frow select {
            border: 1px solid #d0d0d0; border-radius: 7px; padding: 9px 12px;
            font-size: 14px; color: #333; width: 280px; max-width: 100%;
            outline: none; background: #fff; box-sizing: border-box;
        }
        .frow input:focus, .frow select:focus { border-color: #7ec820; }
        .btn-green { background: #7ec820; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-green:hover { background: #6ab318; }
        .btn-red { background: #c00; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-red:hover { background: #a00; }
        .btn-logout { background: transparent; border: 2px solid #7ec820; color: #7ec820; border-radius: 7px; padding: 8px 18px; font-size: 13px; font-weight: 700; cursor: pointer; margin: 20px 22px 0; display: block; }
        .btn-logout:hover { background: #7ec820; color: #fff; }
        .back-link { font-size: 13px; color: #7ec820; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .alert { border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 18px; }
        .alert-success { background: #f0fae6; border: 1px solid #b5e07a; color: #3a6a00; }
        .alert-error { background: #fff0f0; border: 1px solid #f5c6c6; color: #c00; }
        .res-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 16px; }
        .res-table th { background: #1a3a1a; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; }
        .res-table td { padding: 10px 12px; border-bottom: 1px solid #eee; color: #444; }
        .res-table tr:last-child td { border-bottom: none; }
        .res-table tr:hover td { background: #f9fdf4; }
    </style>
    <script>
        window.addEventListener("pageshow", function(e) {
            if(e.persisted || window.performance.navigation.type === 2) window.location.reload();
        });
    </script>
</head>
<body>
<div class="shell">
    <div class="sidebar">
        <div class="sidebar-brand">Ceylon <span>Coconut</span> Mill</div>
        <div class="sidebar-user">Logged in as<br><strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></div>
        <div class="sidebar-nav">
            <div class="sidebar-nav-title">Actions</div>
            <a class="nav-link <?php if($active=='report') echo 'active'; ?>" href="orders.php">All Orders</a>
            <a class="nav-link <?php if($active=='add') echo 'active'; ?>" href="orders.php?action=add">+ Add Order</a>
            <a class="nav-link <?php if($active=='search') echo 'active'; ?>" href="orders.php?action=search">Search</a>
            <a class="nav-link <?php if($active=='update') echo 'active'; ?>" href="orders.php?action=update">Update</a>
            <a class="nav-link <?php if($active=='delete') echo 'active'; ?>" href="orders.php?action=delete">Delete</a>
        </div>
        <form method="POST" action="orders.php" style="margin-top:auto;">
            <button class="btn-logout" name="Logout">Logout</button>
        </form>
    </div>
    <div class="main">
        <div class="toprow">
            <h1>Orders Panel</h1>
            <a class="back-link" href="index.html">← Back to Home</a>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if($active=='add'): ?>
        <div class="panel">
            <h3>Add Order</h3>
            <form method="POST" action="orders.php?action=add">
                <div class="frow">
                    <label>Type</label>
                    <select name="ty">
                        <option value="">-- Select --</option>
                        <option value="Buy">Buy</option>
                        <option value="Sell">Sell</option>
                    </select>
                </div>
                <div class="frow"><label>Farmer / Customer Name</label><input type="text" name="na"></div>
                <div class="frow"><label>Product</label><input type="text" name="pr"></div>
                <div class="frow"><label>Quantity</label><input type="number" name="qu"></div>
                <div class="frow"><label>Amount (Rs.)</label><input type="number" name="am"></div>
                <div class="frow">
                    <label>Status</label>
                    <select name="st">
                        <option value="">-- Select --</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <input class="btn-green" type="submit" name="add" value="Add Order">
            </form>
        </div>

        <?php elseif($active=='search'): ?>
        <div class="panel">
            <h3>Search Order</h3>
            <form method="POST" action="orders.php?action=search">
                <div class="frow"><label>Farmer / Customer Name</label><input type="text" name="na" value="<?php echo isset($_POST['na']) ? htmlspecialchars($_POST['na']) : ''; ?>"></div>
                <input class="btn-green" type="submit" name="search" value="Search">
            </form>
            <?php if($search_html): ?>
                <p style="font-size:12px;color:#777;margin:16px 0 6px;">Search results:</p>
                <?php echo $search_html; ?>
                <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
            <?php endif; ?>
            <p style="font-size:12px;color:#777;margin-bottom:6px;">All orders:</p>
            <?php echo $all_orders_html ?: '<p style="font-size:13px;color:#aaa;">No orders yet.</p>'; ?>
        </div>

        <?php elseif($active=='update'): ?>
        <div class="panel">
            <h3>Update Order</h3>
            <form method="POST" action="orders.php?action=update">
                <div class="frow"><label>Farmer / Customer Name (to find)</label><input type="text" name="na"></div>
                <div class="frow"><label>Product</label><input type="text" name="pr"></div>
                <div class="frow"><label>Quantity</label><input type="number" name="qu"></div>
                <div class="frow"><label>Amount (Rs.)</label><input type="number" name="am"></div>
                <div class="frow">
                    <label>Status</label>
                    <select name="st">
                        <option value="">-- Select --</option>
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <input class="btn-green" type="submit" name="update" value="Update">
            </form>
        </div>

        <?php elseif($active=='delete'): ?>
        <div class="panel">
            <h3>Delete Order</h3>
            <form method="POST" action="orders.php?action=delete">
                <div class="frow"><label>Farmer / Customer Name</label><input type="text" name="na"></div>
                <input class="btn-red" type="submit" name="delete" value="Delete">
            </form>
            <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
            <p style="font-size:12px;color:#777;margin-bottom:6px;">All orders:</p>
            <?php echo $all_orders_html ?: '<p style="font-size:13px;color:#aaa;">No orders yet.</p>'; ?>
        </div>

        <?php else: ?>
        <div class="panel">
            <h3>All Orders</h3>
            <?php echo $all_orders_html ?: '<p style="font-size:13px;color:#aaa;">No orders yet.</p>'; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
