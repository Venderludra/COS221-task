CREATE DATABASE tripistry_DB;
USE tripistry_DB;

CREATE TABLE Customer(
	CustomerID INT primary key auto_increment,
    PhoneNumber varchar(20) not null,
    Email varchar(100) unique not null,
    Passport varchar(30) not null,
    IDnumber varchar(30) not null,
    LastName varchar(76),
    FirstName varchar(75),
    hashedPassword varchar(125) not null,
    salt varchar(125) not null,
    Apikey varchar(125) not null,
    Minit varchar(1)
);

CREATE TABLE Agency(
	AgencyID INT primary key auto_increment,
    AgencyName varchar(125),
    Description varchar(255),
    ContactDetails varchar(125),
    EmailAddress varchar(125),
    PhoneNumber varchar(125),
    hashedPassword varchar(125) not null,
    salt varchar(125) not null,
    Apikey varchar(125) not null
);

CREATE TABLE Destination(
	DestinationID INT primary key auto_increment,
    Continent varchar(25) ,
    Country varchar(35),
    Province varchar(35),
    City varchar(35),
    Description TEXT
);

CREATE TABLE TravelPackage(
	PackageID INT primary key auto_increment,
    Description TEXT ,
    Total_price decimal(10,2),
    Capacity INT,
    Title varchar(125),
    Start_date date,
    End_date date,
    AgencyID INT not null,
    
    foreign key(AgencyID) references Agency(AgencyID) on delete restrict on update cascade
    
);

CREATE TABLE Booking(
	BookingID INT primary key auto_increment,
    NumberOfPeople INT not null,
    BookingDate date not null,
    Type varchar(112) not null,
    CustomerID INT not null,
    PackageID INT not null,
    
    foreign key(CustomerID) references Customer(CustomerID) on delete restrict on update cascade,
    foreign key(PackageID) references TravelPackage(PackageID) on delete restrict on update cascade
);

CREATE TABLE Flight(
	FlightID INT primary key auto_increment,
    Airline varchar(125),
    Price decimal(10,2) ,
    FlightDuration time ,
    DepartureAirport varchar(125),
    ArrivalAirport varchar(125),
    FlightNumber int ,
    DepartureTime time,
    DepartureDate date,
    PackageID int not null,
    
    foreign key(PackageID) references TravelPackage(PackageID) on delete restrict on update cascade
);

CREATE TABLE Review(
	ReviewNo INT primary key auto_increment,
    Message TEXT ,
    ReviewDate Date not null,
    CustomerID INT not null,
    
    foreign key(CustomerID) references Customer(CustomerID) on delete cascade on update cascade 
);

CREATE TABLE TravellerGroups(
	GroupID INT primary key auto_increment,
    NumberOfTravellers INT,
    CustomerID INT not null,
    
    foreign key(CustomerID) references Customer(CustomerID) on delete cascade on update cascade
);

CREATE TABLE Attraction(
	AttractionID INT primary key auto_increment,
    Longitude decimal(9,6),
    Latitude decimal(9,6),
    Rating decimal(2,1),
    StudentsFee decimal(10,2),
    AdultsFee decimal(10,2),
    kidsFee decimal(10,2),
    ElderlyFee decimal(10,2),
    DestinationID INT not null,
    
    foreign key(DestinationID) references Destination(DestinationID) on delete cascade on update cascade
    
);

CREATE TABLE Accomodation(
	AccomodationID INT primary key auto_increment,
    CostPerNight decimal(10,2),
    Rating decimal(2,1),
    ApartmentNo varchar(25),
    StreetNo varchar(25),
    Street varchar(56),
    Type varchar(56),
    WeekCost decimal(10,2),
    DestinationID INT not null,
    
    foreign key(DestinationID) references Destination(DestinationID) on delete cascade on update cascade
);

CREATE TABLE Restaurant(
	RestaurantID INT primary key auto_increment,
    Name varchar(125) not null,
    StreetNo varchar(125),
    StreetName varchar(125),
    Rating decimal(2,1),
    DestinationID INT not null,
    
    foreign key(DestinationID) references Destination(DestinationID) on delete cascade on update cascade
);

CREATE TABLE Includes(
	DestinationID INT not null,
    PackageID INT not null,
    
    primary key(DestinationID,PackageID),
    foreign key(DestinationID) references Destination(DestinationID) on delete restrict on update cascade,
    foreign key(PackageID) references TravelPackage(PackageID) on delete cascade on update cascade
);

CREATE TABLE Makes(
	CustomerID INT not null,
    BookingID INT not null,
    
    primary key(CustomerID,BookingID),
    foreign key(CustomerID) references Customer(CustomerID) on delete cascade on update cascade,
    foreign key(BookingID) references Booking(BookingID) on delete cascade on update cascade
);

CREATE TABLE Contains(
	FlightID INT not null,
    PackageID INT not null,
    
    primary key(FlightID,PackageID),
    foreign key(FlightID) references Flight(FlightID) on delete cascade on update cascade,
    foreign key(PackageID) references TravelPackage(PackageID) on delete cascade on update cascade
);

CREATE TABLE AgencyEmailContact(
	EmailAddress varchar(125) not null,
    AgencyID INT not null,
    
    primary key(EmailAddress,AgencyID),
    foreign key(AgencyID) references Agency(AgencyID) on delete cascade on update cascade
);

CREATE TABLE AgencyPhoneNumber(
	PhoneNumber varchar(125) not null,
    AgencyID INT not null,
    
    primary key(PhoneNumber,AgencyID),
    foreign key(AgencyID) references Agency(AgencyID) on delete cascade on update cascade
);

INSERT INTO Customer 
(PhoneNumber, Email, Passport, IDnumber, LastName, FirstName, hashedPassword, salt, Apikey, Minit)
VALUES 
(
    '0812345678',
    'john.doe@gmail.com',
    'P12345678',
    '9001015009087',
    'Doe',
    'John',
    'hashedpass123',
    'salt123',
    'apikey123',
    'A'
),
(
    '0823456789',
    'sarah.lee@gmail.com',
    'P98765432',
    '9202026009088',
    'Lee',
    'Sarah',
    'hashedpass456',
    'salt456',
    'apikey456',
    'B'
);

INSERT INTO Agency
(AgencyName, Description, ContactDetails, EmailAddress, PhoneNumber, hashedPassword, salt, Apikey)
VALUES
(
    'SkyHigh Travels',
    'International travel agency',
    '012 555 1000',
    'info@skyhigh.com',
    '0125551000',
    'agencyhash123',
    'agencysalt123',
    'agencyapikey123'
),
(
    'Explore World',
    'Budget travel packages',
    '011 222 3333',
    'contact@exploreworld.com',
    '0112223333',
    'agencyhash456',
    'agencysalt456',
    'agencyapikey456'
);

INSERT INTO Destination (Continent, Country, Province, City, Description)
VALUES
('Europe', 'France', 'Ile-de-France', 'Paris', 'City of lights and culture'),
('Asia', 'Japan', 'Kanto', 'Tokyo', 'Modern and traditional blend city');

INSERT INTO TravelPackage (Description, Total_price, Capacity, Title, Start_date, End_date, AgencyID)
VALUES
('Romantic Paris trip', 25000.00, 20, 'Paris Getaway', '2026-06-01', '2026-06-07', 1),
('Tokyo adventure package', 30000.00, 15, 'Tokyo Explorer', '2026-07-10', '2026-07-20', 2);

INSERT INTO Flight (Airline, Price, FlightDuration, DepartureAirport, ArrivalAirport, FlightNumber, DepartureTime, DepartureDate, PackageID)
VALUES
('Air France', 12000.00, '10:30:00', 'JNB', 'CDG', 101, '08:00:00', '2026-06-01', 1),
('Japan Airlines', 15000.00, '12:00:00', 'JNB', 'HND', 202, '09:00:00', '2026-07-10', 2);

INSERT INTO Booking (NumberOfPeople, BookingDate, Type, CustomerID, PackageID)
VALUES
(2, '2026-05-10', 'Online', 1, 1),
(1, '2026-05-11', 'Online', 2, 2);

INSERT INTO Review (Message, ReviewDate, CustomerID)
VALUES
('Amazing experience in Paris!', '2026-06-10', 1),
('Tokyo trip was unforgettable!', '2026-07-25', 2);

INSERT INTO TravellerGroups (NumberOfTravellers, CustomerID)
VALUES
(4, 1),
(2, 2);

INSERT INTO Attraction (Longitude, Latitude, Rating, StudentsFee, AdultsFee, kidsFee, ElderlyFee, DestinationID)
VALUES
(2.3522, 48.8566, 4.8, 100.00, 200.00, 80.00, 150.00, 1),
(139.6917, 35.6895, 4.7, 120.00, 250.00, 90.00, 180.00, 2);

INSERT INTO Accomodation (CostPerNight, Rating, ApartmentNo, StreetNo, Street, Type, WeekCost, DestinationID)
VALUES
(1800.00, 4.5, 'A12', '10', 'Champs Elysees', 'Hotel', 10000.00, 1),
(2200.00, 4.7, 'B22', '5', 'Shibuya Street', 'Hotel', 13000.00, 2);

INSERT INTO Restaurant (Name, StreetNo, StreetName, Rating, DestinationID)
VALUES
('Le Gourmet', '12', 'Rue Paris', 4.6, 1),
('Sakura Sushi', '8', 'Tokyo Central', 4.8, 2);

INSERT INTO Includes (DestinationID, PackageID)
VALUES
(1, 1),
(2, 2);

INSERT INTO Makes (CustomerID, BookingID)
VALUES
(1, 1),
(2, 2);

INSERT INTO Contains (FlightID, PackageID)
VALUES
(1, 1),
(2, 2);

INSERT INTO AgencyEmailContact (EmailAddress, AgencyID)
VALUES
('support@skyhigh.com', 1),
('help@exploreworld.com', 2);

INSERT INTO AgencyPhoneNumber (PhoneNumber, AgencyID)
VALUES
('0125551000', 1),
('0112223333', 2);


