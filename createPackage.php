<?php

session_start();

// OPTIONAL:
// Redirect if not logged in
if(!isset($_SESSION['api_key'])) {

    die("You must be logged in.");
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Package</title>

</head>

<body>

    <div class="container">

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

        // =====================================
        // FORM SUBMIT
        // =====================================
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

                // =====================================
                // VALIDATION
                // =====================================
                if(
                    !packageName ||
                    !destination ||
                    !description ||
                    !price ||
                    !startDate ||
                    !endDate ||
                    !packageType
                ) {

                    message.innerHTML =
                        "<span class='error'>" +
                        "All fields are required" +
                        "</span>";

                    return;
                }

                // =====================================
                // CREATE DATA OBJECT
                // =====================================
                const data = {

                    type: "CreatePackage",

                    api_key: localStorage.getItem('api_key'),
                        
                    package_name:
                        packageName,

                    capicity: capacity,

                    description:
                        description,

                    price:
                        price,

                    start_date:
                        startDate,

                    end_date:
                        endDate,

                    package_type:
                        packageType
                };

                // SEND TO API
                fetch(
                    "api/api.php",

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
                    }

                    // ERROR
                    else {

                        message.innerHTML =
                            "<span class='error'>"
                            +
                            result.message
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

    </script>

</body>

</html>