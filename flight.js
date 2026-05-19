document.addEventListener("DOMContentLoaded", loadFlights);

// ======================================
// LOAD FLIGHTS
// ======================================
function loadFlights() {

    const apiURL = "http://localhost/COS221/api.php"; //may change depending on the person's localhost directory

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
        type: "GetAllFlights",
        api_key: apiKey
    };

    // ======================================
    // CREATE REQUEST
    // ======================================
    const xhr = new XMLHttpRequest();

    xhr.open("POST", apiURL, true);

    xhr.setRequestHeader(
        "Content-Type",
        "application/json"
    );

    // ======================================
    // HANDLE RESPONSE
    // ======================================
    xhr.onreadystatechange = function () {

        if (xhr.readyState === 4) {

            if (xhr.status === 200) {

                const response =
                    JSON.parse(xhr.responseText);

                if (response.status === "success") {

                    printFlights(response.data);

                } else {

                    alert(response.data);

                }

            } else {

                alert("Failed to connect to API");

            }

        }

    };

    // ======================================
    // SEND REQUEST
    // ======================================
    xhr.send(JSON.stringify(requestData));

}


// ======================================
// PRINT FLIGHTS
// ======================================
function printFlights(flights) {

    const container =
        document.getElementById("flight-container");

    container.innerHTML = "";

    // ======================================
    // NO FLIGHTS
    // ======================================
    if (flights.length === 0) {

        container.innerHTML = `
            <h2>No flights found</h2>
        `;

        return;
    }

    // ======================================
    // CREATE FLIGHT CARDS
    // ======================================
    flights.forEach(function (flight) {

        const card =
            document.createElement("div");

        card.classList.add("flight-card");

        card.innerHTML = `

            <div class="flight-title">
                ${flight.Airline}
            </div>

            <div class="flight-info">
                <strong>Flight Number:</strong>
                ${flight.FlightNumber}
            </div>

            <div class="flight-info">
                <strong>Package:</strong>
                ${flight.PackageName}
            </div>

            <div class="flight-info">
                <strong>Departure:</strong>
                ${flight.DepartureAirport}
            </div>

            <div class="flight-info">
                <strong>Arrival:</strong>
                ${flight.ArrivalAirport}
            </div>

            <div class="flight-info">
                <strong>Departure Date:</strong>
                ${flight.DepartureDate}
            </div>

            <div class="flight-info">
                <strong>Departure Time:</strong>
                ${flight.DepartureTime}
            </div>

            <div class="flight-info">
                <strong>Flight Duration:</strong>
                ${flight.FlightDuration}
            </div>

            <div class="flight-info">
                <strong>Trip Duration:</strong>
                ${flight.Duration}
            </div>

            <div class="flight-info">
                <strong>Type:</strong>
                ${flight.PackageType}
            </div>

            <div class="flight-price">
                R${flight.Price}
            </div>

        `;

        container.appendChild(card);

    });

}