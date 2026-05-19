<?php
session_start();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Packages</title>
</head>

<body>

<h1>Manage Packages</h1>

<div id="container" class="container"></div>

<script>

const apiKey = localStorage.getItem("api_key");
const container = document.getElementById("container");

// LOAD PACKAGES
function loadPackages(){

    fetch("api/api.php", {

        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            type: "GetAgencyPackages",
            api_key: apiKey
        })

    })
    .then(res => res.json())
    .then(data => {

        container.innerHTML = "";

        if(data.status === "success"){

            data.data.forEach(pkg => {

                container.innerHTML += `
                
                <div class="card">

                    <div class="title">${pkg.PackageName}</div>

                    <div>${pkg.StartDate} → ${pkg.EndDate}</div>

                    <div class="price">R ${pkg.Price}</div>

                    <a class="btn edit"
                       href="editPackage.php?id=${pkg.PackageID}">
                        Edit
                    </a>

                    <button class="btn delete"
                        onclick="deletePackage(${pkg.PackageID})">
                        Delete
                    </button>

                </div>

                `;
            });

        } else {
            container.innerHTML = data.message;
        }

    });

}

// ============================
// DELETE PACKAGE
// ============================
function deletePackage(id){

    if(!confirm("Delete this package?")) return;

    fetch("api/api.php", {

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
            loadPackages();
        } else {
            alert(data.message);
        }
    });
}

// Load on start
loadPackages();

</script>

</body>
</html>