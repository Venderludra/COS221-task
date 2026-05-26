<?php
// No session needed, but include navbar if you want
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Package</title>
    <link rel="stylesheet" href="css/createPackage.css">
    <link rel="stylesheet" href="css/AgencyNavBar.css">
</head>
<body>

<?php include("AgencyNavBar.php"); ?>

<div class="container">
    <div class="back-button">
        <a href="AgencyDashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    <h1>Create Travel Package</h1>
        <!-- PACKAGE FORM -->
        
        <form id="packageForm">

            <!-- PACKAGE NAME -->
            <input
                type="text"
                id="packageName"
                placeholder="Package Name"
                required
            >

            <!-- DESCRIPTION -->
            <textarea
                id="description"
                placeholder="Package Description"
                required
            ></textarea>

            <!-- PRICE -->
            <input
                type="number"
                id="price"
                placeholder="Price"
                min="0"
                step="0.01"
                required
            >

            <!-- START DATE -->
            <input
                type="date"
                id="startDate"
                required
            >

            <!-- END DATE -->
            <input
                type="date"
                id="endDate"
                required
            >

            <!--Capacity-->
            <input 
                type="number"
                id="capacity"
                placeholder="Capacity"
                required
            >
        
            <!-- PACKAGE TYPE -->
            <select id="packageType" required>

                <option value="">
                    Select Package Type
                </option>

                <option value="Adventure">
                    Adventure
                </option>

                <option value="Luxury">
                    Luxury
                </option>

                <option value="Family">
                    Family
                </option>

                <option value="Romantic">
                    Romantic
                </option>

            </select>

            <select id="destinations" required>
            </select>

            <select id="flights" required>
            </select>

            <select id="accomodations" required>
            </select>

            <select id="attractions" required>
            </select>

            <select id="restaurants" required>
            </select>

            <div class="radio-group">

                <p>Is this a Group Package?</p>

                <label>
                    <input
                        type="radio"
                        name="isGroupPackage"
                        value="1"
                        required
                    >
                    Yes
                </label>

                <label>
                    <input
                        type="radio"
                        name="isGroupPackage"
                        value="0"
                    >
                    No
                </label>

            </div>

            <div id="groupIDContainer" style="display:none;">

                <input
                    type="number"
                    id="groupID"
                    placeholder="Enter Group ID"
                    min="1"
                >

            </div>


            <!-- SUBMIT -->
            <button type="submit">
                Create Package
            </button>

        </form>

        <!-- MESSAGE -->
        <div
            id="message"
            class="message"
        ></div>

    </div>

    <script>
        const apiKey = localStorage.getItem("api_key");

        if (!apiKey) {

            alert("You must be logged in");

            window.location.href = "login.html";
        }

        const groupRadios =
        document.querySelectorAll(
            'input[name="isGroupPackage"]'
        );

        groupRadios.forEach(radio => {

        radio.addEventListener("change", function(){

            const groupContainer =
                document.getElementById("groupIDContainer");

            const groupInput =
                document.getElementById("groupID");

            // YES selected
            if(this.value === "1"){

                groupContainer.style.display = "block";

                groupInput.required = true;
            }

            // NO selected
            else{

                groupContainer.style.display = "none";

                groupInput.required = false;

                groupInput.value = "";
            }

        });

    });

        // FORM SUBMIT
        document
        .getElementById("packageForm")
        .addEventListener(
            "submit",

            function(e) {

                e.preventDefault();

                const message =
                    document.getElementById("message");

                // Clear message
                message.innerHTML = "";

                // =====================================
                // GET VALUES
                // =====================================
                const packageTitle =
                    document
                    .getElementById("packageName")
                    .value
                    .trim();

                const description =
                    document
                    .getElementById("description")
                    .value
                    .trim();

                const price =
                    document
                    .getElementById("price")
                    .value;

                //const Duration = document.getElementById("Duration").value;

                const startDate =
                    document
                    .getElementById("startDate")
                    .value;

                const capacity = 
                    document
                    .getElementById("capacity")
                    .value;
                    
                const endDate =
                    document
                    .getElementById("endDate")
                    .value;

                const packageType =
                    document
                    .getElementById("packageType")
                    .value;
                
                const flightID =
                    document
                    .getElementById("flights")
                    .value;
                
                const accomodationID =
                    document
                    .getElementById("accomodations")
                    .value; // Accomodation ID

                const destinationID = 
                    document
                    .getElementById("destinations")
                    .value; // Destination ID

                const attractionID = 
                    document
                    .getElementById("attractions")
                    .value; // Attraction ID

                const restaurantID =
                    document
                    .getElementById("restaurants")
                    .value; // Restaurant ID

                const selectedGroupPackage = 
                    document.querySelector('input[name="isGroupPackage"]:checked'); // Group Package

                const isGroupPackage = 
                    selectedGroupPackage ? selectedGroupPackage.value : null;

                const groupID =
                document.getElementById("groupID").value || null;

                // =====================================
                // VALIDATION
                // =====================================
                if(
                    !packageTitle ||
                    !description ||
                    !price ||
                    !startDate ||
                    !endDate ||
                    !packageType ||
                    !flightID ||
                    !capacity ||
                    !accomodationID ||
                    !destinationID ||
                    !attractionID ||
                    !restaurantID ||
                    isGroupPackage === undefined
                ) {

                    message.innerHTML =
                        "<span class='error'>" +
                        "All fields are required" +
                        "</span>";

                    return;
                }

                //date validation
                const start =
                    new Date(startDate);

                const end =
                    new Date(endDate);

                if (end < start) {

                    message.innerHTML =
                        "<span class='error'>" +
                        "End date cannot be before start date" +
                        "</span>";

                    return;
                }

                //group package validation
                if (isGroupPackage === "1" && !groupID) {

                    message.innerHTML =
                        "<span class='error'>" +
                        "Group ID is required for group packages" +
                        "</span>";

                    return;
                }


                // CREATE DATA OBJECT
                const data = {

                    type: "CreatePackage",

                    api_key: apiKey,
                        
                    package_name:
                        packageTitle,

                    Capacity: capacity,

                    description:
                        description,

                    duration: calculateDuration(),

                    price:
                        price,

                    start_date:
                        startDate,

                    end_date:
                        endDate,

                    package_type:
                        packageType,
                    
                    flight_id:
                        flightID,

                    accomodation_id:
                        accomodationID,

                    destination_id:
                        destinationID,
                    
                    attraction_id:
                        attractionID,

                    restaurant_id:
                        restaurantID,

                    is_group_package:
                        isGroupPackage,

                    group_id:
                        groupID
                };

                // SEND TO API
                fetch(
                    "api.php", //needs to be changed to persons who is demoing

                    {
                        method: "POST",
                        headers: {

                            "Content-Type":
                                "application/json"
                        },
                        body: JSON.stringify(data)
                    }
                )

                .then(response => response.json())

                .then(result => {

                    // SUCCESS
                    if(
                        result.status === "success"
                    ) {

                        message.innerHTML =

                            "<span class='success'>"
                            +
                            "Package created successfully"
                            +
                            "</span>";

                        // Reset form
                        document
                        .getElementById("packageForm")
                        .reset();

                        //hide group ID field
                        document
                        .getElementById("groupIDContainer")
                        .style.display = "none";
                    }

                    // ERROR
                    else {

                        message.innerHTML =
                            "<span class='error'>"
                            +
                            result.data
                            +
                            "</span>";
                    }

                }).catch(error => {
                    console.error(error);
                    message.innerHTML =
                        "<span class='error'>"
                        +
                        "Server error"
                        +
                        "</span>";
                });

            }
        );

        // LOAD FLIGHTS
        loadFlights();
        function loadFlights(){

            fetch(

                "api.php",

                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify({
                        type: "GetFlights",
                        api_key: apiKey
                    })
                }

            )

            .then(response => response.json())

            .then(result => {

                const flightSelect =
                    document.getElementById("flights");

                // Clear current options
                flightSelect.innerHTML =

                    `<option value="">
                        Select Flight Option
                    </option>`;

                // SUCCESS
                if(result.status === "success"){

                    result.data.forEach(flight => {

                        const option =
                            document.createElement("option");

                        option.value =
                            flight.FlightID;

                        option.textContent =

                            `${flight.Airline} | ` +

                            `${flight.DepartureAirport} → ` +

                            `${flight.ArrivalAirport} | ` +

                            `${flight.DepartureDate} ` +

                            `${flight.DepartureTime}`;

                        flightSelect.appendChild(option);

                    });

                }

                // ERROR
                else{

                    console.error(result.data);

                }
            })

            .catch(error => {

                console.error(error);

            });

        }


        loadDestinations(); 
        function loadDestinations(){
            fetch(
    
                    "api.php",
    
                    {
                        method: "POST",
    
                        headers: {
                            "Content-Type": "application/json"
                        },
    
                        body: JSON.stringify({
                            type: "GetDestinations",
                            api_key: apiKey
                        })
                    }
    
                )
    
                .then(response => response.json())
    
                .then(result => {
    
                    const destinationSelect =
                        document.getElementById("destinations");
    
                    // Clear current options
                    destinationSelect.innerHTML =
    
                        `<option value="">
                            Select Destination Option
                        </option>`;
    
                    // SUCCESS
                    if(result.status === "success"){
    
                        result.data.forEach(destination => {
    
                            const option =
                                document.createElement("option");
    
                            option.value =
                                destination.DestinationID;
    
                            option.textContent =
    
                                `${destination.City} | ` +
    
                                `${destination.Country}`;
    
                            destinationSelect.appendChild(option);
    
                        });
    
                    }
    
                    // ERROR
                    else{
    
                        console.error(result.data);
    
                    }
                })
    
                .catch(error => {
    
                    console.error(error);
                });
        }

        document
        .getElementById("destinations")
        .addEventListener("change", function(){

            const destinationID = this.value;

            if(destinationID){

                loadAccomodations(destinationID);
                loadRestaurants(destinationID);
                loadAttractions(destinationID);

            }
        });

        
        function loadAccomodations(destinationID){
                fetch(
    
                    "api.php",
    
                    {
                        method: "POST",
    
                        headers: {
                            "Content-Type": "application/json"
                        },
    
                        body: JSON.stringify({
                            type: "GetAccomodations",
                            api_key: apiKey,
                            destination_id: destinationID
                        })
                    }
    
                )
                .then(response => response.json())
                .then(result => {
    
                    const accomodationSelect =
                        document.getElementById("accomodations");
    
                    // Clear current options
                    accomodationSelect.innerHTML =
    
                        `<option value="">
                            Select Accomodation Option
                        </option>`;
    
                    // SUCCESS
                    if(result.status === "success"){
    
                        result.data.forEach(accomodation => {
    
                            const option =
                                document.createElement("option");
    
                            option.value =
                                accomodation.AccomodationID;

                            option.textContent =
                                `${accomodation.AccomodationName} | `+
                                `${accomodation.StreetNo} ${accomodation.Street}` 
                                ;
    
                            accomodationSelect.appendChild(option);
    
                        });   
                    }
    
                    // ERROR
                    else{
    
                        console.error(result.data);
    
                    }
                })
    
                .catch(error => {
    
                    console.error(error);
                });
        }

        function loadRestaurants(destinationID){
            fetch(
    
                    "api.php",
    
                    {
                        method: "POST",
    
                        headers: {
                            "Content-Type": "application/json"
                        },
    
                        body: JSON.stringify({
                            type: "GetRestaurants",
                            api_key: apiKey,
                            destination_id: destinationID
                        })
                    }
    
                )
    
                .then(response => response.json())
    
                .then(result => {
    
                    const restaurantSelect =
                        document.getElementById("restaurants");
    
                    // Clear current options
                    restaurantSelect.innerHTML =
    
                        `<option value="">
                            Select Restaurant Option
                        </option>`;
    
                    // SUCCESS
                    if(result.status === "success"){
    
                        result.data.forEach(restaurant => {
    
                            const option =
                                document.createElement("option");
    
                            option.value =
                                restaurant.RestaurantID;
    
                            option.textContent =
    
                                `${restaurant.Name} | ` +
    
                                `${restaurant.StreetNo} ${restaurant.StreetName}`;
    
                            restaurantSelect.appendChild(option);
    
                        });
    
                    }
    
                    // ERROR
                    else{
    
                        console.error(result.data);
    
                    }
                })
    
                .catch(error => {
    
                    console.error(error);
                });
        }

        function loadAttractions(destinationID){
            fetch(
    
                    "api.php",
    
                    {
                        method: "POST",
    
                        headers: {
                            "Content-Type": "application/json"
                        },
    
                        body: JSON.stringify({
                            type: "GetAttractions",
                            api_key: apiKey,
                            destination_id: destinationID
                        })
                    }
    
                )
    
                .then(response => response.json())
    
                .then(result => {
    
                    const attractionSelect =
                        document.getElementById("attractions");
    
                    // Clear current options
                    attractionSelect.innerHTML =
    
                        `<option value="">
                            Select Attraction Option
                        </option>`;
    
                    // SUCCESS
                    if(result.status === "success"){
    
                        result.data.forEach(attraction => {
    
                            const option =
                                document.createElement("option");
    
                            option.value =
                                attraction.AttractionID;
    
                            option.textContent =
    
                                `${attraction.AttractionName}` ;

                            attractionSelect.appendChild(option);
    
                        });
    
                    }
    
                    // ERROR
                    else{
    
                        console.error(result.data);
    
                    }
                })
    
                .catch(error => {
    
                    console.error(error);
                });
        }

        function calculateDuration(){
            /*
            const start = document.getElementById("startDate").value;
            const end = document.getElementById("endDate").value;

            if(start && end){
                const startDate = new Date(start);
                const endDate = new Date(end);

                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                return diffDays;
            }
            */
            const start =
            document.getElementById("startDate").value;

            const end =
                document.getElementById("endDate").value;

            if (start && end) {

                const startDate =
                    new Date(start);

                const endDate =
                    new Date(end);

                // INVALID RANGE
                if (endDate < startDate) {
                    return null;
                }

                const diffTime =
                    endDate - startDate;

                const diffDays =
                    Math.ceil(
                        diffTime /
                        (1000 * 60 * 60 * 24)
                    );

                return diffDays;
            }

            return null;
        }   

    </script>

</body>

</html>