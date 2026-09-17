<?php
session_start();
if(!isset($_SESSION['username'])) { header('location: login.php'); exit; }

$msg = '';
$msg_type = '';
$table_html = '';
$edit_user = null;

require 'db_config.php';
$con = get_db_connection();

// Load user for editing
if(isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $r = mysqli_query($con, "SELECT * FROM users WHERE id=$eid");
    $edit_user = mysqli_fetch_array($r);
}

if(isset($_POST['add'])) {
    $na = trim($_POST["na"]);
    $un = trim($_POST["un"]);
    $pa = trim($_POST["pa"]);
    $ro = trim($_POST["ro"]);
    if($na==''||$un==''||$pa==''||$ro=='') {
        $msg = "All fields are required."; $msg_type = 'error';
    } else {
        $q = mysqli_query($con,"INSERT INTO users(name,username,password,role) values('$na','$un','$pa','$ro')");
        if($q) { $msg = "User added: $na ($ro)"; $msg_type = 'success'; }
        else { $msg = "Failed: ".mysqli_error($con); $msg_type = 'error'; }
    }
}

if(isset($_POST['update'])) {
    $id = (int)$_POST['uid'];
    $un = trim($_POST['un']);
    $pa = trim($_POST['pa']);
    $ro = trim($_POST['ro']);
    if($un==''||$pa==''||$ro=='') {
        $msg = "All fields are required."; $msg_type = 'error';
    } else {
        $q = mysqli_query($con,"UPDATE users SET username='$un', password='$pa', role='$ro' WHERE id=$id");
        if($q) { $msg = "User updated successfully."; $msg_type = 'success'; }
        else { $msg = "Update failed: ".mysqli_error($con); $msg_type = 'error'; }
    }
}

if(isset($_POST['search'])) {
    $na = trim($_POST['na']);
    if($na=='') { $msg = "Enter a name to search."; $msg_type = 'error'; }
    else {
        $q = mysqli_query($con,"SELECT * FROM users WHERE name='$na'");
        $row = mysqli_fetch_array($q);
        if($row) {
            $table_html = '<table class="res-table"><thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th></tr></thead><tbody>';
            $table_html .= '<tr><td>'.htmlspecialchars($row['id']).'</td><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['username']).'</td><td>'.htmlspecialchars($row['role']).'</td></tr>';
            $table_html .= '</tbody></table>';
        } else { $msg = "No record found."; $msg_type = 'error'; }
    }
}

if(isset($_POST['delete'])) {
    $na = trim($_POST['na']);
    if($na=='') { $msg = "Enter a name to delete."; $msg_type = 'error'; }
    else {
        $q = mysqli_query($con,"DELETE FROM users WHERE name='$na'");
        if($q) { $msg = "Deleted: $na"; $msg_type = 'success'; }
        else { $msg = "Delete failed: ".mysqli_error($con); $msg_type = 'error'; }
    }
}

if(isset($_POST['report'])) {
    $q = mysqli_query($con,"SELECT * FROM users");
    if(mysqli_num_rows($q)==0) { $msg = "No users found."; $msg_type = 'error'; }
    else {
        $table_html = '<table class="res-table"><thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th></tr></thead><tbody>';
        while($row = mysqli_fetch_array($q)) {
            $table_html .= '<tr><td>'.htmlspecialchars($row['id']).'</td><td>'.htmlspecialchars($row['name']).'</td><td>'.htmlspecialchars($row['username']).'</td><td>'.htmlspecialchars($row['role']).'</td></tr>';
        }
        $table_html .= '</tbody></table>';
    }
}

if(isset($_POST['Logout'])) { session_destroy(); header('Location: login.php'); exit; }

// Build user list table for Update panel
$users_table = '';
$uq = mysqli_query($con,"SELECT * FROM users");
if(mysqli_num_rows($uq) > 0) {
    $users_table = '<table class="res-table"><thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Role</th><th></th></tr></thead><tbody>';
    while($u = mysqli_fetch_array($uq)) {
        $users_table .= '<tr><td>'.htmlspecialchars($u['id']).'</td><td>'.htmlspecialchars($u['name']).'</td><td>'.htmlspecialchars($u['username']).'</td><td>'.htmlspecialchars($u['role']).'</td>';
        $users_table .= '<td><a class="edit-btn" href="admin.php?action=update&edit_id='.$u['id'].'">Edit</a></td></tr>';
    }
    $users_table .= '</tbody></table>';
}

mysqli_close($con);

$active = 'none';
if(isset($_GET['action'])) $active = $_GET['action'];
elseif(isset($_POST['add'])) $active = 'add';
elseif(isset($_POST['update'])) $active = 'update';
elseif(isset($_POST['search'])) $active = 'search';
elseif(isset($_POST['delete'])) $active = 'delete';
elseif(isset($_POST['report'])) $active = 'report';
?>
<html>
<head>
    <title>Admin Panel – Ceylon Coconut Mill</title>
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
        .nav-link { display: block; padding: 10px 22px; color: #ccc; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: none; }
        .nav-link:hover { color: #fff; background: #234523; }
        .nav-link.active { color: #7ec820; background: #1f401f; border-left: 3px solid #7ec820; font-weight: 700; }
        .main { flex: 1; padding: 36px 40px; }
        .toprow { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
        .toprow h1 { font-size: 22px; font-weight: 800; color: #1a3a1a; margin: 0; }
        .panel { background: #fff; border: 1px solid #e3e3e3; border-radius: 10px; padding: 24px 26px; margin-bottom: 18px; }
        .panel h3 { font-size: 15px; font-weight: 700; color: #1a3a1a; margin: 0 0 18px; }
        .frow { margin-bottom: 14px; }
        .frow label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .frow input, .frow select { border: 1px solid #d0d0d0; border-radius: 7px; padding: 9px 12px; font-size: 14px; color: #333; width: 300px; max-width: 100%; outline: none; background: #fff; box-sizing: border-box; }
        .frow input:focus, .frow select:focus { border-color: #7ec820; }
        .frow .hint { font-size: 12px; color: #999; margin-top: 4px; }
        .btn-green { background: #7ec820; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-green:hover { background: #6ab318; }
        .btn-red { background: #c00; color: #fff; border: none; border-radius: 7px; padding: 9px 22px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-red:hover { background: #a00; }
        .btn-ghost { background: transparent; border: 1px solid #d0d0d0; color: #555; border-radius: 7px; padding: 8px 18px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-block; margin-left: 10px; }
        .btn-ghost:hover { border-color: #999; color: #333; }
        .btn-logout { background: transparent; border: 2px solid #7ec820; color: #7ec820; border-radius: 7px; padding: 8px 18px; font-size: 13px; font-weight: 700; cursor: pointer; margin: 20px 22px 0; display: block; }
        .btn-logout:hover { background: #7ec820; color: #fff; }
        .back-link { font-size: 13px; color: #7ec820; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .alert { border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 18px; }
        .alert-success { background: #f0fae6; border: 1px solid #b5e07a; color: #3a6a00; }
        .alert-error { background: #fff0f0; border: 1px solid #f5c6c6; color: #c00; }
        .res-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 4px; }
        .res-table th { background: #1a3a1a; color: #fff; padding: 10px 12px; text-align: left; font-weight: 600; }
        .res-table td { padding: 10px 12px; border-bottom: 1px solid #eee; color: #444; }
        .res-table tr:last-child td { border-bottom: none; }
        .res-table tr:hover td { background: #f9fdf4; }
        .edit-btn { background: #f0fae6; color: #4a8500; border: 1px solid #b5e07a; border-radius: 5px; padding: 4px 12px; font-size: 12px; font-weight: 600; text-decoration: none; }
        .edit-btn:hover { background: #7ec820; color: #fff; border-color: #7ec820; }
        .edit-banner { background: #f0fae6; border: 1px solid #b5e07a; border-radius: 8px; padding: 10px 16px; margin-bottom: 20px; font-size: 13px; color: #3a6a00; display: flex; align-items: center; justify-content: space-between; }
        .edit-banner strong { font-size: 14px; }
        .divider { border: none; border-top: 1px solid #eee; margin: 20px 0; }
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
            <a class="nav-link <?php if($active=='add') echo 'active'; ?>" href="admin.php?action=add">+ Add User</a>
            <a class="nav-link <?php if($active=='update') echo 'active'; ?>" href="admin.php?action=update">Edit Users</a>
            <a class="nav-link <?php if($active=='search') echo 'active'; ?>" href="admin.php?action=search">Search</a>
            <a class="nav-link <?php if($active=='delete') echo 'active'; ?>" href="admin.php?action=delete">Delete</a>
            <a class="nav-link <?php if($active=='report') echo 'active'; ?>" href="admin.php?action=report">Report</a>
        </div>
        <form method="POST" action="admin.php" style="margin-top:auto;">
            <button class="btn-logout" name="Logout">Logout</button>
        </form>
    </div>
    <div class="main">
        <div class="toprow">
            <h1>Admin Panel</h1>
            <a class="back-link" href="index.html">← Back to Home</a>
        </div>

        <?php if($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <?php if($active=='add'): ?>
        <div class="panel">
            <h3>Add User</h3>
            <form method="POST" action="admin.php?action=add">
                <div class="frow"><label>Full Name</label><input type="text" name="na" placeholder="e.g. John Silva"></div>
                <div class="frow"><label>Username</label><input type="text" name="un" placeholder="Login username"></div>
                <div class="frow"><label>Password</label><input type="password" name="pa" placeholder="Set a password"></div>
                <div class="frow">
                    <label>Role</label>
                    <select name="ro">
                        <option value="">-- Select Role --</option>
                        <option value="Admin">Admin</option>
                        <option value="Staff">Staff</option>
                    </select>
                </div>
                <input class="btn-green" type="submit" name="add" value="Add User">
            </form>
        </div>

        <?php elseif($active=='update'): ?>
        <div class="panel">
            <?php if($edit_user): ?>
                <div class="edit-banner">
                    <span>Editing: <strong><?php echo htmlspecialchars($edit_user['name']); ?></strong></span>
                    <a href="admin.php?action=update" style="font-size:12px;color:#777;text-decoration:none;">← Back to list</a>
                </div>
                <form method="POST" action="admin.php?action=update">
                    <input type="hidden" name="uid" value="<?php echo $edit_user['id']; ?>">
                    <div class="frow">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($edit_user['name']); ?>" disabled style="background:#f5f5f0;color:#999;">
                        <div class="hint">Name cannot be changed</div>
                    </div>
                    <div class="frow">
                        <label>Username</label>
                        <input type="text" name="un" value="<?php echo htmlspecialchars($edit_user['username']); ?>">
                    </div>
                    <div class="frow">
                        <label>New Password</label>
                        <input type="password" name="pa" placeholder="Enter new password">
                    </div>
                    <div class="frow">
                        <label>Role</label>
                        <select name="ro">
                            <option value="Admin" <?php if($edit_user['role']=='Admin') echo 'selected'; ?>>Admin</option>
                            <option value="Staff" <?php if($edit_user['role']=='Staff') echo 'selected'; ?>>Staff</option>
                        </select>
                    </div>
                    <input class="btn-green" type="submit" name="update" value="Save Changes">
                    <a class="btn-ghost" href="admin.php?action=update">Cancel</a>
                </form>
            <?php else: ?>
                <h3>Edit Users</h3>
                <p style="font-size:13px;color:#777;margin-bottom:14px;">Click <strong>Edit</strong> on any user to update their details.</p>
                <?php echo $users_table; ?>
            <?php endif; ?>
        </div>

        <?php elseif($active=='search'): ?>
        <div class="panel">
            <h3>Search User</h3>
            <form method="POST" action="admin.php?action=search">
                <div class="frow"><label>Name</label><input type="text" name="na" placeholder="Enter full name"></div>
                <input class="btn-green" type="submit" name="search" value="Search">
            </form>
            <?php echo $table_html; ?>
        </div>

        <?php elseif($active=='delete'): ?>
        <div class="panel">
            <h3>Delete User</h3>
            <form method="POST" action="admin.php?action=delete">
                <div class="frow"><label>Name</label><input type="text" name="na" placeholder="Enter full name"></div>
                <input class="btn-red" type="submit" name="delete" value="Delete User">
            </form>
        </div>

        <?php elseif($active=='report'): ?>
        <div class="panel">
            <h3>User Report</h3>
            <form method="POST" action="admin.php?action=report">
                <input class="btn-green" type="submit" name="report" value="View All Users">
            </form>
            <?php echo $table_html; ?>
        </div>

        <?php else: ?>
        <div class="panel" style="text-align:center;padding:48px;">
            <div style="font-size:15px;font-weight:600;color:#444;margin-bottom:6px;">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></div>
            <div style="font-size:13px;color:#aaa;">Select an action from the sidebar to get started.</div>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
