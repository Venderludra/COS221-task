<?php
    // No session needed (you use localStorage)
?>

<!DOCTYPE html>
<html>

<head>
    <title>Agency Dashboard</title>
</head>

<body>

<?php include("AgencyNavBar.php"); ?>

<div class="container">

    <h1>Welcome Agency</h1>

    <p>Manage your travel packages, flights, restaurants, and more.</p>

    <!-- DASHBOARD CARDS -->
    <div class="cards">

        <div class="card">
            <h2>Packages</h2>
            <p>Create and manage travel packages</p>
            <a class="btn" href="managePackage.php">
                View Packages
            </a>
        </div>

        <div class="card">
            <h2>Create Package</h2>
            <p>Add a new travel experience</p>
            <a class="btn" href="createPackage.php">
                Create
            </a>
        </div>

        <div class="card">
            <h2>Flights</h2>
            <p>Add flights to packages</p>
            <a class="btn" href="manageFlight.php">
                Manage Flights
            </a>
        </div>

        <div class="card">
            <h2>Restaurants</h2>
            <p>Add dining options</p>
            <a class="btn" href="manageRestaurant.php">
                Manage Restaurants
            </a>
        </div>

    </div>

</div>

<script>

// Protect page (if no login)
if(!localStorage.getItem("api_key")){
    window.location.href = "login.php";
}

</script>

</body>

</html>