<?php
    // include("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Register</title>
</head>

<body>

    <h1>Sign Up</h1>

    <!-- USER TYPE -->
    <select id="userType">

        <option value="">
            Select User Type
        </option>

        <option value="Customer">
            Customer
        </option>

        <option value="Agency">
            Agency
        </option>

    </select>

    <br><br>

    <!-- FORM -->
    <form id="registerForm">

        <!-- DYNAMIC FIELDS -->
        <div id="dynamicFields"></div>

        <button type="submit">
            Register
        </button>

    </form>

    <br>

    <!-- MESSAGE AREA -->
    <div id="message"></div>

    <script>

        // =========================
        // ELEMENTS
        // =========================
        const userType =
            document.getElementById("userType");

        const dynamicFields =
            document.getElementById("dynamicFields");

        const registerForm =
            document.getElementById("registerForm");

        const message =
            document.getElementById("message");


        // =========================
        // DYNAMIC FORM CHANGES
        // =========================
        userType.addEventListener(
            "change",
            function () {

                const type = this.value;

                // Clear old fields
                dynamicFields.innerHTML = "";
                 // CUSTOMER FORM
                if(type === "Customer") {

                    dynamicFields.innerHTML = `

                        <input
                            type="text"
                            id="firstName"
                            placeholder="First Name"
                            required
                        >

                        <br><br>

                        <input
                            type="text"
                            id="lastName"
                            placeholder="Last Name"
                            required
                        >

                        <br><br>

                        <input
                            type="text"
                            id="minit"
                            placeholder="Middle Initial"
                        >

                        <br><br>

                        <input
                            type="text"
                            id="idNumber"
                            placeholder="ID Number"
                            required
                        >

                        <br><br>

                        <input
                            type="text"
                            id="passport"
                            placeholder="Passport"
                            required
                        >

                        <br><br>

                        <input
                            type="text"
                            id="phoneNumber"
                            placeholder="Phone Number"
                            required
                        >

                        <br><br>

                        <input
                            type="email"
                            id="email"
                            placeholder="Email"
                            required
                        >

                        <br><br>

                        <input
                            type="password"
                            id="password"
                            placeholder="Password"
                            required
                        >

                        <br><br>

                    `;
                }

                // AGENCY FORM
                else if(type === "Agency") {

                    dynamicFields.innerHTML = `

                        <input
                            type="text"
                            id="agencyName"
                            placeholder="Agency Name"
                            required
                        >

                        <br><br>

                        <textarea
                            id="description"
                            placeholder="Description"
                            required
                        ></textarea>

                        <br><br>

                        <input
                            type="text"
                            id="phoneNumber"
                            placeholder="Phone Number"
                            required
                        >

                        <br><br>

                        <input
                            type="email"
                            id="email"
                            placeholder="Email"
                            required
                        >

                        <br><br>

                        <input
                            type="password"
                            id="password"
                            placeholder="Password"
                            required
                        >

                        <br><br>

                    `;
                }

            }
        );


        // =========================
        // FORM SUBMIT
        // =========================
        registerForm.addEventListener(
            "submit",
            function(e) {

                e.preventDefault();

                message.innerHTML = "";

                const type = userType.value;

                // Validate type
                if(!type) {

                    message.innerHTML =
                        "<span style='color:red'>" +
                        "Please select a user type" +
                        "</span>";

                    return;
                }

                let data = {};

                // =========================
                // CUSTOMER DATA
                // =========================
                if(type === "Customer") {
                    data = {
                        type : "Register",
                        user_type: "Customer",

                        first_name:
                            document
                            .getElementById("firstName")
                            .value
                            .trim(),

                        last_name:
                            document
                            .getElementById("lastName")
                            .value
                            .trim(),

                        minit:
                            document
                            .getElementById("minit")
                            .value
                            .trim(),

                        id_number:
                            document
                            .getElementById("idNumber")
                            .value
                            .trim(),

                        passport:
                            document
                            .getElementById("passport")
                            .value
                            .trim(),

                        phone_number:
                            document
                            .getElementById("phoneNumber")
                            .value
                            .trim(),

                        email:
                            document
                            .getElementById("email")
                            .value
                            .trim(),

                        password:
                            document
                            .getElementById("password")
                            .value
                    };
                }

                // =========================
                // AGENCY DATA
                // =========================
                else if(type === "Agency") {

                    data = {
                        type : "Register",
                        user_type: "Agency",

                        agency_name:
                            document
                            .getElementById("agencyName")
                            .value
                            .trim(),

                        description:
                            document
                            .getElementById("description")
                            .value
                            .trim(),


                        phone_number:
                            document
                            .getElementById("phoneNumber")
                            .value
                            .trim(),

                        email:
                            document
                            .getElementById("email")
                            .value
                            .trim(),

                        password:
                            document
                            .getElementById("password")
                            .value
                    };
                }

                // =========================
                // SEND TO API
                // =========================
                fetch("api.php", {

                    method: "POST",

                    headers: {
                        "Content-Type": "application/json"
                    },

                    body: JSON.stringify(data)

                })
                .then(async response => {

                    const result =
                        await response.json();

                    if(!response.ok) {
                        throw new Error(
                            result.message
                        );
                    }

                    return result;
                })
                .then(result => {

                    message.innerHTML =
                        "<span style='color:green'>" +
                        result.message +
                        "</span>";

                    registerForm.reset();

                    dynamicFields.innerHTML = "";

                })
                .catch(error => {

                    message.innerHTML =
                        "<span style='color:red'>" +
                        error.message +
                        "</span>";

                    console.error(error);

                });

            }
        );

    </script>

</body>
</html>