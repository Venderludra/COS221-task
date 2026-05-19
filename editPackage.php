<?php
    $packageID = $_GET['id'] ?? null;

    if(!$packageID){
        die("Invalid Package ID");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Package</title>
    <link rel="stylesheet" href="css/editPackage.css">
</head>

<body>

<div class="container">

    <h2>Edit Package</h2>

    <form id="editForm">

        <input type="text" id="packageName" placeholder="Package Name">

        <textarea id="description" placeholder="Description"></textarea>

        <input type="number" id="price" placeholder="Price">

        <input type="date" id="startDate">

        <input type="date" id="endDate">

        <input type="number" id = 'capacity' placeholder="capacity">

        <input type="number" id = 'duration' placeholder="Trip Duration">

        <select id="packageType">
            <option value="Adventure">Adventure</option>
            <option value="Luxury">Luxury</option>
            <option value="Family">Family</option>
            <option value="Romantic">Romantic</option>
        </select>

        <button type="submit">Update Package</button>

    </form>

    <div class="msg" id="msg"></div>

</div>

<script>

const packageID = "<?php echo $packageID; ?>";
const apiKey = localStorage.getItem("api_key");
const msg = document.getElementById("msg");

// ======================================
// LOAD PACKAGE DETAILS
// ======================================
function loadPackage(){

    fetch("api/api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "GetPackageByID",
            api_key: apiKey,
            PackageID: packageID
        })
    })

    .then(res => res.json())
    .then(data => {

        if(data.status === "success"){

            const p = data.data;
            document.getElementById("packageName").value = p.Title;
            document.getElementById("description").value = p.Description;
            document.getElementById("price").value = p.Total_rice;

            document.getElementById('capacity').value = p.Capacity;
            document.getElementById('duration').value = p.Duration;

            document.getElementById("startDate").value = p.Start_date;
            document.getElementById("endDate").value = p.End_date;
            document.getElementById("packageType").value = p.PackageType;

        } else {
            msg.innerHTML = data.data;
        }

    });

}

// UPDATE PACKAGE
document.getElementById("editForm")
.addEventListener("submit", function(e){

    e.preventDefault();

    fetch("api/api.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "EditPackage",
            api_key: apiKey,
            PackageID: packageID,

            package_name: document.getElementById("packageName").value,
            description: document.getElementById("description").value,
            price: document.getElementById("price").value,
            Capacity: document.getElementById('capacity').value,
            Duration : document.getElementById('duration').value,
            start_date: document.getElementById("startDate").value,
            end_date: document.getElementById("endDate").value,
            package_type: document.getElementById("packageType").value

        })
    })

    .then(res => res.json())
    .then(data => {

        if(data.status === "success"){
            msg.innerHTML = "Package updated successfully!";
        } else {
            msg.innerHTML = data.data;
        }

    });

});

// Load on page start
loadPackage();

</script>

</body>
</html>