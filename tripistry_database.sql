create database tripistry_DB;
use tripistry_DB;

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
    ImageURL VARCHAR(255) DEFAULT NULL,
    Duration INT NOT NULL,
    PackageType ENUM('Romantic','Adventure','Family','Luxury') NOT NULL,
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
    ReviewNo INT PRIMARY KEY AUTO_INCREMENT,
    Message TEXT,
    Rating INT NOT NULL CHECK (Rating BETWEEN 1 AND 5),
    ReviewDate DATE NOT NULL,
    CustomerID INT NOT NULL,
    PackageID INT NOT NULL,
    FOREIGN KEY (CustomerID) REFERENCES Customer(CustomerID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (PackageID) REFERENCES TravelPackage(PackageID) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE TravellerGroups(
	GroupID INT primary key auto_increment,
    NumberOfTravellers INT,
    CustomerID INT not null,
    
    foreign key(CustomerID) references Customer(CustomerID) on delete cascade on update cascade
);

CREATE TABLE Attraction(
	AttractionID INT primary key auto_increment,
    AttractionName VARCHAR(255) not null,
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

# Tripistry Database Sample Inserts


USE tripistry_DB;

-- =========================
-- CUSTOMER
-- =========================
INSERT INTO Customer (PhoneNumber, Email, Passport, IDnumber, LastName, FirstName, hashedPassword, salt, Apikey, Minit) VALUES
('0711111111','john1@gmail.com','P10001','8001015009087','Smith','John','hash1','salt1','api1','A'),
('0711111112','mary2@gmail.com','P10002','8101015009087','Johnson','Mary','hash2','salt2','api2','B'),
('0711111113','david3@gmail.com','P10003','8201015009087','Brown','David','hash3','salt3','api3','C'),
('0711111114','sarah4@gmail.com','P10004','8301015009087','Jones','Sarah','hash4','salt4','api4','D'),
('0711111115','mike5@gmail.com','P10005','8401015009087','Williams','Mike','hash5','salt5','api5','E'),
('0711111116','anna6@gmail.com','P10006','8501015009087','Taylor','Anna','hash6','salt6','api6','F'),
('0711111117','james7@gmail.com','P10007','8601015009087','Davis','James','hash7','salt7','api7','G'),
('0711111118','linda8@gmail.com','P10008','8701015009087','Miller','Linda','hash8','salt8','api8','H'),
('0711111119','kevin9@gmail.com','P10009','8801015009087','Wilson','Kevin','hash9','salt9','api9','I'),
('0711111120','emma10@gmail.com','P10010','8901015009087','Moore','Emma','hash10','salt10','api10','J'),
('0711111121','noah11@gmail.com','P10011','9001015009087','Clark','Noah','hash11','salt11','api11','K'),
('0711111122','mia12@gmail.com','P10012','9101015009087','Hall','Mia','hash12','salt12','api12','L'),
('0711111123','lucas13@gmail.com','P10013','9201015009087','Allen','Lucas','hash13','salt13','api13','M'),
('0711111124','ava14@gmail.com','P10014','9301015009087','Young','Ava','hash14','salt14','api14','N'),
('0711111125','liam15@gmail.com','P10015','9401015009087','King','Liam','hash15','salt15','api15','O'),
('0711111126','zoe16@gmail.com','P10016','9501015009087','Scott','Zoe','hash16','salt16','api16','P'),
('0711111127','ethan17@gmail.com','P10017','9601015009087','Green','Ethan','hash17','salt17','api17','Q'),
('0711111128','grace18@gmail.com','P10018','9701015009087','Adams','Grace','hash18','salt18','api18','R'),
('0711111129','daniel19@gmail.com','P10019','9801015009087','Baker','Daniel','hash19','salt19','api19','S'),
('0711111130','chloe20@gmail.com','P10020','9901015009087','Nelson','Chloe','hash20','salt20','api20','T'),
('0711111131','ryan21@gmail.com','P10021','0001015009087','Hill','Ryan','hash21','salt21','api21','U'),
('0711111132','ella22@gmail.com','P10022','0101015009087','Carter','Ella','hash22','salt22','api22','V'),
('0711111133','jack23@gmail.com','P10023','0201015009087','Mitchell','Jack','hash23','salt23','api23','W'),
('0711111134','ruby24@gmail.com','P10024','0301015009087','Perez','Ruby','hash24','salt24','api24','X'),
('0711111135','leo25@gmail.com','P10025','0401015009087','Roberts','Leo','hash25','salt25','api25','Y');

-- =========================
-- AGENCY
-- =========================
INSERT INTO Agency (AgencyName, Description, EmailAddress, PhoneNumber, hashedPassword, salt, Apikey) VALUES
('Safari Travels','Luxury safari packages','safari1@agency.com','0101000001','hash1','salt1','api1'),
('Ocean Escape','Beach holidays worldwide','ocean2@agency.com','0101000002','hash2','salt2','api2'),
('Mountain Adventures','Adventure tours','mountain3@agency.com','0101000003','hash3','salt3','api3'),
('Dream Vacations','Family vacation planners','dream4@agency.com','0101000004','hash4','salt4','api4'),
('Skyline Tours','City exploration tours','sky5@agency.com','0101000005','hash5','salt5','api5'),
('Elite Getaways','Luxury retreats','elite6@agency.com','0101000006','hash6','salt6','api6'),
('Africa Explore','African travel packages','africa7@agency.com','0101000007','hash7','salt7','api7'),
('Holiday Hub','Budget holiday packages','holiday8@agency.com','0101000008','hash8','salt8','api8'),
('Travel Masters','Corporate travel experts','masters9@agency.com','0101000009','hash9','salt9','api9'),
('Paradise Trips','Island adventures','paradise10@agency.com','0101000010','hash10','salt10','api10'),
('Global Journeys','International tours','global11@agency.com','0101000011','hash11','salt11','api11'),
('Sunset Escapes','Romantic escapes','sunset12@agency.com','0101000012','hash12','salt12','api12'),
('Wildlife Wonders','Wildlife experiences','wild13@agency.com','0101000013','hash13','salt13','api13'),
('Urban Adventures','Modern city tours','urban14@agency.com','0101000014','hash14','salt14','api14'),
('Nature Trails','Eco travel packages','nature15@agency.com','0101000015','hash15','salt15','api15'),
('Luxury Life','High-end vacations','luxury16@agency.com','0101000016','hash16','salt16','api16'),
('Adventure Seekers','Extreme travel','seekers17@agency.com','0101000017','hash17','salt17','api17'),
('Cultural Routes','Cultural heritage tours','culture18@agency.com','0101000018','hash18','salt18','api18'),
('World Discoveries','Worldwide destinations','world19@agency.com','0101000019','hash19','salt19','api19'),
('Family First Travels','Family-friendly packages','family20@agency.com','0101000020','hash20','salt20','api20'),
('Weekend Breaks','Quick getaways','weekend21@agency.com','0101000021','hash21','salt21','api21'),
('Luxury Cruise Co','Cruise specialists','cruise22@agency.com','0101000022','hash22','salt22','api22'),
('Explore More','Adventure holidays','explore23@agency.com','0101000023','hash23','salt23','api23'),
('Holiday Makers','Affordable trips','makers24@agency.com','0101000024','hash24','salt24','api24'),
('Vision Travels','Custom travel planning','vision25@agency.com','0101000025','hash25','salt25','api25');

-- =========================
-- DESTINATION
-- =========================
INSERT INTO Destination (Continent, Country, Province, City, Description) VALUES
('Africa','South Africa','Gauteng','Pretoria','Capital city with historical attractions'),
('Africa','South Africa','Western Cape','Cape Town','Coastal city with Table Mountain'),
('Europe','France','Ile-de-France','Paris','City of lights and romance'),
('Asia','Japan','Tokyo','Tokyo','Modern city with rich culture'),
('North America','USA','New York','New York City','Famous urban destination'),
('Europe','Italy','Lazio','Rome','Ancient historical city'),
('Africa','Kenya','Nairobi','Nairobi','Safari and wildlife destination'),
('South America','Brazil','Rio de Janeiro','Rio','Beach and carnival city'),
('Asia','Thailand','Bangkok','Bangkok','Popular tropical destination'),
('Europe','Spain','Catalonia','Barcelona','Artistic Mediterranean city'),
('Oceania','Australia','New South Wales','Sydney','Harbour city destination'),
('Africa','Egypt','Cairo','Cairo','Pyramids and ancient history'),
('Europe','Germany','Bavaria','Munich','Cultural German city'),
('Asia','China','Beijing','Beijing','Historic capital city'),
('North America','Canada','Ontario','Toronto','Modern multicultural city'),
('Europe','Netherlands','North Holland','Amsterdam','Canal city'),
('Africa','Morocco','Marrakesh','Marrakesh','Traditional markets and culture'),
('Asia','India','Maharashtra','Mumbai','Busy metropolitan city'),
('South America','Argentina','Buenos Aires','Buenos Aires','Tango and culture'),
('Europe','Greece','Attica','Athens','Ancient Greek landmarks'),
('Africa','Tanzania','Zanzibar','Stone Town','Island beach paradise'),
('Asia','Singapore','Central','Singapore','Modern city-state'),
('Europe','Switzerland','Zurich','Zurich','Scenic alpine destination'),
('North America','Mexico','Quintana Roo','Cancun','Beach resort destination'),
('Africa','Botswana','Gaborone','Gaborone','Wildlife and safari gateway');

-- =========================
-- TRAVEL PACKAGE
-- =========================
INSERT INTO TravelPackage (Description, Total_price, Capacity, Title, Start_date, End_date, AgencyID, ImageURL, Duration, PackageType) VALUES
('Luxury Cape Town tour',15000.00,20,'Cape Escape','2026-06-01','2026-06-07',1,'cape.jpg',7,'Luxury'),
('Paris romantic getaway',32000.00,10,'Paris Lovers','2026-07-01','2026-07-10',2,'paris.jpg',10,'Romantic'),
('Tokyo adventure trip',28000.00,15,'Tokyo Explorer','2026-08-01','2026-08-12',3,'tokyo.jpg',12,'Adventure'),
('Safari experience',22000.00,25,'Wild Safari','2026-09-01','2026-09-08',4,'safari.jpg',8,'Family'),
('Rome history tour',26000.00,18,'Roman Holiday','2026-10-01','2026-10-09',5,'rome.jpg',9,'Luxury'),
('Bangkok family package',18000.00,30,'Bangkok Fun','2026-11-01','2026-11-06',6,'bangkok.jpg',6,'Family'),
('Barcelona city break',24000.00,20,'Barcelona Dreams','2026-12-01','2026-12-08',7,'barcelona.jpg',8,'Romantic'),
('Sydney luxury retreat',40000.00,12,'Sydney Elite','2027-01-01','2027-01-10',8,'sydney.jpg',10,'Luxury'),
('New York shopping trip',35000.00,16,'NYC Shopping','2027-02-01','2027-02-09',9,'nyc.jpg',9,'Adventure'),
('Cairo pyramids tour',21000.00,22,'Egypt Wonders','2027-03-01','2027-03-07',10,'cairo.jpg',7,'Family'),
('Amsterdam canals package',27000.00,14,'Amsterdam Escape','2027-04-01','2027-04-08',11,'amsterdam.jpg',8,'Romantic'),
('Zanzibar beach holiday',30000.00,18,'Zanzibar Bliss','2027-05-01','2027-05-09',12,'zanzibar.jpg',9,'Luxury'),
('Toronto urban adventure',29000.00,17,'Toronto City','2027-06-01','2027-06-08',13,'toronto.jpg',8,'Adventure'),
('Athens history package',25000.00,19,'Athens Legends','2027-07-01','2027-07-09',14,'athens.jpg',9,'Family'),
('Cancun beach escape',33000.00,20,'Cancun Sun','2027-08-01','2027-08-10',15,'cancun.jpg',10,'Luxury'),
('Singapore city lights',31000.00,15,'Singapore Nights','2027-09-01','2027-09-08',16,'singapore.jpg',8,'Romantic'),
('Brazil carnival package',36000.00,25,'Rio Carnival','2027-10-01','2027-10-11',17,'rio.jpg',11,'Adventure'),
('Mumbai culture tour',23000.00,20,'Mumbai Magic','2027-11-01','2027-11-08',18,'mumbai.jpg',8,'Family'),
('Marrakesh market trip',22000.00,16,'Marrakesh Souks','2027-12-01','2027-12-07',19,'marrakesh.jpg',7,'Adventure'),
('Zurich alpine package',42000.00,10,'Zurich Alps','2028-01-01','2028-01-10',20,'zurich.jpg',10,'Luxury'),
('Botswana safari',27000.00,14,'Botswana Wild','2028-02-01','2028-02-08',21,'botswana.jpg',8,'Adventure'),
('Pretoria heritage tour',12000.00,30,'Pretoria Heritage','2028-03-01','2028-03-05',22,'pretoria.jpg',5,'Family'),
('Cape wine experience',20000.00,18,'Cape Wine','2028-04-01','2028-04-07',23,'wine.jpg',7,'Romantic'),
('Kenya migration safari',34000.00,15,'Migration Tour','2028-05-01','2028-05-10',24,'kenya.jpg',10,'Adventure'),
('Dubai luxury shopping',50000.00,12,'Dubai Luxe','2028-06-01','2028-06-09',25,'dubai.jpg',9,'Luxury');


-- =========================
-- BOOKING
-- =========================
INSERT INTO Booking (NumberOfPeople, BookingDate, Type, CustomerID, PackageID) VALUES
(2,'2026-05-01','Online',1,1),
(4,'2026-05-02','Online',2,2),
(1,'2026-05-03','Walk-In',3,3),
(3,'2026-05-04','Online',4,4),
(5,'2026-05-05','Agent',5,5),
(2,'2026-05-06','Online',6,6),
(1,'2026-05-07','Walk-In',7,7),
(4,'2026-05-08','Agent',8,8),
(2,'2026-05-09','Online',9,9),
(6,'2026-05-10','Online',10,10),
(2,'2026-05-11','Walk-In',11,11),
(3,'2026-05-12','Online',12,12),
(1,'2026-05-13','Agent',13,13),
(5,'2026-05-14','Online',14,14),
(2,'2026-05-15','Walk-In',15,15),
(3,'2026-05-16','Agent',16,16),
(4,'2026-05-17','Online',17,17),
(2,'2026-05-18','Online',18,18),
(1,'2026-05-19','Walk-In',19,19),
(5,'2026-05-20','Agent',20,20),
(2,'2026-05-21','Online',21,21),
(3,'2026-05-22','Walk-In',22,22),
(1,'2026-05-23','Agent',23,23),
(4,'2026-05-24','Online',24,24),
(2,'2026-05-25','Online',25,25);

-- =========================
-- FLIGHT
-- =========================
INSERT INTO Flight (Airline, Price, FlightDuration, DepartureAirport, ArrivalAirport, FlightNumber, DepartureTime, DepartureDate, PackageID) VALUES
('South African Airways',3500.00,'02:15:00','OR Tambo','Cape Town International',1001,'08:00:00','2026-06-01',1),
('Air France',12000.00,'11:30:00','OR Tambo','Charles de Gaulle',1002,'19:00:00','2026-07-01',2),
('Japan Airlines',15000.00,'14:45:00','OR Tambo','Tokyo Haneda',1003,'21:00:00','2026-08-01',3),
('Kenya Airways',5000.00,'04:20:00','OR Tambo','Jomo Kenyatta',1004,'10:30:00','2026-09-01',4),
('ITA Airways',9800.00,'10:10:00','OR Tambo','Rome Fiumicino',1005,'17:00:00','2026-10-01',5),
('Thai Airways',11000.00,'12:00:00','OR Tambo','Bangkok Suvarnabhumi',1006,'20:00:00','2026-11-01',6),
('Iberia',9500.00,'10:30:00','OR Tambo','Barcelona El Prat',1007,'18:00:00','2026-12-01',7),
('Qantas',18000.00,'13:40:00','OR Tambo','Sydney Kingsford',1008,'22:00:00','2027-01-01',8),
('Delta Airlines',16000.00,'15:15:00','OR Tambo','JFK International',1009,'23:00:00','2027-02-01',9),
('EgyptAir',7500.00,'08:00:00','OR Tambo','Cairo International',1010,'14:00:00','2027-03-01',10),
('KLM',10500.00,'11:20:00','OR Tambo','Amsterdam Schiphol',1011,'18:30:00','2027-04-01',11),
('FlySafair',6500.00,'05:00:00','OR Tambo','Zanzibar Airport',1012,'09:00:00','2027-05-01',12),
('Air Canada',14000.00,'16:00:00','OR Tambo','Toronto Pearson',1013,'20:00:00','2027-06-01',13),
('Aegean Airlines',10000.00,'10:50:00','OR Tambo','Athens International',1014,'16:00:00','2027-07-01',14),
('Aeromexico',17000.00,'17:10:00','OR Tambo','Cancun International',1015,'21:00:00','2027-08-01',15),
('Singapore Airlines',15500.00,'13:15:00','OR Tambo','Changi Airport',1016,'19:30:00','2027-09-01',16),
('LATAM Airlines',16500.00,'14:40:00','OR Tambo','Rio International',1017,'22:00:00','2027-10-01',17),
('Air India',9000.00,'09:30:00','OR Tambo','Mumbai International',1018,'15:00:00','2027-11-01',18),
('Royal Air Maroc',8200.00,'08:40:00','OR Tambo','Marrakesh Airport',1019,'13:00:00','2027-12-01',19),
('Swiss International',19000.00,'11:45:00','OR Tambo','Zurich Airport',1020,'18:00:00','2028-01-01',20),
('Air Botswana',6000.00,'03:00:00','OR Tambo','Gaborone Airport',1021,'09:00:00','2028-02-01',21),
('CemAir',2500.00,'01:00:00','OR Tambo','Wonderboom Airport',1022,'07:00:00','2028-03-01',22),
('British Airways',4500.00,'02:20:00','OR Tambo','Cape Town International',1023,'08:30:00','2028-04-01',23),
('Kenya Airways',13000.00,'05:30:00','OR Tambo','Nairobi Airport',1024,'12:00:00','2028-05-01',24),
('Emirates',22000.00,'08:50:00','OR Tambo','Dubai International',1025,'20:00:00','2028-06-01',25);

-- =========================
-- REVIEW
-- =========================
INSERT INTO Review (Message, Rating, ReviewDate, CustomerID, PackageID) VALUES
('Amazing experience and excellent service',5,'2026-06-10',1,1),
('Loved every moment of the Paris trip',5,'2026-07-15',2,2),
('Tokyo was unforgettable',4,'2026-08-20',3,3),
('Safari was exciting for the whole family',5,'2026-09-12',4,4),
('Rome tour guide was very knowledgeable',4,'2026-10-14',5,5),
('Bangkok package was affordable and fun',4,'2026-11-10',6,6),
('Barcelona nightlife was fantastic',5,'2026-12-12',7,7),
('Sydney hotels were luxurious',5,'2027-01-15',8,8),
('Shopping in New York was amazing',4,'2027-02-14',9,9),
('Loved seeing the pyramids',5,'2027-03-10',10,10),
('Amsterdam canals were beautiful',4,'2027-04-12',11,11),
('Zanzibar beaches were paradise',5,'2027-05-14',12,12),
('Toronto was clean and modern',4,'2027-06-11',13,13),
('Athens history tour was educational',5,'2027-07-13',14,14),
('Cancun beaches were breathtaking',5,'2027-08-16',15,15),
('Singapore city lights were stunning',4,'2027-09-12',16,16),
('Rio carnival exceeded expectations',5,'2027-10-18',17,17),
('Mumbai culture was vibrant',4,'2027-11-15',18,18),
('Marrakesh markets were unique',4,'2027-12-10',19,19),
('Zurich scenery was incredible',5,'2028-01-15',20,20),
('Botswana safari was unforgettable',5,'2028-02-10',21,21),
('Pretoria tour was very informative',4,'2028-03-07',22,22),
('Cape wine tasting was excellent',5,'2028-04-12',23,23),
('Kenya migration safari was breathtaking',5,'2028-05-16',24,24),
('Dubai shopping experience was luxury at its best',5,'2028-06-14',25,25);

-- =========================
-- TRAVELLER GROUPS
-- =========================
INSERT INTO TravellerGroups (NumberOfTravellers, CustomerID) VALUES
(2,1),
(4,2),
(1,3),
(3,4),
(5,5),
(2,6),
(6,7),
(4,8),
(3,9),
(5,10),
(2,11),
(7,12),
(4,13),
(3,14),
(6,15),
(2,16),
(5,17),
(4,18),
(1,19),
(3,20),
(8,21),
(2,22),
(4,23),
(5,24),
(3,25);

-- =========================
-- ATTRACTION
-- =========================
INSERT INTO Attraction 
(AttractionName, Longitude, Latitude, Rating, StudentsFee, AdultsFee, kidsFee, ElderlyFee, DestinationID) 
VALUES
('Union Buildings',28.2293,-25.7405,4.7,50.00,100.00,40.00,60.00,1),
('Table Mountain',18.4098,-33.9628,4.9,120.00,250.00,80.00,150.00,2),
('Eiffel Tower',2.2945,48.8584,5.0,200.00,400.00,150.00,250.00,3),
('Tokyo Skytree',139.8107,35.7100,4.8,180.00,350.00,120.00,220.00,4),
('Statue of Liberty',-74.0445,40.6892,4.7,150.00,300.00,100.00,180.00,5),
('Colosseum',12.4922,41.8902,4.9,170.00,320.00,120.00,200.00,6),
('Nairobi National Park',36.8588,-1.3733,4.6,140.00,280.00,90.00,160.00,7),
('Christ the Redeemer',-43.2105,-22.9519,4.8,160.00,300.00,110.00,190.00,8),
('Grand Palace',100.4913,13.7500,4.7,130.00,260.00,85.00,150.00,9),
('Sagrada Familia',2.1744,41.4036,4.9,190.00,360.00,130.00,210.00,10),
('Sydney Opera House',151.2153,-33.8568,4.8,200.00,380.00,140.00,240.00,11),
('Great Pyramid of Giza',31.1342,29.9792,5.0,220.00,420.00,160.00,260.00,12),
('Marienplatz',11.5755,48.1374,4.5,60.00,120.00,40.00,70.00,13),
('Forbidden City',116.3970,39.9163,4.8,180.00,340.00,120.00,200.00,14),
('CN Tower',-79.3871,43.6426,4.7,160.00,310.00,100.00,180.00,15),
('Anne Frank House',4.8839,52.3752,4.8,120.00,240.00,80.00,140.00,16),
('Jemaa el-Fnaa',-7.9892,31.6258,4.6,70.00,140.00,50.00,90.00,17),
('Gateway of India',72.8347,18.9220,4.5,50.00,100.00,30.00,60.00,18),
('Teatro Colon',-58.3830,-34.6037,4.7,130.00,250.00,90.00,150.00,19),
('Acropolis',23.7265,37.9715,4.9,180.00,350.00,120.00,220.00,20),
('Stone Town Beach',39.1910,-6.1659,4.8,90.00,180.00,60.00,110.00,21),
('Marina Bay Sands',103.8607,1.2834,4.9,200.00,390.00,150.00,240.00,22),
('Lake Zurich Cruise',8.5417,47.3769,4.7,170.00,320.00,110.00,190.00,23),
('Chichen Itza',-88.5678,20.6843,5.0,210.00,400.00,150.00,250.00,24),
('Okavango Delta',22.9375,-19.2964,4.9,250.00,500.00,180.00,300.00,25);

-- =========================
-- ACCOMODATION
-- =========================
INSERT INTO Accomodation
(CostPerNight, Rating, ApartmentNo, StreetNo, Street, Type, WeekCost, DestinationID)
VALUES
(1200.00,4.5,'A1','101','Church Street','Hotel',7500.00,1),
(2500.00,4.9,'B2','22','Beach Road','Resort',16000.00,2),
(3200.00,5.0,'C3','15','Paris Avenue','Hotel',21000.00,3),
(2800.00,4.8,'D4','8','Shibuya Street','Apartment',18500.00,4),
(3500.00,4.7,'E5','99','Broadway','Hotel',23000.00,5),
(2600.00,4.8,'F6','11','Roma Street','Villa',17500.00,6),
(1800.00,4.4,'G7','45','Safari Lane','Lodge',12000.00,7),
(2900.00,4.7,'H8','77','Copacabana Ave','Resort',19000.00,8),
(1700.00,4.5,'I9','66','Bangkok Central','Apartment',11000.00,9),
(3100.00,4.9,'J10','25','Catalonia Road','Hotel',20500.00,10),
(4000.00,5.0,'K11','14','Harbour Street','Luxury Hotel',27000.00,11),
(2200.00,4.6,'L12','88','Pyramid Road','Hotel',14500.00,12),
(2400.00,4.5,'M13','33','Munich Platz','Apartment',15500.00,13),
(2700.00,4.8,'N14','55','Imperial Road','Hotel',17800.00,14),
(3000.00,4.7,'O15','71','Toronto Avenue','Condo',19800.00,15),
(2600.00,4.6,'P16','40','Canal Street','Apartment',17200.00,16),
(1900.00,4.4,'Q17','19','Market Street','Riad',12500.00,17),
(2100.00,4.5,'R18','60','Mumbai Central','Hotel',13800.00,18),
(2300.00,4.6,'S19','73','Buenos Aires Ave','Apartment',15000.00,19),
(3400.00,4.9,'T20','81','Athens Hill','Villa',22500.00,20),
(2800.00,4.8,'U21','12','Ocean Drive','Beach Resort',18500.00,21),
(3900.00,5.0,'V22','50','Marina Boulevard','Luxury Hotel',26000.00,22),
(3600.00,4.9,'W23','44','Zurich Lake Road','Chalet',24000.00,23),
(2500.00,4.7,'X24','91','Cancun Strip','Resort',16800.00,24),
(3000.00,4.8,'Y25','27','Delta Safari Road','Safari Lodge',20000.00,25);

-- =========================
-- RESTAURANT
-- =========================
INSERT INTO Restaurant
(Name, StreetNo, StreetName, Rating, DestinationID)
VALUES
('Capital Grill','101','Church Street',4.5,1),
('Ocean View Dining','22','Beach Road',4.8,2),
('Le Gourmet Paris','15','Paris Avenue',5.0,3),
('Sakura Sushi','8','Shibuya Street',4.7,4),
('Empire Steakhouse','99','Broadway',4.6,5),
('Roma Bella','11','Roma Street',4.8,6),
('Savannah Eats','45','Safari Lane',4.4,7),
('Rio Flavours','77','Copacabana Ave',4.7,8),
('Bangkok Spice','66','Bangkok Central',4.5,9),
('Barcelona Tapas','25','Catalonia Road',4.9,10),
('Harbour Seafood','14','Harbour Street',5.0,11),
('Pyramid Bistro','88','Pyramid Road',4.6,12),
('Munich Tavern','33','Munich Platz',4.5,13),
('Dragon Palace','55','Imperial Road',4.8,14),
('Toronto Grill','71','Toronto Avenue',4.7,15),
('Canal Café','40','Canal Street',4.6,16),
('Moroccan Delight','19','Market Street',4.4,17),
('Mumbai Curry House','60','Mumbai Central',4.5,18),
('Buenos Aires Steak','73','Buenos Aires Ave',4.6,19),
('Athens Olive Garden','81','Athens Hill',4.9,20),
('Zanzibar Ocean Eats','12','Ocean Drive',4.8,21),
('Marina Bay Dining','50','Marina Boulevard',5.0,22),
('Zurich Alpine Grill','44','Zurich Lake Road',4.9,23),
('Cancun Beach Café','91','Cancun Strip',4.7,24),
('Delta Safari Restaurant','27','Delta Safari Road',4.8,25);

-- =========================
-- INCLUDES
-- =========================
INSERT INTO Includes (DestinationID, PackageID) VALUES
(1,1),
(2,2),
(3,3),
(4,4),
(5,5),
(6,6),
(7,7),
(8,8),
(9,9),
(10,10),
(11,11),
(12,12),
(13,13),
(14,14),
(15,15),
(16,16),
(17,17),
(18,18),
(19,19),
(20,20),
(21,21),
(22,22),
(23,23),
(24,24),
(25,25);

-- =========================
-- MAKES
-- =========================
INSERT INTO Makes (CustomerID, BookingID) VALUES
(1,1),
(2,2),
(3,3),
(4,4),
(5,5),
(6,6),
(7,7),
(8,8),
(9,9),
(10,10),
(11,11),
(12,12),
(13,13),
(14,14),
(15,15),
(16,16),
(17,17),
(18,18),
(19,19),
(20,20),
(21,21),
(22,22),
(23,23),
(24,24),
(25,25);

-- =========================
-- CONTAINS
-- =========================
INSERT INTO Contains (FlightID, PackageID) VALUES
(1,1),
(2,2),
(3,3),
(4,4),
(5,5),
(6,6),
(7,7),
(8,8),
(9,9),
(10,10),
(11,11),
(12,12),
(13,13),
(14,14),
(15,15),
(16,16),
(17,17),
(18,18),
(19,19),
(20,20),
(21,21),
(22,22),
(23,23),
(24,24),
(25,25);

-- =========================
-- AGENCY EMAIL CONTACT
-- =========================
INSERT INTO AgencyEmailContact (EmailAddress, AgencyID) VALUES
('contact1@safaritravels.com',1),
('contact2@oceanescape.com',2),
('contact3@mountainadventures.com',3),
('contact4@dreamvacations.com',4),
('contact5@skylinetours.com',5),
('contact6@elitegetaways.com',6),
('contact7@africaexplore.com',7),
('contact8@holidayhub.com',8),
('contact9@travelmasters.com',9),
('contact10@paradisetrips.com',10),
('contact11@globaljourneys.com',11),
('contact12@sunsetescapes.com',12),
('contact13@wildlifewonders.com',13),
('contact14@urbanadventures.com',14),
('contact15@naturetrails.com',15),
('contact16@luxurylife.com',16),
('contact17@adventureseekers.com',17),
('contact18@culturalroutes.com',18),
('contact19@worlddiscoveries.com',19),
('contact20@familyfirsttravels.com',20),
('contact21@weekendbreaks.com',21),
('contact22@luxurycruiseco.com',22),
('contact23@exploremore.com',23),
('contact24@holidaymakers.com',24),
('contact25@visiontravels.com',25);

-- =========================
-- AGENCY PHONE NUMBER
-- =========================
INSERT INTO AgencyPhoneNumber (PhoneNumber, AgencyID) VALUES
('0102000001',1),
('0102000002',2),
('0102000003',3),
('0102000004',4),
('0102000005',5),
('0102000006',6),
('0102000007',7),
('0102000008',8),
('0102000009',9),
('0102000010',10),
('0102000011',11),
('0102000012',12),
('0102000013',13),
('0102000014',14),
('0102000015',15),
('0102000016',16),
('0102000017',17),
('0102000018',18),
('0102000019',19),
('0102000020',20),
('0102000021',21),
('0102000022',22),
('0102000023',23),
('0102000024',24),
('0102000025',25);