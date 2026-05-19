<?php 
include("config.php"); 
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<!--Dynamic Page title-->
    <?php if(isset($pageTitle) && !empty($pageTitle)): ?>
        <title><?php echo $pageTitle ?? "Flight System"; ?></title>
    <?php endif; ?>

    <link rel="stylesheet" href="css/navbar.css">
    <link rel="stylesheet" href="css/main.css">

    <?php if(isset($pageCSS) && !empty($pageCSS)): ?>
        <link rel="stylesheet" href="<?php echo $pageCSS; ?>">
    <?php endif; ?>

</head>
<body>
    <header>
    <nav>
        <ul>
        <li><a href="index.php">Book Flights</a></li>
        <li><a href="bookings.php" >Bookings</a></li>
        <li><a href="planes.php">Planes</a></li>
        <li><a href="favourites.php">Favourite Planes</a></li>
        <?php 
            if(isset($_SESSION['user'])):
        ?>
            <!-- <li>Welcome,<?php echo $_SESSION['user']['name']; ?></li> -->
            <li><a href="logout.php">Logout</a></li>

        <?php else: ?>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Register</a></li>
        <?php endif; ?>

        </ul>
    </nav>
</header>

        