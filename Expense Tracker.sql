DROP DATABASE IF EXISTS `Expense Tracker`;
CREATE DATABASE `Expense Tracker`;
USE `Expense Tracker`;

CREATE TABLE `User` (
	id INT NOT NULL AUTO_INCREMENT,
    email VARCHAR(200) UNIQUE NOT NULL,
    password VARCHAR(300) NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE `Category` (
	id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(300) NOT NULL UNIQUE,
    PRIMARY KEY(id)
);

CREATE TABLE `Expense` (
	id INT NOT NULL AUTO_INCREMENT,
    date DATETIME NOT NULL,
    itemName VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    amount INT NOT NULL DEFAULT(1),
    categoryId INT NOT NULL,
    userId INT NOT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(categoryId) REFERENCES Category(id)
		ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY(userId) REFERENCES User(id)
);


INSERT INTO `User` (email,password) VALUES ("Hadi@hadi","$2y$10$iZEnmvNCg6IGQRu/53crDetAfQfwoYvg4Na7CwFI4EMVYEw3DzBqS"); 
INSERT INTO `User` (email,password) VALUES ("Aya@aya","$2y$10$vF2.zddUjm3AYMvzW3Vv9uNu..6Ei2PV9/zFINAsh56ls.uoKuQ6e");
INSERT INTO `User` (email,password) VALUES ("Zeinab@zeinab","$2y$10$YVwoIcpDy98sgZUJ.C.vBeqURPoQnnaVXpH/s0uX2/nFPRZDGikvC");

INSERT INTO `Category` (name) Values('Computers');
INSERT INTO `Category` (name) VALUES ("accessories"); 
INSERT INTO `Category` (name) VALUES ("laps");
INSERT INTO `Category` (name) VALUES ("hds"); 

INSERT INTO `Expense` (date, itemName, price, amount, categoryId, userId) Values('2020:11:11 00:00', 'ASUS', 1500, 1, 1, 1);
