<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); // Redirect to admin login if not logged in
    exit();
}

// Include database connection
include 'db_connect.php';

// Initialize search variable
$search = "";

// Check if a search has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $search = trim($_POST['search']);
}

// Fetch user data based on search input
if ($search) {
    $stmt = $conn->prepare("SELECT id, name, email, phone, plan FROM users WHERE name LIKE ? OR email LIKE ?");
    $searchParam = "%" . $search . "%"; // Prepare search parameter with wildcards
    $stmt->bind_param("ss", $searchParam, $searchParam);
} else {
    $stmt = $conn->prepare("SELECT id, name, email, phone, plan FROM users");
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query failed: " . $conn->error); // Handle query error
}

// Handle membership cancellation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_membership'])) {
    $user_id = intval($_POST['user_id']);
    $delete_result = $conn->query("DELETE FROM users WHERE id = $user_id");

    if ($delete_result) {
        echo "<p class='success-message'>Membership cancelled successfully!</p>";
        // Refresh the user list
        $result = $conn->query("SELECT id, name, email, phone, plan FROM users");
    } else {
        echo "<p class='error-message'>Error cancelling membership: " . $conn->error . "</p>";
    }
}


// Handle plan update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_plan'])) {
    $user_id = intval($_POST['user_id']);
    $new_plan = $_POST['new_plan']; // Get the new plan from the form

    $update_stmt = $conn->prepare("UPDATE users SET plan = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_plan, $user_id);

    if ($update_stmt->execute()) {
        echo "<p class='success-message'>Plan updated successfully!</p>";
        // Refresh the user list
        $result = $conn->query("SELECT id, name, email, phone, plan FROM users");
    } else {
        echo "<p class='error-message'>Error updating plan: " . $conn->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Olympus Gym</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="nav__header section__container">
        <div class="nav__logo">
            <a href="index.html">
                <img src="assets/logo.png" alt="logo" width="150" height="154" />
                OLYMPUS STRENGTH GYM
            </a>
        </div>
        <nav class="nav__menu">
            <ul class="nav__links">
                <li class="link"><a href="index.html">Home</a></li>
                <li class="link"><a href="about.html">About</a></li>
                <li class="link"><a href="classes.html">Classes</a></li>
                <li class="link"><a href="trainers.html">Trainers</a></li>
                <li class="link"><a href="pricing.html">Pricing</a></li>
                <li class="link"><a href="contact.html">Contact Us</a></li>
                <li class="link"><a href="logout.php" class="btn">Log Out</a></li>
            </ul>
        </nav>
    </div>
</header>

<div class="section__container">
    <h1 style="text-align: center;">Admin Dashboard - Olympus Gym</h1>
    <h2 style="text-align: center;">Manage Users</h2>

    <!-- Search Form -->
<form method="POST" action="" class="search-form">
    <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn">Search</button>
</form>

    <table class="user-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Plan</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['plan']); ?></td>
                <td>
                    <form method="POST" action="">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                        <select name="new_plan">
                            <option value="zeus" <?php if($row['plan'] == 'zeus') echo 'selected'; ?>>zeus</option>
                            <option value="athena" <?php if($row['plan'] == 'athena') echo 'selected'; ?>>athena</option>
                            <option value="hades" <?php if($row['plan'] == 'hades') echo 'selected'; ?>>hades</option>
                        </select>
                        <button type="submit" name="update_plan" class="btn">Update Plan</button>
                        <button type="submit" name="cancel_membership" class="cancel-btn">Cancel Membership</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<!-- Footer -->
<footer class="footer">
  <div class="section__container footer__container">
    <div class="footer__col">
      <div class="footer__logo">
        <p><a href="index.html"><img src="assets/logo.png" alt="logo" width="92" height="90"> OLYMPUS</a></p>
      </div>
      <p>Become the best version of yourself with Olympus Strength Gym. Join us today and experience fitness like never before.</p>
      <div class="footer__socials">
        <a href="#"><i class="ri-facebook-fill"></i></a>
        <a href="#"><i class="ri-instagram-line"></i></a>
        <a href="#"><i class="ri-twitter-fill"></i></a>
      </div>
    </div>
    <div class="footer__col">
      <h4>Quick Links</h4>
      <ul><br>
        <li><a href="about.html">About Us</a></li>
        <li><a href="classes.html">Classes</a></li>
        <li><a href="trainers.html">Trainers</a></li>
        <li><a href="pricing.html">Pricing</a></li>
      </ul>
    </div>
    <div class="footer__col">
      <h4>Contact Us</h4>
      <br>
      <p>123 Fitness Street, Muscle City, CA 45678</p>
      <p>Email: info@olympusgym.com</p>
      <p>Phone: +1 (234) 567-8901</p>
    </div>
  </div>
  <div class="footer__bar" style="text-align: center;"> <!-- Centering the button in the footer bar -->
    <p>&copy; 2023 Olympus Strength Gym. All rights reserved.</p>
  </div>
</footer>
</body>
</html>