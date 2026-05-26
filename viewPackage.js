const api_key = localStorage.getItem("api_key");
const pa
const container = document.getElementById("packageCard");

fetch("api.php", {

    method: "POST",

    headers: {
        "Content-Type": "application/json"
    },

    body: JSON.stringify({

        type: "GetPackageDetails",
        PackageID: packageID

    })

})
.then(res => res.json())

.then(data => {

    if(data.status !== "success"){

        container.innerHTML = data.data;

        return;
    }

    const pkg = data.data.package;

    const attractions = data.data.attractions;

    const accomodations = data.data.accomodations;

    container.innerHTML = `

        <h1>${pkg.Title}</h1>

        <img src="${pkg.ImageURL}" class="package-image">

        <p class="description">${pkg.Description}</p>

        <div class="section">

            <h2>Package Details</h2>

            <p><strong>Price:</strong> R ${pkg.Total_price}</p>

            <p><strong>Duration:</strong> ${pkg.Duration} Days</p>

            <p><strong>Type:</strong> ${pkg.PackageType}</p>

            <p><strong>Rating:</strong> ${pkg.Rating}</p>

        </div>

        <div class="section">

            <h2>Destination</h2>

            <p>${pkg.City}, ${pkg.Country}</p>

            <p>${pkg.DestinationDescription}</p>

        </div>

        <div class="section">

            <h2>Flight</h2>

            <p><strong>Airline:</strong> ${pkg.Airline}</p>

            <p><strong>From:</strong> ${pkg.DepartureAirport}</p>

            <p><strong>To:</strong> ${pkg.ArrivalAirport}</p>

        </div>

        <div class="section">

            <h2>Accommodations</h2>

            ${accomodations.map(a => `

                <div class="item">

                    <p><strong>${a.AccomodationName}</strong></p>

                    <p>${a.Type}</p>

                    <p>R ${a.CostPerNight}</p>

                    <p>⭐ ${a.Rating}</p>

                </div>

            `).join("")}

        </div>

        <div class="section">

            <h2>Attractions</h2>

            ${attractions.map(a => `

                <div class="item">

                    <p><strong>${a.AttractionName}</strong></p>

                    <p>⭐ ${a.Rating}</p>

                    <p>Adult Fee: R ${a.AdultsFee}</p>

                </div>

            `).join("")}

        </div>

    `;

});