<?php
// Set headers for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/includes/config.php';

class API {
    private $db;
    private $response;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->response = [];
    }
    
    // Main method to handle requests
    public function handleRequest() {
        // Get request method
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Only accept POST requests
        if ($method !== 'POST') {
            $this->sendError('Method not allowed. Only POST requests are accepted.', 405);
            return;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendError('Invalid JSON input', 400);
            return;
        }
        
        // Check for type parameter
        if (!isset($input['type'])) {
            $this->sendError('Missing parameter: type', 400);
            return;
        }
        
        // Route to appropriate handler
        switch ($input['type']) {
            case 'GetAllDestinations':
                $this->handleDestination($input);
                break;
            
            case "GetAllFlights":
                $this->handleAllFlights($input);
                break;

            case "GetAllBookings":
                $this->handleBookings($input);
                break;

            case "GetAllRestaurant":
                $this->handleAllRestaurant($input);
                break;
                
            case 'GetAllAttractions':           
                $this->handleAttraction($input);
                break;
            
            case 'GetAllAccommodations':
                $this->handleAccommodation($input);
                break;
            
            case 'Getlaunch':
                $this->handlelaunch($input);
                break;
            
            case 'Login':
                $this->handleLogin($input);
                break;

            case 'createPackage':
                $this->createPackage($input);
                break;
            case 'EditPackage':
                $this->editPackage($input);
                break;
            
            case 'GetPackageByID':
                $this->getPackageByID($input);
                return;
            
            case "DeletePackage":
                $this->deletePackage($input);
                break;

            case 'GetAllAgencies':
                $this->handleGetAllAgencies($input);
                break;

            case 'Register':
                $this->handleRegister($input);
                break;
            
            default:
                $this->sendError('Invalid type: ' . $input['type'], 400);
                break;
        }
    }

    // Send success response
    private function sendSuccess($data) {
        $this->response = [
            'status' => 'success',
            'data' => $data
        ];
        echo json_encode($this->response);
    }
    
    // Send error response
    private function sendError($message, $statusCode = 400) {
        http_response_code($statusCode);
        $this->response = [
            'status' => 'error',
            'data' => $message
        ];
        echo json_encode($this->response);
    }
    
    private function handleGetAllAgencies($input){
        if(isset($input['apikey']) && !empty($input['apikey'])){
            $valid=$this->validateApiKey($input['apikey']);
            if(!$valid){
                $this->sendError('Invalid API key',401);
                return;
            }
        }

        $stmt=$this->db->prepare("SELECT AgencyID,AgencyName,Description,EmailAddress,PhoneNumber FROM Agency");
        $stmt->execute();
        $result=$stmt->get_result();
        $agencies=[];
        while($row=$result->fetch_assoc()){
            $agencies[]=$row;
        }
        $stmt->close();
        $this->sendSuccess($agencies);
    }

    private function validateApiKey($apiKey){
        $stmt=$this->db->prepare("SELECT CustomerID FROM Customer WHERE Apikey=? UNION SELECT AgencyID FROM  Agency WHERE Apikey=?");
        $stmt->bind_param("ss",$apiKey,$apiKey);
        $stmt->execute();
        $result=$stmt->get_result();
        $exists=$result->num_rows>0;
        $stmt->close();
        return $exists;

    }

    private function handleLogin($input) {
        // Validate required fields
        if (!isset($input['email']) || !isset($input['password']) || !isset($input['role'])) {
            $this->sendError('Email, password and role are required', 400);
            return;
        }

        $email = trim($input['email']);
        $password = $input['password'];
        $role = trim($input['role']);

        if (!in_array($role, ['traveller', 'agency'])) {
            $this->sendError('Invalid role', 400);
            return;
        }

        if ($role === 'traveller') {
            // Query Customer table
            $stmt = $this->db->prepare("SELECT CustomerID, FirstName, Email, hashedPassword, salt, Apikey FROM Customer WHERE Email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $this->sendError('Invalid email or password', 401);
                return;
            }

            // Verify password
            $hashedInput = $this->secure_password($password, $user['salt']);
            if ($hashedInput !== $user['hashedPassword']) {
                $this->sendError('Invalid email or password', 401);
                return;
            }

            $this->sendSuccess([
                'api_key' => $user['Apikey'],
                'user_id' => $user['CustomerID'],
                'name'    => $user['FirstName'],
                'email'   => $user['Email'],
                'role'    => 'traveller'
            ]);
        } else { // agency
            $stmt = $this->db->prepare("SELECT AgencyID, AgencyName, EmailAddress, hashedPassword, salt, Apikey FROM Agency WHERE EmailAddress = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                $this->sendError('Invalid email or password', 401);
                return;
            }

            $hashedInput = $this->secure_password($password, $user['salt']);
            if ($hashedInput !== $user['hashedPassword']) {
                $this->sendError('Invalid email or password', 401);
                return;
            }

            $this->sendSuccess([
                'api_key' => $user['Apikey'],
                'user_id' => $user['AgencyID'],
                'name'    => $user['AgencyName'],
                'email'   => $user['EmailAddress'],
                'role'    => 'agency'
            ]);
        }
    }

    private function handleRegister($input) {
        // Validate user type
        if (!isset($input['user_type']) || !in_array($input['user_type'], ['Customer', 'Agency'])) {
            $this->sendError('Invalid or missing user_type', 400);
            return;
        }

        $userType = $input['user_type'];

        if ($userType === 'Customer') {
            // Required fields for Customer
            $required = ['first_name', 'last_name', 'id_number', 'passport', 'phone_number', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $this->sendError("Missing required field: $field", 400);
                    return;
                }
            }

            // Check if email already exists
            $checkStmt = $this->db->prepare("SELECT CustomerID FROM Customer WHERE Email = ?");
            $checkStmt->bind_param("s", $input['email']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                $this->sendError('Email already registered', 409);
                return;
            }
            $checkStmt->close();

            // Generate salt and hash password
            $salt = $this->salt();
            $hashedPassword = $this->secure_password($input['password'], $salt);
            
            // Generate API key
            $apiKey = bin2hex(random_bytes(16));
            
            
            // Insert into Customer table
            $stmt = $this->db->prepare("INSERT INTO Customer (FirstName, LastName, Minit, IDnumber, Passport, PhoneNumber, Email, hashedPassword, salt, Apikey) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $minit = $input['minit'] ?? null;

            $stmt->bind_param("ssssssssss", 
                $input['first_name'],
                $input['last_name'],
                $minit,                   
                $input['id_number'],
                $input['passport'],
                $input['phone_number'],
                $input['email'],
                $hashedPassword,
                $salt,
                $apiKey
            );

            
            if ($stmt->execute()) {
                $this->sendSuccess(['message' => 'Customer registered successfully', 'api_key' => $apiKey]);
            } else {
                $this->sendError('Registration failed: ' . $stmt->error, 500);
            }
            $stmt->close();

        } else { // Agency
            $required = ['agency_name', 'phone_number', 'email', 'password'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $this->sendError("Missing required field: $field", 400);
                    return;
                }
            }

            // Check if email already exists
            $checkStmt = $this->db->prepare("SELECT AgencyID FROM Agency WHERE EmailAddress = ?");
            $checkStmt->bind_param("s", $input['email']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            if ($checkResult->num_rows > 0) {
                $checkStmt->close();
                $this->sendError('Email already registered', 409);
                return;
            }
            $checkStmt->close();

            // Generate salt and hash password
            $salt = $this->salt();
            $hashedPassword = $this->secure_password($input['password'], $salt);
            
            // Generate API key
            $apiKey = bin2hex(random_bytes(16));

            // Insert into Agency table
            $stmt = $this->db->prepare("INSERT INTO Agency (AgencyName, Description, PhoneNumber, EmailAddress, hashedPassword, salt, Apikey) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $description = $input['description'] ?? '';
            $stmt->bind_param("sssssss", 
                $input['agency_name'],
                $description,
                $input['phone_number'],
                $input['email'],
                $hashedPassword,
                $salt,
                $apiKey
            );
            
            if ($stmt->execute()) {
                $this->sendSuccess(['message' => 'Agency registered successfully', 'api_key' => $apiKey]);
            } else {
                $this->sendError('Registration failed: ' . $stmt->error, 500);
            }
            $stmt->close();
        }
    }
    // Generate attraction name based on coordinates
    private function generateAttractionName($attraction) {
        $coordinateMap = [
            '2.3522,48.8566' => 'Eiffel Tower',
            '139.6917,35.6895' => 'Senso-ji Temple',
            '12.4964,41.9028' => 'Colosseum',
            '-74.0060,40.7128' => 'Statue of Liberty',
            '18.4241,-33.9249' => 'Table Mountain',
            '100.5018,13.7367' => 'Grand Palace',
            '151.2093,-33.8688' => 'Sydney Opera House',
            '2.1734,41.3851' => 'Sagrada Familia',
            '-43.1729,-22.9068' => 'Christ the Redeemer',
            '77.2090,28.6139' => 'Qutub Minar',
            '-0.1278,51.5074' => 'Big Ben',
            '-123.1207,49.2827' => 'Stanley Park',
            '31.2357,30.0444' => 'Pyramids of Giza',
            '55.2708,25.2048' => 'Burj Khalifa',
            '11.5820,48.1351' => 'Marienplatz',
            '116.4074,39.9042' => 'Great Wall of China'
        ];
        
        $key = round($attraction['Longitude'], 4) . ',' . round($attraction['Latitude'], 4);
        
        if (isset($coordinateMap[$key])) {
            return $coordinateMap[$key];
        }
        
        return $attraction['City'] . ' Attraction';
    }
    
    //edit package by ID
    private function editPackage($data){
   
            $api_key = $data['api_key'] ?? null;

            if(empty($api_key)){

                $this->sendError(
                    "User is not logged in",
                    401
                );

                return;
            }

            // =========================================
            // VALIDATE AGENCY
            // =========================================
            $query = "

                SELECT AgencyID
                FROM Agency
                WHERE Apikey = ?

            ";

            $smst = $this->db->prepare($query);

            $smst->bind_param(
                "s",
                $api_key
            );

            $smst->execute();

            $result = $smst->get_result();

            if($result->num_rows == 0){

                $this->sendError(
                    "Agency not registered",
                    401
                );

                return;
            }

            // =========================================
            // GET AGENCY ID
            // =========================================
            $row = $result->fetch_assoc();

            $AgencyID = $row['AgencyID'];

            // =========================================
            // GET PACKAGE DATA
            // =========================================
            $PackageID =
                $data['PackageID'] ?? null;

            $package_name =
                trim($data['package_name'] ?? '');

            $description =
                trim($data['description'] ?? '');

            $price =
                $data['price'] ?? null;

            $duration = $data['Duration'] ?? null;

            $capacity = $data['Capacity'] ?? null;

            $start_date =
                $data['start_date'] ?? null;

            $end_date =
                $data['end_date'] ?? null;

            $package_type =
                trim($data['package_type'] ?? '');

            // =========================================
            // VALIDATION
            // =========================================
            if(
                empty($PackageID) ||
                empty($package_name) ||
                empty($description) ||
                empty($price) ||
                empty($start_date) ||
                empty($end_date) ||
                empty($package_type) ||
                empty($capacity) ||
                empty($duration)
            ){

                $this->sendError(
                    "All fields are required",
                    400
                );

                return;
            }

            // =========================================
            // CHECK PACKAGE OWNERSHIP
            // =========================================
            $ownershipQuery = "

                SELECT PackageID
                FROM TravelPackage
                WHERE PackageID = ?
                AND AgencyID = ?

            ";

            $smst = $this->db->prepare(
                $ownershipQuery
            );

            $smst->bind_param(
                "ii",
                $PackageID,
                $AgencyID
            );

            $smst->execute();

            $result = $smst->get_result();

            if($result->num_rows == 0){

                $this->sendError(
                    "Package not found or access denied",
                    403
                );

                return;
            }

            // =========================================
            // UPDATE PACKAGE
            // =========================================
            $updateQuery = "

                UPDATE TravelPackage

                SET
                    Title = ?,
                    Description = ?,
                    Total_price = ?,
                    Start_date = ?,
                    End_date = ?,
                    PackageType = ?,
                    Capacity = ?,
                    Duration = ?

                WHERE PackageID = ?
                AND AgencyID = ?

            ";

            $smst = $this->db->prepare(
                $updateQuery
            );

            $smst->bind_param(

                "ssdsssiiii",

                $package_name,
                $description,
                $price,
                $start_date,
                $end_date,
                $package_type,
                $capacity,
                $duration,
                $PackageID,
                $AgencyID
            );

            // =========================================
            // EXECUTE UPDATE
            // =========================================
            if($smst->execute()){
                $this->sendSuccess( "Package updated successfully");
                return;
            }

            // =========================================
            // UPDATE FAILED
            // =========================================
            else{

                $this->sendError("Failed to update package",500);
                return;
            }

    }

    private function getPackageByID($data){

        // =========================================
        // API KEY CHECK
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        // =========================================
        // VALIDATE AGENCY
        // =========================================
        $query = "
            SELECT AgencyID
            FROM Agency
            WHERE Apikey = ?
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bind_param("s", $api_key);

        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $this->sendError(
                "Agency not registered",
                401
            );

            return;
        }

        $agency = $result->fetch_assoc();

        $AgencyID = $agency['AgencyID'];

        // =========================================
        // GET PACKAGE ID
        // =========================================
        $PackageID = $data['PackageID'] ?? null;

        if(empty($PackageID)){

            $this->sendError(
                "PackageID is required",
                400
            );

            return;
        }

        // =========================================
        // FETCH PACKAGE (ONLY OWNER CAN ACCESS)
        // =========================================
        $query = "
            SELECT *
            FROM TravelPackage
            WHERE PackageID = ?
            AND AgencyID = ?
        ";

        $stmt = $this->db->prepare($query);

        $stmt->bind_param(
            "ii",
            $PackageID,
            $AgencyID
        );

        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $this->sendError("Package not found or access denied",404);
            return;
        }

        $package = $result->fetch_assoc();

        $this->sendSuccess($package);
        return;
    }
        // Handle GetAllDestinations
        private function handleDestination($input) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM Destination");
                $stmt->execute();
                $result = $stmt->get_result();
                
                $destinations = [];
                while ($row = $result->fetch_assoc()) {
                    $destinations[] = $row;
                }
                
                $stmt->close();
                $this->sendSuccess($destinations);
            } catch (Exception $e) {
                $this->sendError($e->getMessage());
            }
        }
        
        // Handle GetAllAttractions
        private function handleAttraction($input) {
            try {
                // Check if specific destination filter is provided
                if (isset($input['DestinationID']) && !empty($input['DestinationID'])) {
                    $stmt = $this->db->prepare("SELECT a.*, d.City, d.Country, d.Continent, d.Province, d.Description as DestinationDescription 
                                            FROM Attraction a
                                            JOIN Destination d ON a.DestinationID = d.DestinationID
                                            WHERE a.DestinationID = ?");
                    $stmt->bind_param("i", $input['DestinationID']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    // No filter - get all attractions with destination info
                    $stmt = $this->db->prepare("SELECT a.*, d.City, d.Country, d.Continent, d.Province, d.Description as DestinationDescription 
                                            FROM Attraction a
                                            JOIN Destination d ON a.DestinationID = d.DestinationID");
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
                
                $attractions = [];
                while ($row = $result->fetch_assoc()) {
                    // Add generated attraction name
                    $row['AttractionName'] = $this->generateAttractionName($row);
                    $attractions[] = $row;
                }
                
                $stmt->close();
                $this->sendSuccess($attractions);
            } catch (Exception $e) {
                $this->sendError($e->getMessage());
            }
        }
        
        // Handle GetAllAccommodations
        private function handleAccommodation($input) {
            try {
                // Check if specific destination filter is provided
                if (isset($input['DestinationID']) && !empty($input['DestinationID'])) {
                    $stmt = $this->db->prepare("SELECT a.*, d.City, d.Country, d.Continent, d.Province 
                                            FROM Accomodation a
                                            JOIN Destination d ON a.DestinationID = d.DestinationID
                                            WHERE a.DestinationID = ?");
                    $stmt->bind_param("i", $input['DestinationID']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                } else {
                    // No filter - get all accommodations with destination info
                    $stmt = $this->db->prepare("SELECT a.*, d.City, d.Country, d.Continent, d.Province 
                                            FROM Accomodation a
                                            JOIN Destination d ON a.DestinationID = d.DestinationID");
                    $stmt->execute();
                    $result = $stmt->get_result();
                }
                
                $accommodations = [];
                while ($row = $result->fetch_assoc()) {
                    $accommodations[] = $row;
                }
                
                $stmt->close();
                $this->sendSuccess($accommodations);
            } catch (Exception $e) {
                $this->sendError($e->getMessage());
            }
        }
        
        // Handle Getlaunch
        private function handlelaunch($input) {
            try {
                // Get featured packages
                $stmt = $this->db->prepare("SELECT PackageID, Title, Description, Total_price, Capacity, Start_date, End_date 
                                            FROM TravelPackage 
                                            LIMIT 5");
                $stmt->execute();
                $result = $stmt->get_result();
                
                $packages = [];
                while ($row = $result->fetch_assoc()) {
                    $packages[] = $row;
                }
                
                $stmt->close();
                
                // Get total counts
                $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM Destination");
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $destinationCount = $countResult->fetch_assoc()['total'];
                $countStmt->close();
                
                $data = [
                    'message' => 'Tripistry API is running',
                    'featured_packages' => $packages,
                    'total_destinations' => $destinationCount
                ];
                
                $this->sendSuccess($data);
            } catch (Exception $e) {
                $this->sendError($e->getMessage());
            }
        }

        private function createPackage($data){
            $api_key = $data['api_key'] ?? null;

            //check if logged in
            if(empty($api_key)){
                $this->sendError("User not Logged in",401);
                return;
            }

            //api key validation
            $query = "
                SELECT AgencyID
                FROM Agency
                WHERE Apikey = ?
            ";

            $smst = $this->db->prepare($query);

            $smst->bind_param("s",$api_key);

            $smst->execute();

            $result = $smst->get_result();

            // Invalid API key
            if($result->num_rows == 0){
                $this->sendError("Agency not registered",401);
                return;
            }

            // GET AGENCY ID
            $row = $result->fetch_assoc();

            $AgencyID = $row['AgencyID'];


            // GET PACKAGE DATA
            $capacity = $data['Capacity'] ?? null;

            $package_name =
                trim($data['package_name'] ?? '');

            $description =
                trim($data['description'] ?? '');

            $price =
                $data['price'] ?? null;

            $duration = $data['duration'] ?? null;

            $start_date =
                $data['start_date'] ?? null;

            $end_date =
                $data['end_date'] ?? null;

            $package_type =
                trim($data['package_type'] ?? '');


            // VALIDATION

            if(
                empty($package_name) ||
                empty($description) ||
                empty($price) ||
                empty($start_date) ||
                empty($end_date) ||
                empty($package_type)||
                empty($duration) ||
                empty($capacity)
            ){

                $this->sendError("All fields are required",400);
                return;
            }

            // INSERT PACKAGE

            $insertQuery = "
                INSERT INTO TravelPackage(
                    AgencyID,
                    Title,
                    Description,
                    Total_price,
                    Start_date,
                    End_date,
                    PackageType,
                    Duration,
                    Capacity
                )

                VALUES(?, ?, ?, ?, ?, ?, ?, ? ,?)

            ";

            $smst = $this->db->prepare(
                $insertQuery
            );

            $smst->bind_param(

                "isssdssii",

                $AgencyID,
                $package_name,
                $description,
                $price,
                $start_date,
                $end_date,
                $package_type,
                $duration,
                $capacity
            );

            // EXECUTE INSERT
            if($smst->execute()){
                $this->sendSuccess("Insert Successful");
                return;
            }

            // INSERT FAILED
            else{

                $this->sendError("Failed to create package",500);
                return;
            }

    }

    private function deletePackage($data){


        // API KEY CHECK
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){

            $this->sendError(
                "User is not logged in",
                401
            );

            return;
        }

        // VALIDATE AGENCY
        $query = "
            SELECT AgencyID
            FROM Agency
            WHERE Apikey = ?
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $api_key);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $this->sendError(
                "Agency not registered",
                401
            );

            return;
        }

        $agency = $result->fetch_assoc();
        $AgencyID = $agency['AgencyID'];


        // GET PACKAGE ID
        $PackageID = $data['PackageID'] ?? null;

        if(empty($PackageID)){

            $this->sendError(
                "PackageID is required",
                400
            );

            return;
        }


        // CHECK OWNERSHIP
        $checkQuery = "
            SELECT PackageID
            FROM TravelPackage
            WHERE PackageID = ?
            AND AgencyID = ?
        ";

        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("ii", $PackageID, $AgencyID);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 0){

            $this->sendError("Package not found or access denied",403);
            return;
        }

        // DELETE PACKAGE
        $deleteQuery = "
            DELETE FROM TravelPackage
            WHERE PackageID = ?
            AND AgencyID = ?
        ";

        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("ii", $PackageID, $AgencyID);

        if($stmt->execute()){
            $this->sendSuccess("Package deleted successfully");
            return;
        }

        // ERROR
        $this->sendError("Failed to delete package",500);
        return;
}

    private function handleAllFlights($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        //retrieve all available flights
        $flightQuery = "SELECT 
            f.FlightID,
            f.Airline,
            f.Price,
            f.FlightDuration,
            f.DepartureAirport,
            f.ArrivalAirport,
            f.FlightNumber,
            f.DepartureTime,
            f.DepartureDate,
            tp.Title AS PackageName,

            tp.PackageType,
            tp.Duration

            FROM Flight f

            INNER JOIN TravelPackage tp
            ON f.PackageID = tp.PackageID
        ";

        $smst = $this->db->prepare($flightQuery);
        $smst->execute();
    
        $result = $smst->get_result();

        $flights = [];

        while($row = $result->fetch_assoc()){
            $flights[] = $row;
        }

        $this->sendSuccess($flights);
        return; 
    }

    private function handleBookings($data){
            $api_key = $data['api_key'] ?? null;

            if(empty($api_key)){
                $this->sendError("User is not logged in",401);
                return;
            }

            $query = "SELECT CustomerID FROM Customer WHERE Apikey = ?";


            $smst = $this->db->prepare($query);
            $smst->bind_param('s',$api_key);

            $smst->execute();
            $result = $smst->get_result();

            if($result->num_rows == 0){
                $this->sendError("User is not Registered",401);
                return;
            }

            $row = $result->fetch_assoc();
            $CustomerID = $row['CustomerID'];

            $bookingQuery = "
                SELECT
                b.BookingID,
                b.NumberOfPeople,
                b.BookingDate,
                b.Type,
                tp.Title AS PackageName,
                tp.Total_price As TotalPrice,
                tp.Start_date,
                tp.End_date

                FROM Booking b

                INNER JOIN TravelPackage tp
                ON b.PackageID = tp.PackageID

                WHERE b.CustomerID = ?";

            $smst = $this->db->prepare($bookingQuery);
            $smst->bind_param("i",$CustomerID);
            $smst->execute();
            $result = $smst->get_result();

            $bookings = [];

            while($row = $result->fetch_assoc()){
                $bookings[] = $row;

            }

            $this->sendSuccess($bookings);
            return;

    }

    private function handleAllRestaurant($data){
            $api_key = $data['api_key'] ?? null;

            if(empty($api_key)){
                $this->sendError("User is not logged in",401);
                return;
            }
            
            if(!($this->validateApiKey($api_key))){
                $this->sendError("User is not Registered",401);
                return;
            }

            $restaurantQuery = "
            
                SELECT
                    r.RestaurantID,
                    r.Name,
                    r.StreetNo,
                    r.StreetName,
                    r.Rating,
                    d.City,
                    d.Country,
                    d.Province

                FROM Restaurant r

                INNER JOIN Destination d
                ON r.DestinationID = d.DestinationID
            ";


            $smst = $this->db->prepare($restaurantQuery);
            $smst->execute();
            $result = $smst->get_result();

            $restaurants = [];
            while($row = $result->fetch_assoc()){
                $restaurants[] = $row;
            }

            $this->sendSuccess($restaurants);
            return; 
        }

    private function salt(){
            $randombytes = openssl_random_pseudo_bytes(16);

            //make it hex readeable
            $random = bin2hex($randombytes);
            return $random;
    }

    private function secure_password($password,$salt){
            $combinedPassword = $password . $salt;
            
            $hash = $combinedPassword;
            for($i = 0 ; $i < 100000 ; $i++){
                $hash = hash("sha256", $hash . $i);
            }
            return $hash;
    }
}

// Create and run API
$api = new API();
$api->handleRequest();
?>