<?php

session_start();

// Check if the user is logged in
if(isset($_SESSION['user'])) {
    
    $userLoggedIn = true;
    $profilePicture = $_SESSION['user']['picture'];  // Get picture stored in session 
} 

else {
    
    $userLoggedIn = false;
    $profilePicture = null;
}
?>


<!-- This is the html that will only show for a logged in user. Only the header is modified for now.-->
<link rel="stylesheet" href="/pages_css/style.css">
<header class="header">
    <nav class="navbar">
        <a href="/pages_php/index.php">Home</a>
        <a href="/pages_php/search.php">Find Parking</a>
        <a href="/pages_php/payment.php">Payment</a>
        
        <!-- Show Register and Login links if user is not logged in -->
        <?php if (!$userLoggedIn): ?>
            <a href="/pages_php/registration.php">Register</a>
            <a href="/pages_php/login.php">Login</a>
        <?php else: ?>
            <!-- If logged in, show the profile image that links to the dashboard -->
            <a href="/pages_php/dashboard.php">
                <img src="<?= $profilePicture; ?>" alt="Profile" class="profile-icon">
            </a>
        <?php endif; ?>
    </nav>
</header>