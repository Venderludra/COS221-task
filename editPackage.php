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
    <link rel="stylesheet" href="css/createPackage.css">
    <link rel="stylesheet" href="css/AgencyNavBar.css">
</head>

<body>
<?php include("AgencyNavBar.php"); ?>

<div class="container">

    <h2>Edit Travel Package</h2>

    <form id="editForm">

        <input type="text" id="packageName" placeholder="Package Name">

        <textarea id="description" placeholder="Description"></textarea>

        <input type="number" id="price" placeholder="Price">

        <input type="date" id="startDate">

        <input type="date" id="endDate">

        <input type="number" id = 'capacity' placeholder="capacity">

        <select id="packageType">
            <option value="Adventure">Adventure</option>
            <option value="Luxury">Luxury</option>
            <option value="Family">Family</option>
            <option value="Romantic">Romantic</option>
        </select>

        <select id="flights">
        </select>

        <select id="destinations">
        </select>

        <select id="accomodations">
        </select>

        <select id="attractions">
        </select>

        <select id="restaurants">
        </select>

        <!-- GROUP PACKAGE -->
        <div class="radio-group">

            <p>Is this a Group Package?</p>

            <label class="radio-option">

                <input
                    type="radio"
                    name="isGroupPackage"
                    value="1"
                >

                <span>Yes</span>

            </label>

            <label class="radio-option">

                <input
                    type="radio"
                    name="isGroupPackage"
                    value="0"
                >

                <span>No</span>

            </label>

        </div>

        <!-- GROUP ID -->
        <div id="groupIDContainer" style="display:none;">

            <input
                type="number"
                id="groupID"
                placeholder="Enter Group ID"
                min="1"
            >

        </div>

        <button type="submit">Update Package</button>

    </form>

    <div class="msg" id="msg"></div>

</div>

<script>

const packageID = "<?php echo $packageID; ?>";
const apiKey = localStorage.getItem("api_key");
const msg = document.getElementById("msg");

if(!apiKey){
    alert("Please login first");
    window.location.href = "login.php";
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

        // YES
        if(this.value === "1"){

            groupContainer.style.display = "block";

            groupInput.required = true;
        }

        // NO
        else{

            groupContainer.style.display = "none";

            groupInput.required = false;

            groupInput.value = "";
        }

    });

});

// ======================================
// LOAD PACKAGE DETAILS
// ======================================
async function loadPackage(){

    try{
        const response = await fetch("api.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                type: "GetPackageByID",
                api_key: apiKey,
                PackageID: packageID
            })
        });

        //.then(res => res.json())
        //.then(data => {

            const data = await response.json();

            if(data.status !== "success"){
                msg.innerHTML =
                `<span class="error">${data.data}</span>`;

                return;
            }

            const p = data.data;

            await loadFlights();
            await loadDestinations();

            if(p.DestinationID){
                await loadAccomodations(p.DestinationID);
                await loadRestaurants(p.DestinationID);
                await loadAttractions(p.DestinationID);
            }
        
           // ======================================
        // SET VALUES
        // ======================================
        document.getElementById("packageName").value =
            p.Title;

        document.getElementById("description").value =
            p.Description;

        document.getElementById("price").value =
            p.Total_price;

        document.getElementById("capacity").value =
            p.Capacity;

        document.getElementById("startDate").value =
            p.Start_date;

        document.getElementById("endDate").value =
            p.End_date;

        document.getElementById("packageType").value =
            p.PackageType;

        // ======================================
        // SET SELECT VALUES
        // ======================================
        document.getElementById("flights").value =
            p.FlightID;

        document.getElementById("destinations").value =
            p.DestinationID;

        document.getElementById("accomodations").value =
            p.AccomodationID;

        document.getElementById("attractions").value =
            p.AttractionID;

        document.getElementById("restaurants").value =
            p.RestaurantID;
        
        
        // ======================================
        // GROUP PACKAGE
        // ======================================
        if(p.GroupID){

                document.querySelector(
                    'input[name="isGroupPackage"][value="1"]'
                ).checked = true;

                document.getElementById(
                    "groupIDContainer"
                ).style.display = "block";

                document.getElementById(
                    "groupID"
                ).required = true;

                document.getElementById(
                    "groupID"
                ).value = p.GroupID;
        }

        else{
            document.querySelector(
                'input[name="isGroupPackage"][value="0"]'
            ).checked = true;
        }

        }
    catch(error){
        console.error(error);
        msg.innerHTML =
        `<span class="error">An error occurred while loading package details.</span>`;
    }
}

// UPDATE PACKAGE
/*
    document.getElementById("editForm")
    .addEventListener("submit", function(e){

        e.preventDefault();

        fetch("api.php", {
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

                Duration : calculateDuration(),

                start_date: document.getElementById("startDate").value,

                end_date: document.getElementById("endDate").value,

                package_type: document.getElementById("packageType").value,

                flight_id:
                    document.getElementById("flights").value,

                destination_id:
                    document.getElementById("destinations").value,

                accomodation_id:
                    document.getElementById("accomodations").value,

                attraction_id:
                    document.getElementById("attractions").value,

                restaurant_id:
                    document.getElementById("restaurants").value
            })
        })

        .then(res => res.json())
        .then(data => {

            if(data.status === "success"){
                msg.innerHTML = "Package updated successfully!";
                setTimeout(function(){
                    window.location.href = "agencyDashboard.php";
                }, 2000);
            } else {
                msg.innerHTML = data.data;
            }

        });

    });
*/
document
.getElementById("editForm")
.addEventListener("submit", function(e){

    e.preventDefault();

    // ======================================
    // GET VALUES
    // ======================================
    const packageName =
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

    const startDate =
        document
        .getElementById("startDate")
        .value;

    const endDate =
        document
        .getElementById("endDate")
        .value;

    const capacity =
        document
        .getElementById("capacity")
        .value;

    const packageType =
        document
        .getElementById("packageType")
        .value;

    const flightID =
        document
        .getElementById("flights")
        .value;

    const destinationID =
        document
        .getElementById("destinations")
        .value;

    const accomodationID =
        document
        .getElementById("accomodations")
        .value;

    const attractionID =
        document
        .getElementById("attractions")
        .value;

    const restaurantID =
        document
        .getElementById("restaurants")
        .value;

    const selectedGroupPackage =
        document.querySelector(
            'input[name="isGroupPackage"]:checked'
        );

    const isGroupPackage =
        selectedGroupPackage
        ? selectedGroupPackage.value
        : null;

    const groupID =
        document
        .getElementById("groupID")
        .value || null;

    // ======================================
    // VALIDATION
    // ======================================
    
    if(
        !packageName ||
        !description ||
        !price ||
        !startDate ||
        !endDate ||
        !capacity ||
        !packageType ||
        !flightID ||
        !destinationID ||
        !accomodationID ||
        !attractionID ||
        !restaurantID ||
        isGroupPackage === null

    ){

        msg.innerHTML =
            `<span class="error">
                All fields are required
            </span>`;

        return;
    }
    

    // DATE VALIDATION
    const start =
        new Date(startDate);

    const end =
        new Date(endDate);

    if(end < start){

        msg.innerHTML =
            `<span class="error">
                End date cannot be before start date
            </span>`;

        return;
    }

    // GROUP VALIDATION
    if(
        isGroupPackage === "1"
        &&
        !groupID
    ){

        msg.innerHTML =
            `<span class="error">
                Group ID is required
            </span>`;

        return;
    }

    // ======================================
    // SEND REQUEST
    // ======================================
    fetch("api.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/json"
        },

        body: JSON.stringify({

            type: "EditPackage",

            api_key: apiKey,

            PackageID: packageID,

            package_name: packageName,

            description: description,

            price: price,

            Capacity: capacity,

            duration: calculateDuration(),

            start_date: startDate,

            end_date: endDate,

            package_type: packageType,

            flight_id: flightID,

            destination_id: destinationID,

            accomodation_id: accomodationID,

            attraction_id: attractionID,

            restaurant_id: restaurantID,

            is_group_package: isGroupPackage,

            group_id: groupID
        })
    })

    .then(res => res.json())

    .then(data => {

        if(data.status === "success"){

            msg.innerHTML =
                `<span class="success">
                    Package updated successfully!
                </span>`;

            setTimeout(function(){

                window.location.href =
                    "AgencyDashboard.php";

            }, 2000);
        }

        else{

            msg.innerHTML =
                `<span class="error">
                    ${data.data}
                </span>`;
        }

    })

    .catch(error => {

        console.error(error);

        msg.innerHTML =
            `<span class="error">
                Server error
            </span>`;
    });

});

// LOAD FLIGHTS
        loadFlights();
        async function loadFlights(){

            const response = await fetch(

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
        });

        const result = await response.json();

        const flightSelect =
            document.getElementById("flights");

        flightSelect.innerHTML =

            `<option value="">
                Select Flight Option
            </option>`;

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
    }

        //loadDestinations(); 
        async function loadDestinations(){
            const response = await fetch("api.php",
                    {
                        method: "POST",
    
                        headers: {
                            "Content-Type": "application/json"
                        },
    
                        body: JSON.stringify({
                            type: "GetDestinations",
                            api_key: apiKey
                        })
                    });
            const result = await response.json();
            
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

        //loadAccomodations();
        async function loadAccomodations(destinationID){
                const response = await fetch(
    
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
                    });

                const result = await response.json();

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
                                `${accomodation.AccomodationName} | ` +
                                `${accomodation.StreetNo} ${accomodation.Street}` ;
                            accomodationSelect.appendChild(option);
                        });
                    }
                    // ERROR
                    else{
                        console.error(result.data);
                    }
        }

        //loadRestaurants();
        async function loadRestaurants(destinationID){
            const response = await fetch("api.php",
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
                    });
            const result = await response.json();
            
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
        }
    
        //loadAttractions();
        async function loadAttractions(destinationID){
            const response = await fetch(
    
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
                    });

            const result = await response.json();
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
                            `${attraction.AttractionName}`;
                        attractionSelect.appendChild(option);
                    });
                }
                // ERROR
                else{
                    console.error(result.data);
                }
    }
        
        function calculateDuration(){

            const start =
                document.getElementById("startDate").value;

            const end =
                document.getElementById("endDate").value;

            if(start && end){

                const startDate =
                    new Date(start);

                const endDate =
                    new Date(end);

                if(endDate < startDate){

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

// Load on page start
loadPackage();

</script>

</body>
</html>
