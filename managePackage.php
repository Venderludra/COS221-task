<!DOCTYPE html>
<html>

<head>
    <title>Manage Packages</title>
    <link rel="stylesheet" href="css/managePackage.css">
    <link rel="stylesheet" href="css/AgencyNavBar.css">
</head>

<body>
<?php include("AgencyNavBar.php"); ?>
<h1>Manage Packages</h1>

<div id="container" class="container"></div>

<script>

const apiKey = localStorage.getItem("api_key");

    if(!apiKey){

        alert("Please login first");

        window.location.href = "login.php";
    }
    
const container = document.getElementById("container");

// LOAD PACKAGES
function loadPackages(){

    fetch("api.php", {

        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "GetAgencyPackagesByID",
            api_key: apiKey
        })

    })
    .then(res => res.json())
    .then(data => {

        container.innerHTML = "";

        if(data.status === "success"){

            data.data.forEach(pkg => {

                container.innerHTML += `
                
                <div class="card" onclick="viewPackage(${pkg.PackageID})">

                    <div class="title">${pkg.Title}</div>

                    <div>${pkg.Start_date} → ${pkg.End_date}</div>

                    <div class="price">R ${pkg.Total_price}</div>

                    <button class="btn edit"
                       onclick="event.stopPropagation(); editPackage(${pkg.PackageID})">
                        Edit
                    </button>

                    <button class="btn delete"
                        onclick="event.stopPropagation(); deletePackage(${pkg.PackageID})">
                        Delete
                    </button>

                </div>

                `;
            });

        } else {
            container.innerHTML = data.data;
        }

    });

}

function editPackage(id){
    window.location.href = `editPackage.php?id=${id}`;
}
// ============================
// DELETE PACKAGE
// ============================
function deletePackage(id){

    if(!confirm("Delete this package?")) return;

    fetch("api.php", {

        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "DeletePackage",
            api_key: apiKey,
            PackageID: id
        })

    })
    .then(res => res.json())
    .then(data => {

        if(data.status === "success"){
            alert("Package deleted successfully!");
            loadPackages();
        } else {
            alert(data.data);
        }
    });
}

function viewPackage(id){
    window.location.href = `viewPackages.php?id=${id}`;
}

// Load on start
loadPackages();

</script>

</body>
</html>
