document.addEventListener("DOMContentLoaded", loadBookings);

// ======================================
// LOAD BOOKINGS
// ======================================
function loadBookings() {

    const apiURL = "http://localhost/yourproject/api.php";

    // API key stored after login
    const apiKey = localStorage.getItem("api_key");

    if (!apiKey) {
        alert("User not logged in");
        return;
    }

    // ======================================
    // REQUEST DATA
    // ======================================
    const requestData = {
        type: "GetAllBookings",
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

                    printBookings(response.data);

                } else {

                    alert(response.data);

                }

            } else {

                alert("Failed to connect to API");

            }

        }

    };
    //send request
    xhr.send(JSON.stringify(requestData));

}


function printBookings(bookings) {

    const container =
        document.getElementById("booking-container");

    container.innerHTML = "";

    // ======================================
    // NO BOOKINGS
    // ======================================
    if (bookings.length === 0) {

        container.innerHTML = `
            <h2>No bookings found</h2>
        `;

        return;
    }

    // ======================================
    // CREATE CARDS
    // ======================================
    bookings.forEach(function (booking) {

        const card =
            document.createElement("div");

        card.classList.add("booking-card");

        card.innerHTML = `

            <div class="booking-title">
                ${booking.Title}
            </div>

            <div class="booking-info">
                <strong>Booking ID:</strong>
                #${booking.BookingID}
            </div>

            <div class="booking-info">
                <strong>Booking Date:</strong>
                ${booking.BookingDate}
            </div>

            <div class="booking-info">
                <strong>Travellers:</strong>
                ${booking.NumberOfPeople}
            </div>

            <div class="booking-info">
                <strong>Type:</strong>
                ${booking.Type}
            </div>

            <div class="booking-info">
                <strong>Price:</strong>
                R${booking.Total_price}
            </div>

            <div class="booking-info">
                <strong>Trip Start:</strong>
                ${booking.Start_date}
            </div>

            <div class="booking-info">
                <strong>Trip End:</strong>
                ${booking.End_date}
            </div>

        `;

        container.appendChild(card);

    });

}