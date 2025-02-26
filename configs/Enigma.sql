DROP DATABASE IF EXISTS Enigma;
CREATE DATABASE Enigma;
USE Enigma;

CREATE TABLE Users (
  UserId int NOT NULL AUTO_INCREMENT,
  Username varchar(255) NOT NULL,
  Password varchar(255) NOT NULL,
  Email varchar(255) NOT NULL,
  Role enum('Admin','User') NOT NULL DEFAULT 'User',
  CreatedAt timestamp NULL DEFAULT current_timestamp(),
  LastLogin timestamp NULL DEFAULT NULL,
  AccountStatus enum('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (UserId),
  UNIQUE KEY Username (Username),
  UNIQUE KEY Email (Email)
) ENGINE=InnoDB;

INSERT INTO Users (UserId, Username, Password, Email, Role, CreatedAt, LastLogin, AccountStatus) VALUES
(1, 'Admin.', '$2y$10$z0wpXkfkhRnc4MYpRjkDVudtYFP.3Amrc5ANUiximpXFvkOuQhmZy', 'admin@enigma.org', 'Admin', '2025-02-24 00:00:00', '2025-02-24 00:00:00', 'Active');

CREATE TABLE Sessions (
  SessionId varchar(255) NOT NULL,
  UserId int NOT NULL,
  CreatedAt timestamp NULL DEFAULT current_timestamp(),
  ExpiresAt timestamp NOT NULL,
  LastActivity timestamp NULL DEFAULT current_timestamp(),
  IPAddress varchar(45) NOT NULL,
  UserAgent varchar(255) DEFAULT NULL,
  IsValid boolean NOT NULL DEFAULT TRUE,
  PRIMARY KEY (SessionId)
) ENGINE=InnoDB;

CREATE TABLE LoginAttempts (
  AttemptId int NOT NULL AUTO_INCREMENT,
  UserId int NOT NULL,
  AttemptTime timestamp NULL DEFAULT current_timestamp(),
  Status enum('Success','Failure') NOT NULL,
  IPAddress varchar(45) NOT NULL,
  UserAgent varchar(255) DEFAULT NULL,
  PRIMARY KEY (AttemptId)
) ENGINE=InnoDB;

ALTER TABLE Sessions
  ADD CONSTRAINT sessions_user_fk FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE;

ALTER TABLE LoginAttempts
  ADD CONSTRAINT login_attempts_user_fk FOREIGN KEY (UserId) REFERENCES Users(UserId) ON DELETE CASCADE;
