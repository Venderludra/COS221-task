<?php
set_exception_handler(function(Throwable $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'data' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

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

            case 'GetAgencyPackagesByID':
                $this->GetAgencyPackagesByID($input);
                break;

            case 'GetFlights':
                $this->getFlights($input);
                break;

            case 'CreatePackage':
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

            case 'EditFlight':
                $this->editFlight($input);
                break;

            case 'DeleteFlight':
                $this->deleteFlight($input);
                break;

            case 'EditRestaurant':
                $this->editRestaurant($input);
                break;

            case 'DeleteRestaurant':
                $this->deleteRestaurant($input);
                break;
            
            case 'GetRestaurantByID':
                $this->getRestaurantByID($input);
                break;

            case 'GetRestaurantsNoFilter':
                $this->getRestaurantsNoFilter($input);
                break;
            
            case 'GetAccomodations':
                $this->getAccomodations($input);
                break;
            
            case 'GetDestinations':
                $this->getDestinations($input);
                break;
            
            case 'GetRestaurants':
                $this->getRestaurants($input);
                break;
            
            case 'GetAttractions':
                $this->getAttractions($input);
                break;
            
            case 'Register':
                $this->handleRegister($input);
                break;
            
            case 'GetAllAgencies':
                $this->handleGetAllAgencies($input);
                break;
            
            case 'GetAgencyPackages':
                $this->handleGetAgencyPackages($input);
                break;
            case 'GetPackageDetails':
                $this->handleGetPackageDetails($input);
                break;
            case 'BookPackage':
                $this->handleBookPackage($input);
                break;
            case 'BookingDetails':
                $this->getBookingDetails($input);
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

    private function handleBookPackage($input){
        // Require valid API key 
        if (!isset($input['apikey']) || empty($input['apikey'])) {
            $this->sendError('Login required to book', 401);
            return;
        }
        $customerId = $this->getCustomerIdFromApiKey($input['apikey']);
        if (!$customerId) {
            $this->sendError('Invalid API key', 401);
            return;
        }

        $packageId = (int)($input['package_id'] ?? 0);
        $numberOfPeople = (int)($input['number_of_people'] ?? 1);
        if ($packageId <= 0 || $numberOfPeople <= 0) {
            $this->sendError('Package ID and number of people are required', 400);
            return;
        }

        // Get package capacity and title
        $stmt = $this->db->prepare("SELECT Capacity, Title FROM TravelPackage WHERE PackageID = ?");
        $stmt->bind_param("i", $packageId);
        $stmt->execute();
        $result = $stmt->get_result();
        $package = $result->fetch_assoc();
        $stmt->close();

        if (!$package) {
            $this->sendError('Package not found', 404);
            return;
        }

        // Calculate already booked people for this package
        $checkStmt = $this->db->prepare("SELECT SUM(NumberOfPeople) as total_booked FROM Booking WHERE PackageID = ?");
        $checkStmt->bind_param("i", $packageId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $booked = $checkResult->fetch_assoc()['total_booked'] ?? 0;
        $checkStmt->close();

        if ($booked + $numberOfPeople > $package['Capacity']) {
            $available = $package['Capacity'] - $booked;
            $this->sendError("Only $available spots left. You requested $numberOfPeople.", 400);
            return;
        }

        // Insert booking (no Type column)
        $bookingDate = date('Y-m-d');
        $stmt = $this->db->prepare("INSERT INTO Booking (NumberOfPeople, BookingDate, CustomerID, PackageID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isii", $numberOfPeople, $bookingDate, $customerId, $packageId);
        if ($stmt->execute()) {
            $bookingId = $stmt->insert_id;
            $stmt->close();
            $this->sendSuccess(['message' => 'Booking successful', 'booking_id' => $bookingId]);
        } else {
            $this->sendError('Booking failed: ' . $stmt->error, 500);
        }
    }

    private function getCustomerIdFromApiKey($apiKey){
        $stmt = $this->db->prepare("SELECT CustomerID FROM Customer WHERE Apikey = ?");
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? $row['CustomerID'] : null;
    }

    private function handleGetPackageDetails($input){
        $packageId = (int)($input['package_id'] ?? 0);
        if ($packageId <= 0) {
            $this->sendError('Package ID required', 400);
            return;
        }

        // 1. Package info + agency name
        $pkgStmt = $this->db->prepare("
            SELECT p.*, a.AgencyName 
            FROM TravelPackage p
            JOIN Agency a ON p.AgencyID = a.AgencyID
            WHERE p.PackageID = ?
        ");
        $pkgStmt->bind_param("i", $packageId);
        $pkgStmt->execute();
        $pkgResult = $pkgStmt->get_result();
        $package = $pkgResult->fetch_assoc();
        if (!$package) {
            $this->sendError('Package not found', 404);
            return;
        }
        $pkgStmt->close();

        // 2. Flights for this package
        $flightStmt = $this->db->prepare("SELECT * FROM Flight WHERE PackageID = ?");
        $flightStmt->bind_param("i", $packageId);
        $flightStmt->execute();
        $flightResult = $flightStmt->get_result();
        $flights = $flightResult->fetch_all(MYSQLI_ASSOC);
        $flightStmt->close();

        // 3. Destinations (via Includes) and related data (accommodations, restaurants, attractions)
        $destStmt = $this->db->prepare("
            SELECT d.DestinationID, d.City, d.Country, d.Continent, d.Description
            FROM Includes i
            JOIN Destination d ON i.DestinationID = d.DestinationID
            WHERE i.PackageID = ?
        ");
        $destStmt->bind_param("i", $packageId);
        $destStmt->execute();
        $destResult = $destStmt->get_result();
        $destinations = [];
        while ($dest = $destResult->fetch_assoc()) {
            $destId = $dest['DestinationID'];

            // Accommodations
            $accStmt = $this->db->prepare("SELECT * FROM Accomodation WHERE DestinationID = ?");
            $accStmt->bind_param("i", $destId);
            $accStmt->execute();
            $accResult = $accStmt->get_result();
            $accommodations = $accResult->fetch_all(MYSQLI_ASSOC);
            $accStmt->close();

            // Restaurants
            $restStmt = $this->db->prepare("SELECT * FROM Restaurant WHERE DestinationID = ?");
            $restStmt->bind_param("i", $destId);
            $restStmt->execute();
            $restResult = $restStmt->get_result();
            $restaurants = $restResult->fetch_all(MYSQLI_ASSOC);
            $restStmt->close();

            // Attractions
            $attrStmt = $this->db->prepare("SELECT * FROM Attraction WHERE DestinationID = ?");
            $attrStmt->bind_param("i", $destId);
            $attrStmt->execute();
            $attrResult = $attrStmt->get_result();
            $attractions = $attrResult->fetch_all(MYSQLI_ASSOC);
            $attrStmt->close();

            $dest['accommodations'] = $accommodations;
            $dest['restaurants'] = $restaurants;
            $dest['attractions'] = $attractions;
            $destinations[] = $dest;
        }
        $destStmt->close();

        // 4. Reviews for this package
        $reviewStmt = $this->db->prepare("
            SELECT r.Rating, r.Message, r.ReviewDate, c.FirstName, c.LastName
            FROM Review r
            JOIN Customer c ON r.CustomerID = c.CustomerID
            WHERE r.PackageID = ?
            ORDER BY r.ReviewDate DESC
        ");
        $reviewStmt->bind_param("i", $packageId);
        $reviewStmt->execute();
        $reviewResult = $reviewStmt->get_result();
        $reviews = $reviewResult->fetch_all(MYSQLI_ASSOC);
        $reviewStmt->close();

        // 5. Get total booked people for capacity display
        $bookedStmt = $this->db->prepare("SELECT SUM(NumberOfPeople) as total_booked FROM Booking WHERE PackageID = ?");
        $bookedStmt->bind_param("i", $packageId);
        $bookedStmt->execute();
        $bookedResult = $bookedStmt->get_result();
        $totalBooked = $bookedResult->fetch_assoc()['total_booked'] ?? 0;
        $bookedStmt->close();

        $response = [
            'package' => $package,
            'flights' => $flights,
            'destinations' => $destinations,
            'reviews' => $reviews,
            'total_booked' => $totalBooked
        ];
        $this->sendSuccess($response);
    }

    private function handleGetAgencyPackages($input) {
        // Require API key
        if (!isset($input['apikey']) || empty($input['apikey'])) {
            $this->sendError('API key is required', 401);
            return;
        }
        
        $apiKey = $input['apikey'];
        
        // Validate API key and get user type and ID
        $userInfo = $this->getUserFromApiKey($apiKey);
        if (!$userInfo) {
            $this->sendError('Invalid API key', 401);
            return;
        }
        
        $userType = $userInfo['type'];
        $userId = $userInfo['id'];
        
        // Base query – added LEFT JOIN to grouppackage
        $sql = "SELECT p.PackageID, p.Title, p.Description, p.Total_price, p.Capacity,
                    p.Start_date, p.End_date, p.ImageURL, p.Duration, p.PackageType,
                    a.AgencyName, a.AgencyID,
                    d.City, d.Country,
                    COALESCE(AVG(r.Rating), 0) AS Rating,
                    COUNT(r.Rating) AS ReviewCount
                FROM TravelPackage p
                JOIN Agency a ON p.AgencyID = a.AgencyID
                JOIN Includes i ON p.PackageID = i.PackageID
                JOIN Destination d ON i.DestinationID = d.DestinationID
                LEFT JOIN Review r ON p.PackageID = r.PackageID
                LEFT JOIN grouppackage gp ON p.PackageID = gp.PackageID
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Agency filter
        if ($userType === 'agency') {
            $sql .= " AND a.AgencyID = ?";
            $params[] = $userId;
            $types .= "i";
        }
        if (isset($input['agency_id']) && !empty($input['agency_id']) && $userType === 'traveller') {
            $sql .= " AND a.AgencyID = ?";
            $params[] = (int)$input['agency_id'];
            $types .= "i";
        }
        
        // Destination filter
        if (!empty($input['destination'])) {
            $sql .= " AND d.City LIKE ?";
            $params[] = "%" . $input['destination'] . "%";
            $types .= "s";
        }
        
        // Price filters
        if (isset($input['min_price']) && is_numeric($input['min_price'])) {
            $sql .= " AND p.Total_price >= ?";
            $params[] = (float)$input['min_price'];
            $types .= "d";
        }
        if (isset($input['max_price']) && is_numeric($input['max_price'])) {
            $sql .= " AND p.Total_price <= ?";
            $params[] = (float)$input['max_price'];
            $types .= "d";
        }
        
        // Duration filters
        if (isset($input['min_duration']) && is_numeric($input['min_duration'])) {
            $sql .= " AND p.Duration >= ?";
            $params[] = (int)$input['min_duration'];
            $types .= "i";
        }
        if (isset($input['max_duration']) && is_numeric($input['max_duration'])) {
            $sql .= " AND p.Duration <= ?";
            $params[] = (int)$input['max_duration'];
            $types .= "i";
        }
        
        //Package type filter (group / individual)
        if (isset($input['package_type_filter'])) {
            if ($input['package_type_filter'] === 'group') {
                $sql .= " AND gp.PackageID IS NOT NULL";
            } elseif ($input['package_type_filter'] === 'individual') {
                $sql .= " AND gp.PackageID IS NULL";
            }
        }
        
        $sql .= " GROUP BY p.PackageID";
        
        // Rating filter (HAVING)
        if (isset($input['min_rating']) && is_numeric($input['min_rating'])) {
            $sql .= " HAVING Rating >= ?";
            $params[] = (float)$input['min_rating'];
            $types .= "d";
        }
        
        // Sorting
        $allowedSort = ['price_asc', 'price_desc', 'duration_asc', 'duration_desc', 'rating_desc'];
        $sort = isset($input['sort']) && in_array($input['sort'], $allowedSort) ? $input['sort'] : 'rating_desc';
        switch ($sort) {
            case 'price_asc': $sql .= " ORDER BY p.Total_price ASC"; break;
            case 'price_desc': $sql .= " ORDER BY p.Total_price DESC"; break;
            case 'duration_asc': $sql .= " ORDER BY p.Duration ASC"; break;
            case 'duration_desc': $sql .= " ORDER BY p.Duration DESC"; break;
            default: $sql .= " ORDER BY Rating DESC";
        }
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->sendError('Database error: ' . $this->db->error, 500);
            return;
        }
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $packages = [];
        while ($row = $result->fetch_assoc()) {
            $row['Total_price'] = (float)$row['Total_price'];
            $row['Rating'] = round((float)$row['Rating'], 1);
            $row['ReviewCount'] = (int)$row['ReviewCount'];
            $packages[] = $row;
        }
        $stmt->close();
        
        $this->sendSuccess($packages);
    }

    private function getUserFromApiKey($apiKey) {
        // Check Customer table
        $stmt = $this->db->prepare("SELECT CustomerID AS id, 'traveller' AS type FROM Customer WHERE Apikey = ?");
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        $stmt->close();

        // Check Agency table
        $stmt = $this->db->prepare("SELECT AgencyID AS id, 'agency' AS type FROM Agency WHERE Apikey = ?");
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $stmt->close();
            return $row;
        }
        return null;
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
            //echo "Debug: Preparing query for Customer login";
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
    
    // Handle GetAllDestinations
    private function handleDestination($input) {
        $api_key = $input['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }
            
        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
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
        $api_key = $input['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }
            
        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
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
                //$row['AttractionName'] = $this->generateAttractionName($row);
                $attractions[] = $row;
            }
            
            $stmt->close();
            $this->sendSuccess($attractions);
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    //Nico's endpoints start here and i use api_key not apikey   
    private function GetAgencyPackagesByID($data){
        $api_key = $data['api_key'] ?? null ;
        
        if(empty($api_key)){
            $this->sendError("User is not Logged in");
            return;
        }

        //check if agency exists or not 
        $query = "SELECT AgencyID FROM Agency WHERE Apikey = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s',$api_key);

        $stmt->execute();
        $result = $stmt->get_result();

        //check if the user exists
        if($result->num_rows == 0){
            $this->sendError("Agency does not exist within our database");
            return;
        }

        $row = $result->fetch_assoc();

        //agencyID
        $agencyID = $row['AgencyID'];

        //retrieve all packages related/associated with an agency
        $packageQuery = "
                SELECT 
                PackageID,
                Description, 
                Title, 
                Start_date , 
                End_date, 
                PackageType,
                Capacity,
                Total_price

                FROM TravelPackage
                WHERE AgencyID = ?
        ";

        //execute query
        $stmt = $this->db->prepare($packageQuery);
        $stmt->bind_param("i",$agencyID);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows == 0){
            $this->sendError("No packages found for this agency");
            return;
        }

        $packages = [];

        while($row = $result->fetch_assoc()){
            $packages[] = $row;
        }

        $this->sendSuccess($packages);
        return;

    }

    private function editPackage($data){

        // =========================================
        // API KEY CHECK
        // =========================================
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
    
        $stmt = $this->db->prepare($query);
    
        $stmt->bind_param(
            "s",
            $api_key
        );
    
        $stmt->execute();
    
        $result = $stmt->get_result();
    
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
        $PackageID = $data['PackageID'] ?? null;
    
        $package_name =
            trim($data['package_name'] ?? '');
    
        $description =
            trim($data['description'] ?? '');
    
        $price =
            $data['price'] ?? null;
    
        $duration =
            $data['duration'] ?? null;
    
        $capacity =
            $data['Capacity'] ?? null;
    
        $start_date =
            $data['start_date'] ?? null;
    
        $end_date =
            $data['end_date'] ?? null;
    
        $package_type =
            trim($data['package_type'] ?? '');
    
        // NEW FIELDS
        $DestinationID =
            $data['destination_id'] ?? null;
    
        $FlightID =
            $data['flight_id'] ?? null;
    
        $isGroupPackage =
            $data['is_group_package'] ?? 0;
    
        $GroupID =
            $data['group_id'] ?? null;
    
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
                empty($duration) ||
                empty($DestinationID) ||
                empty($FlightID)
            ){
        
                $this->sendError(
                    "All required fields must be filled",
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
    
        $stmt = $this->db->prepare(
            $ownershipQuery
        );
    
        $stmt->bind_param(
            "ii",
            $PackageID,
            $AgencyID
        );
    
        $stmt->execute();
    
        $result = $stmt->get_result();
    
        if($result->num_rows == 0){
    
            $this->sendError(
                "Package not found or access denied",
                403
            );
    
            return;
        }
    
        // =========================================
        // START TRANSACTION
        // =========================================
        $this->db->begin_transaction();
    
        try{
    
            // =========================================
            // UPDATE TRAVEL PACKAGE
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
    
            $stmt = $this->db->prepare(
                $updateQuery
            );
    
            $stmt->bind_param(
    
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
    
            $stmt->execute();
    
            // =========================================
            // UPDATE DESTINATION
            // =========================================
            $query = "
                UPDATE includes
                SET DestinationID = ?
                WHERE PackageID = ?
            ";
    
            $stmt = $this->db->prepare($query);
    
            $stmt->bind_param(
                "ii",
                $DestinationID,
                $PackageID
            );
    
            $stmt->execute();
    
            // =========================================
            // UPDATE FLIGHT
            // =========================================
            $query = "
                UPDATE contains
                SET FlightID = ?
                WHERE PackageID = ?
            ";
    
            $stmt = $this->db->prepare($query);
    
            $stmt->bind_param(
                "ii",
                $FlightID,
                $PackageID
            );
    
            $stmt->execute();
    
            // =========================================
            // HANDLE GROUP PACKAGE
            // =========================================
    
            // REMOVE OLD GROUP ENTRY
            $query = "
                DELETE FROM grouppackage
                WHERE PackageID = ?
            ";
    
            $stmt = $this->db->prepare($query);
    
            $stmt->bind_param(
                "i",
                $PackageID
            );
    
            $stmt->execute();
    
            // =========================================
            // INSERT NEW GROUP ENTRY
            // =========================================
            if($isGroupPackage == 1){
    
                if(empty($GroupID)){
    
                    $this->db->rollback();
    
                    $this->sendError(
                        "GroupID required for group package",
                        400
                    );
    
                    return;
                }
    
                $query = "
                    INSERT INTO grouppackage
                    (PackageID, GroupID)
                    VALUES (?, ?)
                ";
    
                $stmt = $this->db->prepare($query);
    
                $stmt->bind_param(
                    "ii",
                    $PackageID,
                    $GroupID
                );
    
                $stmt->execute();
            }
    
            // =========================================
            // COMMIT
            // =========================================
            $this->db->commit();
    
            $this->sendSuccess(
                "Package updated successfully"
            );
    
            return;
    
        }catch(Exception $e){
    
            $this->db->rollback();
    
            $this->sendError(
                $e->getMessage(),
                500
            );
    
            return;
        }
    }

    private function getPackageByID($data){

        // =========================================
        // API KEY CHECK
        // =========================================
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
            FROM agency
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
        // FETCH PACKAGE
        // =========================================
        $query = "
            SELECT 
                tp.PackageID,
                tp.Title,
                tp.Description,
                tp.Total_price,
                tp.Capacity,
                tp.Start_date,
                tp.End_date,
                tp.ImageURL,
                tp.Duration,
                tp.Rating,
                tp.PackageType,
                tp.AgencyID,
    
                i.DestinationID,
    
                c.FlightID,
    
                gp.GroupID,
    
                CASE
                    WHEN gp.PackageID IS NOT NULL THEN 1
                    ELSE 0
                END AS isGroupPackage
    
            FROM travelpackage tp
    
            LEFT JOIN includes i
                ON tp.PackageID = i.PackageID
    
            LEFT JOIN contains c
                ON tp.PackageID = c.PackageID
    
            LEFT JOIN grouppackage gp
                ON tp.PackageID = gp.PackageID
    
            WHERE tp.PackageID = ?
            AND tp.AgencyID = ?
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
    
            $this->sendError(
                "Package not found or access denied",
                404
            );
    
            return;
        }
    
        $package = $result->fetch_assoc();
    
        // =========================================
        // SEND RESPONSE
        // =========================================
        $this->sendSuccess($package);
    
        return;
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

        // CHECK IF PACKAGE HAS BOOKINGS
        $bookingCheck = "
            SELECT BookingID
            FROM Booking
            WHERE PackageID = ?
        ";

        $stmt = $this->db->prepare($bookingCheck);
        $stmt->bind_param("i", $PackageID);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){

            $this->sendError(
                "Cannot delete package because bookings exist",
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
        /*
        // DISASSOCIATE FLIGHTS
        $disassociateQuery = "
            UPDATE flight
            SET PackageID = NULL
            WHERE PackageID = ?";

        $stmt = $this->db->prepare($disassociateQuery);
        $stmt->bind_param("i", $PackageID);
        $stmt->execute();
        */

        // DELETE PACKAGE
        $deleteQuery = "
            DELETE FROM TravelPackage
            WHERE PackageID = ?
            AND AgencyID = ?
        ";

        //Disassociate includes relation
        $disassociateIncludes = "
            DELETE FROM includes
            WHERE PackageID = ?
        ";

        $stmt = $this->db->prepare($disassociateIncludes);
        $stmt->bind_param("i", $PackageID);
        $stmt->execute();

        //disassociate contains relation
        $disassociateContains = "
            DELETE FROM contains
            WHERE PackageID = ?
        ";

        $stmt = $this->db->prepare($disassociateContains);
        $stmt->bind_param("i", $PackageID);
        $stmt->execute();

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

    private function getFlights($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $flightQuery = "
        SELECT *
        FROM flight
        ";
    
        $smst = $this->db->prepare($flightQuery);
        $smst->execute();
    
        $result = $smst->get_result();
        $flights = [];


        while($row = $result->fetch_assoc()){
             $flights[] = $row;
        }
        
        if(empty($flights)){
            $this->sendError("No available flights found",404);
            return;
        }

        $this->sendSuccess($flights);
        return;
        
    }

    private function getRestaurantByID($data){
        $api_key = $data['api_key'] ?? null ;
        
        if(empty($api_key)){
            $this->sendError("User is not Logged in");
            return;
        }

        //check if agency exists or not 
        $query = "SELECT AgencyID FROM Agency WHERE Apikey = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s',$api_key);

        $stmt->execute();
        $result = $stmt->get_result();

        //check if the user exists
        if($result->num_rows == 0){
            $this->sendError("Agency does not exist within our database");
            return;
        }

        //$row = $result->fetch_assoc();


        $restaurantID = $data['restaurant_id'] ?? null;
    
        if(!$restaurantID){
            $this->sendError("Restaurant ID required");
            return;
        }
    
        $query = "
            SELECT *
            FROM Restaurant
            WHERE RestaurantID = ?
        ";
    
        $stmt = $this->db->prepare($query);
    
        $stmt->execute($restaurantID);
    
        $result = $stmt->get_result();

        $restaurant = [];
        while($row = $result->fetch_assoc()){
             $restaurant[] = $row;
        }

        if(empty($restaurant)){
            $this->sendError("Restaurant not found",404);
            return;
        }

        $this->sendSuccess($restaurant);
        return;

        
    }

    private function deleteFlight($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $flightID = $data['flight_id'] ?? null;

        if(empty($flightID)){
            $this->sendError("FlightID is required",400);
            return;
        }

        // Check if flight is associated with any package
        $checkQuery = "
            SELECT PackageID
            FROM contains
            WHERE FlightID = ?
        ";

        $stmt = $this->db->prepare($checkQuery);
        $stmt->bind_param("i", $flightID);
        $stmt->execute();

        $result = $stmt->get_result();

        if($result->num_rows > 0){
            $this->sendError("Cannot delete flight because it is associated with a package",400);
            return;
        }

        // Delete flight
        $deleteQuery = "
            DELETE FROM flight
            WHERE FlightID = ?
        ";

        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("i", $flightID);

        if($stmt->execute()){
            $this->sendSuccess("Flight deleted successfully");
            return;
        }

        // Error
        $this->sendError("Failed to delete flight",500);
        return;
    }

    private function editFlight($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $flightID = $data['FlightID'] ?? null;

        if(empty($flightID)){
            $this->sendError("FlightID is required",400);
            return;
        }

        $airline = ($data['Airline'] ?? null);
        $departureAirport = ($data['DepartureAirport'] ?? null);
        $arrivalAirport = ($data['ArrivalAirport'] ?? null);
        $flightNumber = ($data['FlightNumber'] ?? null);

        $price = $data['Price'] ?? null;
        $flightDuration = $data['FlightDuration'] ?? null;
        $departureDate = $data['DepartureDate'] ?? null;
        $dpartureTime = $data['DepartureTime'] ?? null;

        if(empty($airline) || empty($departureAirport) || empty($arrivalAirport)){
            $this->sendError("All fields are required",400);
            return;
        }

        // Update flight
        $updateQuery = "
            UPDATE flight
            SET Airline = ?, DepartureAirport = ?, ArrivalAirport = ?
            WHERE FlightID = ?
        ";

        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("sssi", $airline, $departureAirport, $arrivalAirport, $flightID);

        if($stmt->execute()){
            $this->sendSuccess("Flight updated successfully");
            return;
        }

        // Error
        $this->sendError("Failed to update flight",500);
        return;
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

            $flightID = $data['flight_id'] ?? null;

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

            $destinationID =
                $data['destination_id'] ?? null;

            $isGroupPackage = $data['is_group_package'] ?? null;

            $groupID = $data['group_id'] ?? null;

            // VALIDATION

            if(
                empty($package_name) ||
                empty($description) ||
                empty($price) ||
                empty($start_date) ||
                empty($end_date) ||
                empty($package_type)||
                empty($duration) ||
                empty($capacity) ||
                empty($flightID) ||
                empty($destinationID)
            ){

                $this->sendError("All fields are required",400);
                return;
            }

            //validate group package 
            if($isGroupPackage !== "0" && $isGroupPackage !== "1"){
                $this->sendError("Invalid Group Package value",400);
                return;
            }

            //if group package = yes then groupID is required
            if($isGroupPackage === "1" && empty($groupID)){
                $this->sendError("GroupID is required for group packages",400);
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
                "issdsssii",

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

            // EXECUTE PACKAGE INSERT

            //$packageID = $this->db->insert_id;

            if($smst->execute()){
                
                    $newPackageID = $this->db->insert_id;
                    if($isGroupPackage === "1"){
                        $groupQuery = "
                            INSERT INTO grouppackage(
                                GroupID,
                                PackageID
                            )
                            VALUES(?, ?)";

                        $stmt = $this->db->prepare($groupQuery);
                        $stmt->bind_param(
                            "ii",
                            $groupID,
                            $newPackageID
                        );
                        $stmt->execute();
                    }

                    // =====================================
                    // UPDATE FLIGHT
                    // =====================================

                    $containsQuery = "
                        INSERT INTO contains(
                            PackageID,
                            FlightID
                        )
                        VALUES(?, ?)
                    ";

                    $stmt3 = $this->db->prepare($containsQuery);

                    $stmt3->bind_param(
                        "ii",
                        $newPackageID,
                        $flightID
                    );

                    $stmt3->execute();

                    // =====================================
                    // INSERT DESTINATION RELATION
                    // =====================================

                    $includesQuery = "
                        INSERT INTO includes(
                            DestinationID,
                            PackageID
                        )
                        VALUES(?, ?)
                    ";

                    $stmt2 = $this->db->prepare($includesQuery);

                    $stmt2->bind_param(
                        "ii",
                        $destinationID,
                        $newPackageID
                    );

                    $stmt2->execute();

                    //insert Contains relations
                    

                    $this->sendSuccess(
                        "Package created successfully"
                    );

                    return;
            }

            // INSERT FAILED
            else{
                $this->sendError("Failed to create package",500);
                return;
            }
    }

    private function getPackageDetails($data){

            $packageID = $data['PackageID'] ?? null;

            if(!$packageID){

                $this->sendError("PackageID is required");

                return;
            }

            // GET PACKAGE

            $query = "

            SELECT

                tp.*,

                d.DestinationID,
                d.Country,
                d.City,
                d.Description AS DestinationDescription,

                f.Airline,
                f.DepartureAirport,
                f.ArrivalAirport

            FROM travelpackage tp

            LEFT JOIN includes i
                ON tp.PackageID = i.PackageID

            LEFT JOIN destination d
                ON i.DestinationID = d.DestinationID

            LEFT JOIN flight f
                ON tp.PackageID = f.PackageID

            WHERE tp.PackageID = ?

            ";

            $stmt = $this->db->prepare($query);

            $stmt->bind_param("i", $packageID);

            $stmt->execute();

            $result = $stmt->get_result();

            $package = $result->fetch_assoc();

            if(!$package){

                $this->sendError("Package not found");

                return;
            }

            $destinationID = $package['DestinationID'];

            // GET ACCOMMODATIONS

            $query = "

            SELECT *
            FROM accomodation
            WHERE DestinationID = ?

            ";

            $stmt = mysqli_prepare($this->db, $query);

            mysqli_stmt_bind_param($stmt, "i", $destinationID);

            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $accomodations = [];

            while($row = mysqli_fetch_assoc($result)){

                $accomodations[] = $row;
            }

            // =========================================
            // GET ATTRACTIONS
            // =========================================

            $query = "

            SELECT *
            FROM attraction
            WHERE DestinationID = ?

            ";

            $stmt = $this->db->prepare($query);

            $stmt->bind_param("i", $destinationID);

            $stmt->execute();

            $result = $stmt->get_result();

            $attractions = [];

            while($row = $result->fetch_assoc()){

                $attractions[] = $row;
            }

            $this->response = [

                "status" => "success",

                "data" => [

                    "package" => $package,
                    "accomodations" => $accomodations,
                    "attractions" => $attractions

                ]

            ];

            echo json_encode($this->response);
}

    private function getAccomodations($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
        $destinationID = $data['destination_id'] ?? null;

        $flightQuery = "
        SELECT 
            AccomodationID,
            AccomodationName,
            Type,
            CostPerNight,
            StreetNo,
            Street,
            Rating
        FROM Accomodation
        WHERE DestinationID = ?
        ";
    
        $smst = $this->db->prepare($flightQuery);
        $smst->bind_param("i", $destinationID);
        $smst->execute();
    
        $result = $smst->get_result();
        $accommodations = [];

        while($row = $result->fetch_assoc()){
             $accommodations[] = $row;
        }
        
        $this->sendSuccess($accommodations);
        return;
    }

    private function getDestinations($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $DestinationQuery = "
        SELECT 
            DestinationID,
            City,
            Country,
            Continent,
            Province
        FROM Destination
        ";
    
        $smst = $this->db->prepare($DestinationQuery);
        $smst->execute();
    
        $result = $smst->get_result();
        $destinations = [];

        while($row = $result->fetch_assoc()){
             $destinations[] = $row;
        }
        
        $this->sendSuccess($destinations);
        return;
    }

    private function getRestaurantsNoFilter($data){
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
            RestaurantID,
            Name,
            StreetNo,
            StreetName,
            Rating
        FROM Restaurant
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

    private function editRestaurant($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $restaurantID = $data['RestaurantID'] ?? null;

        if(empty($restaurantID)){
            $this->sendError("RestaurantID is required",400);
            return;
        }

        $name = ($data['Name'] ?? null);
        $streetNo = ($data['StreetNo'] ?? null);
        $streetName = ($data['StreetName'] ?? null);
        $rating = ($data['Rating'] ?? null);

        if(empty($name) || empty($streetNo) || empty($streetName)){
            $this->sendError("All fields are required",400);
            return;
        }

        // Update restaurant
        $updateQuery = "
            UPDATE Restaurant
            SET Name = ?, StreetNo = ?, StreetName = ?, Rating = ?
            WHERE RestaurantID = ?
        ";

        $stmt = $this->db->prepare($updateQuery);
        $stmt->bind_param("ssssi", $name, $streetNo, $streetName, $rating, $restaurantID);

        if($stmt->execute()){
            $this->sendSuccess("Restaurant updated successfully");
            return;
        }

        // Error
        $this->sendError("Failed to update restaurant",500);
        return;
    }

    private function deleteRestaurant($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }

        $restaurantID = $data['restaurant_id'] ?? null;

        if(empty($restaurantID)){
            $this->sendError("RestaurantID is required",400);
            return;
        }

        // Delete restaurant
        $deleteQuery = "
            DELETE FROM Restaurant
            WHERE RestaurantID = ?
        ";

        $stmt = $this->db->prepare($deleteQuery);
        $stmt->bind_param("i", $restaurantID);

        if($stmt->execute()){
            $this->sendSuccess("Restaurant deleted successfully");
            return;
        }

        // Error
        $this->sendError("Failed to delete restaurant",500);
        return;
    }

    private function getRestaurants($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
        $destinationID = $data['destination_id'] ?? null;
        $restaurantQuery = "
        SELECT 
            RestaurantID,
            Name,
            StreetNo,
            StreetName
        FROM Restaurant
        WHERE DestinationID = ?
        ";
    
        $smst = $this->db->prepare($restaurantQuery);
        $smst->bind_param("i", $destinationID);
        $smst->execute();
    
        $result = $smst->get_result();
        $restaurants = [];

        while($row = $result->fetch_assoc()){
             $restaurants[] = $row;
        }
        
        $this->sendSuccess($restaurants);
        return;
    }

    private function getAttractions($data){
        $api_key = $data['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }

        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
        $destinationID = $data['destination_id'] ?? null;

        $attractionQuery = "
        SELECT 
            AttractionID,
            AttractionName
        FROM Attraction
        WHERE 
            DestinationID = ?
        ";
    
        $smst = $this->db->prepare($attractionQuery);
        $smst->bind_param("i", $destinationID);
        $smst->execute();
    
        $result = $smst->get_result();
        $attractions = [];

        while($row = $result->fetch_assoc()){
             $attractions[] = $row;
        }
        
        $this->sendSuccess($attractions);
        return;
    }


    //Nico's section ends here


    // Handle GetAllAccommodations
    private function handleAccommodation($input) {
        $api_key = $input['api_key'] ?? null;

        if(empty($api_key)){
            $this->sendError("User is not logged in",401);
            return;
        }
            
        if(!($this->validateApiKey($api_key))){
            $this->sendError("User is not Registered",401);
            return;
        }
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

            $flightQuery = "
            SELECT 
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
            FROM flight f
            INNER JOIN TravelPackage tp ON f.PackageID = tp.PackageID
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
                $this->sendError("User is not Registered",400);
                return;
            }

            $row = $result->fetch_assoc();
            $CustomerID = $row['CustomerID'];

            $bookingQuery = "
                SELECT
                b.BookingID,
                b.NumberOfPeople,
                b.BookingDate,
                b.PackageID,
                tp.Title AS PackageName,
                tp.Total_price AS TotalPrice,
                tp.Start_date,
                tp.End_date,
                tp.PackageType
                FROM Booking b
                INNER JOIN TravelPackage tp ON b.PackageID = tp.PackageID
                WHERE b.CustomerID = ?
            ";
            
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
 
        private function getBookingDetails($data){
            $bookingid = $data["bookingId"] ?? null;
            if(empty($bookingid)){
            http_response_code(400);
            echo json_encode([
                "status"=>"error",
                "timestamp"=>round(microtime(true)*1000),
                "data"=> "Post parameters are missing"
            ]);
            exit;
        }
        $stmt = $this->db->prepare("SELECT b.BookingID,b.NumberOfPeople,b.BookingDate,b.CustomerID,b.PackageID
        from booking b
         where b.BookingID  = ?");
         $stmt->bind_param("i",$bookingid);
         $answer = $stmt->execute();
         if(!$answer){
            http_response_code(400);
            echo json_encode([
                "status"=>"error",
                "timestamp"=>round(microtime(true)*1000),
                "message"=>"retrieval query failed"
            ]);
            exit;
         }
           $userBooking = $stmt->get_result();
           $row=$userBooking->fetch_assoc();
           
        
        echo json_encode([
            "status"=>"success",
            "data"=>$row
        ]); exit;
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

try {
    $api = new API();
    $api->handleRequest();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'data' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
