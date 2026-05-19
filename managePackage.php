
<!DOCTYPE html>
<html>

<head>
    <title>Manage Packages</title>
    <link rel="stylesheet" href="css/managePackage.css">
</head>

<body>

<h1>Manage Packages</h1>

<div id="container" class="container"></div>

<script>

const apiKey = localStorage.getItem("api_key");

    if(!apiKey){

        alert("Please login first");

        window.location.href = "login.html";
    }
    
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

                    <div class="title">${pkg.Title}</div>

                    <div>${pkg.Start_date} → ${pkg.End_date}</div>

                    <div class="price">R ${pkg.Total_price}</div>

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
            container.innerHTML = data.data;
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
            alert(data.data);
        }
    });
}

// Load on start
loadPackages();

</script>

</body>
</html>