<?php

$id = $_GET['id'] ?? null;

if(!$id){
    die("No package selected");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>View Package</title>

    <link rel="stylesheet" href="css/viewPackage.css">

</head>

<body>
    <button class="btn back" onclick="window.history.back()">← Back</button>
    <div class="container">

        <div id="packageCard" class="card">

            Loading package...

        </div>

    </div>

    <script>

        const packageID = <?php echo $id; ?>;

    </script>

    <script src="js/viewPackage.js"></script>

</body>

</html>