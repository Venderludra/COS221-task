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

            document.getElementById("packageName").value = p.PackageName;
            document.getElementById("destination").value = p.Destination;
            document.getElementById("description").value = p.Description;
            document.getElementById("price").value = p.Price;
            document.getElementById("startDate").value = p.StartDate;
            document.getElementById("endDate").value = p.EndDate;
            document.getElementById("packageType").value = p.PackageType;

        } else {
            msg.innerHTML = data.message;
        }

    });

}

// ======================================
// UPDATE PACKAGE
// ======================================
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
            destination: document.getElementById("destination").value,
            description: document.getElementById("description").value,
            price: document.getElementById("price").value,
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
            msg.innerHTML = data.message;
        }

    });

});

// Load on page start
loadPackage();

</script>

</body>
</html>