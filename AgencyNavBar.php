<!-- agencyNavbar.php -->

<div class="navbar">

    <div>
        ✈ Agency Portal
    </div>

    <div>

        <a href="agencyDashboard.php">Dashboard</a>
        <a href="createPackage.php">Create Package</a>
        <a href="managePackages.php">Packages</a>

        <span class="logout-btn" onclick="logout()">
            Logout
        </span>

    </div>

</div>

<script>

function logout(){
    localStorage.removeItem("api_key");
    window.location.href = "login.php";
}

</script>