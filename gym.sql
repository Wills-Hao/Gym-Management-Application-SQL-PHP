drop table WorkoutHistory1
cascade constraints;
drop table WorkoutHistory2
cascade constraints;
drop table Room
cascade constraints;
drop table Equipment
cascade constraints;
drop table Course
cascade constraints;
drop table PrivateSession
cascade constraints;
drop table GroupSession
cascade constraints;
drop table Member
cascade constraints;
drop table Trainer
cascade constraints;
drop table DietPlan2
cascade constraints;
drop table DietPlan3
cascade constraints;
drop table DietPlan4
cascade constraints;
drop table assessed_BodyAnalysisRecord1
cascade constraints;
drop table assessed_BodyAnalysisRecord3
cascade constraints;
drop table assessed_BodyAnalysisRecord4
cascade constraints;
drop table purchase
cascade constraints;
drop table make
cascade constraints;
drop table evaluate
cascade constraints;
drop table teach
cascade constraints;
drop table take_in
cascade constraints;


CREATE TABLE WorkoutHistory2(
    ExerciseID INTEGER NOT NULL,
    Duration INTEGER NOT NULL,
    CaloriesBurned INTEGER,
    PRIMARY KEY (ExerciseID, Duration));


CREATE TABLE Room(
    RoomID INTEGER PRIMARY KEY,
    Name VARCHAR(20) NOT NULL,
    MaxCapacity INTEGER NOT NULL);
    

CREATE TABLE Member(
    MemberID INTEGER PRIMARY KEY,
    Name VARCHAR(30) NOT NULL,
    DateJoined DATE NOT NULL,
    FitnessGoal VARCHAR(30));


CREATE TABLE WorkoutHistory1(
    HistoryID INTEGER PRIMARY KEY NOT NULL,
    DateTime TIMESTAMP NOT NULL,
    ExerciseID INTEGER,
    Duration INTEGER,
    RoomID INTEGER,
    MemberID INTEGER,
    FOREIGN KEY (ExerciseID, Duration) REFERENCES 
    	WorkoutHistory2(ExerciseID, Duration),
    FOREIGN KEY (RoomID) REFERENCES 
    	Room(RoomID),
    FOREIGN KEY (MemberID) REFERENCES 
    	Member(MemberID));


CREATE TABLE Equipment(
    EquipmentID INTEGER PRIMARY KEY,
    AvailabilityStatus VARCHAR(10) NOT NULL,
    Type VARCHAR(20) NOT NULL,
    RoomID INTEGER,
    FOREIGN KEY (RoomID) REFERENCES 
    	Room(RoomID)
        ON DELETE SET NULL);


CREATE TABLE Course(
    CourseID INTEGER PRIMARY KEY,
    Start_Date DATE NOT NULL,
    Price FLOAT NOT NULL,
    Duration INTEGER NOT NULL);


CREATE TABLE PrivateSession(
    CourseID INTEGER PRIMARY KEY,
    FOREIGN KEY (CourseID) REFERENCES 
        Course(CourseID) 
        ON DELETE CASCADE);


CREATE TABLE GroupSession(
    CourseID INTEGER PRIMARY KEY,
    MaxMembers INTEGER,
    FOREIGN KEY (CourseID) REFERENCES 
    	Course(CourseID) 
        ON DELETE CASCADE);


CREATE TABLE DietPlan2(
    DietPlanID INTEGER PRIMARY KEY,
    Recipes VARCHAR(100),
    MemberID INTEGER,
    FOREIGN KEY (MemberID) REFERENCES 
        Member(MemberID)
        ON DELETE CASCADE);


CREATE TABLE DietPlan3(
    Carbohydrates INTEGER NOT NULL,
    Proteins INTEGER NOT NULL,
    Fats INTEGER NOT NULL,
    Calories INTEGER NOT NULL,
    PRIMARY KEY (Carbohydrates, Proteins, Fats));


CREATE TABLE DietPlan4(
    Recipes VARCHAR(100) PRIMARY KEY,
    Carbohydrates INTEGER,
    Proteins INTEGER,
    Fats INTEGER,
    FOREIGN KEY (Carbohydrates, Proteins, Fats) REFERENCES 
        DietPlan3(Carbohydrates, Proteins, Fats)
        ON DELETE CASCADE);


CREATE TABLE Trainer(
    TrainerID INTEGER PRIMARY KEY,
    Name VARCHAR(30) NOT NULL,
    Expertise VARCHAR(30),
    AvailableHoursDaily INTEGER NOT NULL);


CREATE TABLE assessed_BodyAnalysisRecord1(
    Age INTEGER,
    Weight FLOAT,
    Height FLOAT,
    MetabolicRate FLOAT,
    PRIMARY KEY (Weight, Height, Age));


CREATE TABLE assessed_BodyAnalysisRecord3(
    Age INTEGER,
    Weight FLOAT,
    Height FLOAT,
    Gender VARCHAR(10),
    BodyFatPercentage FLOAT,
    PRIMARY KEY (Weight, Height, Age, Gender),
    FOREIGN KEY (Age, Weight, Height) REFERENCES 
        assessed_BodyAnalysisRecord1(Age, Weight, Height)
        ON DELETE CASCADE);


CREATE TABLE assessed_BodyAnalysisRecord4(
    RecordID INTEGER,
    Age INTEGER,
    Weight FLOAT,
    Height FLOAT,
    Gender VARCHAR(10),
    Assess_Date DATE,
    MuscleMass FLOAT,
    MemberID INTEGER,
    PRIMARY KEY(RecordID, MemberID),
    FOREIGN KEY (MemberID) REFERENCES 
        Member(MemberID)
        ON DELETE CASCADE,
    FOREIGN KEY (Age, Weight, Height, Gender) REFERENCES 
        assessed_BodyAnalysisRecord3(Age, Weight, Height, Gender)
        ON DELETE CASCADE);


CREATE TABLE purchase(
    MemberID INTEGER,
    CourseID INTEGER,
    PRIMARY KEY (MemberID, CourseID),
    FOREIGN KEY (MemberID) REFERENCES 
        Member(MemberID),
    FOREIGN KEY (CourseID) REFERENCES 
        Course(CourseID));


CREATE TABLE make(
    RecordID INTEGER,
    MemberID INTEGER,
    DietPlanID INTEGER,
    PRIMARY KEY (RecordID, MemberID, DietPlanID),
    FOREIGN KEY (RecordID, MemberID) REFERENCES 
        assessed_BodyAnalysisRecord4(RecordID, MemberID) 
        ON DELETE CASCADE,
    FOREIGN KEY (DietPlanID) REFERENCES 
        DietPlan2(DietPlanID)
        ON DELETE SET NULL);


CREATE TABLE evaluate(
    RecordID INTEGER,
    MemberID INTEGER,
    TrainerID INTEGER,
    PRIMARY KEY (RecordID, MemberID, TrainerID),
    FOREIGN KEY (RecordID, MemberID) REFERENCES 
        assessed_BodyAnalysisRecord4(RecordID, MemberID) 
        ON DELETE CASCADE,
    FOREIGN KEY (TrainerID) REFERENCES 
        Trainer(TrainerID)
        ON DELETE CASCADE);


CREATE TABLE teach(
    TrainerID INTEGER,
    CourseID INTEGER,
    PRIMARY KEY (CourseID, TrainerID),
    FOREIGN KEY (TrainerID) REFERENCES 
        Trainer(TrainerID)
        ON DELETE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES 
        Course(CourseID)
        ON DELETE CASCADE);


CREATE TABLE take_in(
    CourseID INTEGER,
    RoomID INTEGER,
    PRIMARY KEY (CourseID, RoomID),
    FOREIGN KEY (CourseID) REFERENCES 
        Course(CourseID)
        ON DELETE CASCADE,
    FOREIGN KEY (RoomID) REFERENCES 
        Room(RoomID));


INSERT INTO WorkoutHistory2 (ExerciseID, Duration, CaloriesBurned) VALUES (101, 30, 150);
INSERT INTO WorkoutHistory2 (ExerciseID, Duration, CaloriesBurned) VALUES (102, 45, 220);
INSERT INTO WorkoutHistory2 (ExerciseID, Duration, CaloriesBurned) VALUES (103, 60, 300);
INSERT INTO WorkoutHistory2 (ExerciseID, Duration, CaloriesBurned) VALUES (104, 55, 270);
INSERT INTO WorkoutHistory2 (ExerciseID, Duration, CaloriesBurned) VALUES (105, 40, 190);

INSERT INTO Room (RoomID, Name, MaxCapacity) VALUES (1, 'Room A', 50);
INSERT INTO Room (RoomID, Name, MaxCapacity) VALUES (2, 'Room B', 40);
INSERT INTO Room (RoomID, Name, MaxCapacity) VALUES (3, 'Room C', 60);
INSERT INTO Room (RoomID, Name, MaxCapacity) VALUES (4, 'Room D', 45);
INSERT INTO Room (RoomID, Name, MaxCapacity) VALUES (5, 'Room E', 55);

INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (1, 'John Doe', TO_DATE('2023-01-15', 'YYYY-MM-DD'), 'Weight Loss');
INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (2, 'Jane Smith', TO_DATE('2023-03-20', 'YYYY-MM-DD'), 'Muscle Gain');
INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (3, 'Alice Johnson', TO_DATE('2023-05-10', 'YYYY-MM-DD'), 'Fitness Maintenance');
INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (4, 'Bob Brown', TO_DATE('2023-07-02', 'YYYY-MM-DD'), 'Cardiovascular Health');
INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (5, 'Eve Wilson', TO_DATE('2023-09-05', 'YYYY-MM-DD'), 'Strength Training');
INSERT INTO Member (MemberID, Name, DateJoined, FitnessGoal) VALUES
    (11, 'Eva son', TO_DATE('2023-10-05', 'YYYY-MM-DD'), 'Strength Training');

INSERT INTO WorkoutHistory1 (HistoryID, DateTime, ExerciseID, Duration, RoomID, MemberID) VALUES
    (1, TO_TIMESTAMP('2023-10-20 08:00:00', 'YYYY-MM-DD HH24:MI:SS'), 101, 30, 1, 1);
INSERT INTO WorkoutHistory1 (HistoryID, DateTime, ExerciseID, Duration, RoomID, MemberID) VALUES
    (2, TO_TIMESTAMP('2023-10-20 09:00:00', 'YYYY-MM-DD HH24:MI:SS'), 102, 45, 2, 2);
INSERT INTO WorkoutHistory1 (HistoryID, DateTime, ExerciseID, Duration, RoomID, MemberID) VALUES
    (3, TO_TIMESTAMP('2023-10-20 10:00:00', 'YYYY-MM-DD HH24:MI:SS'), 103, 60, 3, 3);
INSERT INTO WorkoutHistory1 (HistoryID, DateTime, ExerciseID, Duration, RoomID, MemberID) VALUES
    (4, TO_TIMESTAMP('2023-10-20 11:00:00', 'YYYY-MM-DD HH24:MI:SS'), 104, 55, 4, 4);
INSERT INTO WorkoutHistory1 (HistoryID, DateTime, ExerciseID, Duration, RoomID, MemberID) VALUES
    (5, TO_TIMESTAMP('2023-10-20 12:00:00', 'YYYY-MM-DD HH24:MI:SS'), 105, 40, 5, 5);

INSERT INTO Equipment (EquipmentID, AvailabilityStatus, Type, RoomID) VALUES
    (101, 'Available', 'Treadmill', 1);
INSERT INTO Equipment (EquipmentID, AvailabilityStatus, Type, RoomID) VALUES
    (102, 'In Use', 'Elliptical', 2);
INSERT INTO Equipment (EquipmentID, AvailabilityStatus, Type, RoomID) VALUES
    (103, 'Available', 'Dumbbells', 3);
INSERT INTO Equipment (EquipmentID, AvailabilityStatus, Type, RoomID) VALUES
    (104, 'In Use', 'Exercise Bike', 4);
INSERT INTO Equipment (EquipmentID, AvailabilityStatus, Type, RoomID) VALUES
    (105, 'Available', 'Rowing Machine', 5);

INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (1, TO_DATE('2023-10-20', 'YYYY-MM-DD'), 50.0, 60);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (2, TO_DATE('2023-10-21', 'YYYY-MM-DD'), 40.0, 45);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (3, TO_DATE('2023-10-22', 'YYYY-MM-DD'), 60.0, 75);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (4, TO_DATE('2023-10-23', 'YYYY-MM-DD'), 55.0, 90);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (5, TO_DATE('2023-10-24', 'YYYY-MM-DD'), 70.0, 70);

INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (6, TO_DATE('2023-10-20', 'YYYY-MM-DD'), 50.0, 60);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (7, TO_DATE('2023-10-21', 'YYYY-MM-DD'), 40.0, 45);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (8, TO_DATE('2023-10-22', 'YYYY-MM-DD'), 60.0, 75);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (9, TO_DATE('2023-10-23', 'YYYY-MM-DD'), 55.0, 90);
INSERT INTO Course (CourseID, Start_Date, Price, Duration) VALUES 
    (10, TO_DATE('2023-10-24', 'YYYY-MM-DD'), 70.0, 70);

INSERT INTO PrivateSession (CourseID) VALUES (1);
INSERT INTO PrivateSession (CourseID) VALUES (2);
INSERT INTO PrivateSession (CourseID) VALUES (3);
INSERT INTO PrivateSession (CourseID) VALUES (4);
INSERT INTO PrivateSession (CourseID) VALUES (5);

INSERT INTO GroupSession (CourseID, MaxMembers) VALUES (6, 10);
INSERT INTO GroupSession (CourseID, MaxMembers) VALUES (7, 15);
INSERT INTO GroupSession (CourseID, MaxMembers) VALUES (8, 12);
INSERT INTO GroupSession (CourseID, MaxMembers) VALUES (9, 8);
INSERT INTO GroupSession (CourseID, MaxMembers) VALUES (10, 20);

INSERT INTO Trainer (TrainerID, Name, Expertise, AvailableHoursDaily) VALUES
    (201, 'Trainer 1', 'Strength Training', 2);
INSERT INTO Trainer (TrainerID, Name, Expertise, AvailableHoursDaily) VALUES
    (202, 'Trainer 2', 'Yoga', 3);
INSERT INTO Trainer (TrainerID, Name, Expertise, AvailableHoursDaily) VALUES
    (203, 'Trainer 3', 'Cardiovascular Health', 2);
INSERT INTO Trainer (TrainerID, Name, Expertise, AvailableHoursDaily) VALUES
    (204, 'Trainer 4', 'CrossFit', 1);
INSERT INTO Trainer (TrainerID, Name, Expertise, AvailableHoursDaily) VALUES
    (205, 'Trainer 5', 'Pilates', 3);

INSERT INTO DietPlan2 (DietPlanID, Recipes, MemberID) VALUES (1, 'Balanced Diet', 1);
INSERT INTO DietPlan2 (DietPlanID, Recipes, MemberID) VALUES (2, 'Keto Diet', 2);
INSERT INTO DietPlan2 (DietPlanID, Recipes, MemberID) VALUES (3, 'Vegan Diet', 3);
INSERT INTO DietPlan2 (DietPlanID, Recipes, MemberID) VALUES (4, 'Paleo Diet', 4);
INSERT INTO DietPlan2 (DietPlanID, Recipes, MemberID) VALUES (5, 'Low-Carb Diet', 5);

INSERT INTO DietPlan3 (Carbohydrates, Proteins, Fats, Calories) VALUES (100, 50, 30, 1200);
INSERT INTO DietPlan3 (Carbohydrates, Proteins, Fats, Calories) VALUES (80, 60, 40, 1400);
INSERT INTO DietPlan3 (Carbohydrates, Proteins, Fats, Calories) VALUES (60, 70, 50, 1500);
INSERT INTO DietPlan3 (Carbohydrates, Proteins, Fats, Calories) VALUES (120, 40, 35, 1300);
INSERT INTO DietPlan3 (Carbohydrates, Proteins, Fats, Calories) VALUES (90, 55, 45, 1350);

INSERT INTO DietPlan4 (Recipes, Carbohydrates, Proteins, Fats) VALUES ('Balanced Diet', 80, 60, 40);
INSERT INTO DietPlan4 (Recipes, Carbohydrates, Proteins, Fats) VALUES ('Keto Diet', 100, 50, 30);
INSERT INTO DietPlan4 (Recipes, Carbohydrates, Proteins, Fats) VALUES ('Vegan Diet', 120, 40, 35);
INSERT INTO DietPlan4 (Recipes, Carbohydrates, Proteins, Fats) VALUES ('Paleo Diet', 90, 55, 45);
INSERT INTO DietPlan4 (Recipes, Carbohydrates, Proteins, Fats) VALUES ('Low-Carb Diet', 60, 70, 50);

INSERT INTO assessed_BodyAnalysisRecord1 (Age, Weight, Height, MetabolicRate) VALUES
    (25, 70.5, 175.0, 1500.0);
INSERT INTO assessed_BodyAnalysisRecord1 (Age, Weight, Height, MetabolicRate) VALUES
    (30, 68.2, 170.5, 1400.0);
INSERT INTO assessed_BodyAnalysisRecord1 (Age, Weight, Height, MetabolicRate) VALUES
    (35, 80.0, 180.0, 1600.0);
INSERT INTO assessed_BodyAnalysisRecord1 (Age, Weight, Height, MetabolicRate) VALUES
    (28, 65.5, 160.0, 1450.0);
INSERT INTO assessed_BodyAnalysisRecord1 (Age, Weight, Height, MetabolicRate) VALUES
    (40, 75.0, 170.0, 1550.0);

INSERT INTO assessed_BodyAnalysisRecord3 (Age, Weight, Height, Gender, BodyFatPercentage)
VALUES
    (25, 70.5, 175.0, 'Male', 18.5);
INSERT INTO assessed_BodyAnalysisRecord3 (Age, Weight, Height, Gender, BodyFatPercentage)
VALUES
    (30, 68.2, 170.5, 'Female', 22.0);
INSERT INTO assessed_BodyAnalysisRecord3 (Age, Weight, Height, Gender, BodyFatPercentage)
VALUES
    (35, 80.0, 180.0, 'Male', 15.2);
INSERT INTO assessed_BodyAnalysisRecord3 (Age, Weight, Height, Gender, BodyFatPercentage)
VALUES
    (28, 65.5, 160.0, 'Male', 20.1);
INSERT INTO assessed_BodyAnalysisRecord3 (Age, Weight, Height, Gender, BodyFatPercentage)
VALUES
    (40, 75.0, 170.0, 'Female', 19.8);

INSERT INTO assessed_BodyAnalysisRecord4 (RecordID, Age, Weight, Height, Gender, Assess_Date, MuscleMass, MemberID)
VALUES
    (1, 25, 70.5, 175.0, 'Male', TO_DATE('2023-01-15', 'YYYY-MM-DD'), 65.0, 1);
INSERT INTO assessed_BodyAnalysisRecord4 (RecordID, Age, Weight, Height, Gender, Assess_Date, MuscleMass, MemberID)
VALUES 
    (2, 30, 68.2, 170.5, 'Female', TO_DATE('2023-02-20', 'YYYY-MM-DD'), 60.5, 2);
INSERT INTO assessed_BodyAnalysisRecord4 (RecordID, Age, Weight, Height, Gender, Assess_Date, MuscleMass, MemberID)
VALUES
    (3, 35, 80.0, 180.0, 'Male', TO_DATE('2023-03-10', 'YYYY-MM-DD'), 70.0, 3);
INSERT INTO assessed_BodyAnalysisRecord4 (RecordID, Age, Weight, Height, Gender, Assess_Date, MuscleMass, MemberID)
VALUES
    (4, 28, 65.5, 160.0, 'Male', TO_DATE('2023-04-05', 'YYYY-MM-DD'), 58.0, 4);
INSERT INTO assessed_BodyAnalysisRecord4 (RecordID, Age, Weight, Height, Gender, Assess_Date, MuscleMass, MemberID)
VALUES
    (5, 40, 75.0, 170.0, 'Female', TO_DATE('2023-05-12', 'YYYY-MM-DD'), 68.5, 5);

INSERT INTO purchase (MemberID, CourseID) VALUES (1, 1);
INSERT INTO purchase (MemberID, CourseID) VALUES (2, 2);
INSERT INTO purchase (MemberID, CourseID) VALUES (3, 3);
INSERT INTO purchase (MemberID, CourseID) VALUES (4, 4);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 5);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 7);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 6);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 8);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 9);
INSERT INTO purchase (MemberID, CourseID) VALUES (5, 10);


INSERT INTO make (RecordID, MemberID, DietPlanID) VALUES (1, 1, 1);
INSERT INTO make (RecordID, MemberID, DietPlanID) VALUES (2, 2, 2);
INSERT INTO make (RecordID, MemberID, DietPlanID) VALUES (3, 3, 3);
INSERT INTO make (RecordID, MemberID, DietPlanID) VALUES (4, 4, 4);
INSERT INTO make (RecordID, MemberID, DietPlanID) VALUES (5, 5, 5);

INSERT INTO teach (TrainerID, CourseID) VALUES (201, 1);
INSERT INTO teach (TrainerID, CourseID) VALUES (202, 2);
INSERT INTO teach (TrainerID, CourseID) VALUES (203, 3);
INSERT INTO teach (TrainerID, CourseID) VALUES (204, 4);
INSERT INTO teach (TrainerID, CourseID) VALUES (205, 5);

INSERT INTO evaluate (RecordID, MemberID, TrainerID) VALUES (1, 1, 201);
INSERT INTO evaluate (RecordID, MemberID, TrainerID) VALUES (2, 2, 202);
INSERT INTO evaluate (RecordID, MemberID, TrainerID) VALUES (3, 3, 203);
INSERT INTO evaluate (RecordID, MemberID, TrainerID) VALUES (4, 4, 204);
INSERT INTO evaluate (RecordID, MemberID, TrainerID) VALUES (5, 5, 205);

INSERT INTO take_in (CourseID, RoomID) VALUES (1, 1);
INSERT INTO take_in (CourseID, RoomID) VALUES (2, 2);
INSERT INTO take_in (CourseID, RoomID) VALUES (3, 3);
INSERT INTO take_in (CourseID, RoomID) VALUES (4, 4);
INSERT INTO take_in (CourseID, RoomID) VALUES (5, 5);
