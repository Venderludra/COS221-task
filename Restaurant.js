document.addEventListener("DOMContentLoaded", loadRestaurants);

// LOAD RESTAURANTS

function loadRestaurants() {

    const apiURL = "http://localhost/COS221/api.php";

    // API key from login
    const apiKey = localStorage.getItem("api_key");

    if (!apiKey) {
        alert("User not logged in");
        return;
    }

    // ======================================
    // REQUEST DATA
    // ======================================
    const requestData = {
        type: "GetAllRestaurant",
        api_key: apiKey
    };

   
    // CREATE REQUEST
    const xhr = new XMLHttpRequest();

    xhr.open("POST", apiURL, true);

    xhr.setRequestHeader(
        "Content-Type",
        "application/json"
    );


    // HANDLE RESPONSE
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response =
                    JSON.parse(xhr.responseText);
                if (response.status === "success") {
                    printRestaurants(response.data);
                } else {
                    alert(response.data);
                }

            } else {
                alert("Failed to connect to API");
            }

        }

    };


    // SEND REQUEST
    xhr.send(JSON.stringify(requestData));

}



// PRINT RESTAURANTS
function printRestaurants(restaurants) {

    const container =
        document.getElementById("restaurant-container");

    container.innerHTML = "";

    // NO RESTAURANTS
    if (restaurants.length === 0) {

        container.innerHTML = `
            <h2>No restaurants found</h2>
        `;
        return;
    }

    // CREATE RESTAURANT CARDS
    restaurants.forEach(function (restaurant) {

        const card =
            document.createElement("div");

        card.classList.add("restaurant-card");

        card.innerHTML = `

            <div class="restaurant-title">
                ${restaurant.Name}
            </div>

            <div class="restaurant-info">
                <strong>Rating:</strong>
                ⭐ ${restaurant.Rating}
            </div>

            <div class="restaurant-info">
                <strong>Address:</strong>
                ${restaurant.StreetNo} ${restaurant.StreetName}
            </div>

            <div class="restaurant-info">
                <strong>City:</strong>
                ${restaurant.City}
            </div>

            <div class="restaurant-info">
                <strong>Province:</strong>
                ${restaurant.Province}
            </div>

            <div class="restaurant-info">
                <strong>Country:</strong>
                ${restaurant.Country}
            </div>

        `;

        container.appendChild(card);

    });

}