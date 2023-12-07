<html>

<head>
    <title>GYM Database </title>
</head>

<body>
    <div class="header">
        <div class="add-record-button" onclick="showInputForm()" style="color: white;">+</div>
        <a href="dashboard.php" class="back-button">Back</a>
    </div>


    <h2>Reset</h2>
    <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

    <form method="POST" action="gym.php">
        <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
        <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
        <p><input type="submit" value="Reset" name="reset"></p>
    </form>

    <hr />

    <h2>Insert the new member</h2>
    <p>Press Insert button to add a member record</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="insertMemberRequest" name="insertMemberRequest">
        MemberID: <input type=integer name="insMemID"> <br /><br />
        Name: <input type="text" name="insMemName"> <br /><br />
        DateJoined: <input type="date" name="insMemDateJoined">
        <p>(DateJoined in the YYYY-MM-DD formate)</p>
        FitnessGoal: <input type="text" name="insMemFitnessGoal"> <br /><br />

        <input type="submit" value="Insert" name="insertSubmit"></p>
    </form>

    <hr />

    <h2>Display a list of courses along with the assigned trainers</h2>
    <p>Join</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="joinTableRequest" name="joinTableRequest">

        <input type="submit" value="Display" name="joinSubmit"></p>
    </form>

    <hr />

    <h2>Find the course that has number of members less than the threshold</h2>
    <p>Aggregation with Having</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findCourseRequest" name="findCourseRequest">
        Threshold: <input type=integer name="numMem"> <br /><br />

        <input type="submit" value="Search" name="searchCourseSubmit"></p>
    </form>

    <hr />

    <h2>Display members who have purchased all group courses: </h2>
    <p>Division</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findCustomerRequest" name="findCustomerRequest">

        <input type="submit" value="Display" name="searchCustomerSubmit"></p>
    </form>

    <hr />

    <h2> Update workout duration in workout history: </h2>
    <p>Update</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="updateWorkoutRequest" name="updateWorkoutRequest">

        <label for="historyID">History ID:</label>
        <input type="text" id="historyID" name="historyID" required><br><br>

        <label for="newDuration">New Duration (in minutes):</label>
        <input type="number" id="newDuration" name="newDuration" required><br><br>

        <input type="submit" value="Update Duration" name="updateDurationSubmit"></p>
    </form>

    <hr />

    <h2>Delete a Workout History Record:</h2>
    <p>Delete</p>
    <form method="POST" action="gym.php">
        <input type="hidden" id="deleteWorkoutRequest" name="deleteWorkoutRequest">

        <label for="deleteHistoryID">History ID to Delete:</label>
        <input type="text" id="deleteHistoryID" name="deleteHistoryID" required><br><br>

        <input type="submit" value="Delete Record" name="deleteRecordSubmit">
    </form>

    <hr />

    <h2>Find Workout History in a Specific Room</h2>
    <p>Selection</p>

    <form method="POST" action="gym.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findWorkoutHistoryRequest" name="findWorkoutHistoryRequest">

        <label for="roomID">Room ID:</label>
        <input type="text" id="roomID" name="roomID" required><br><br>

        <input type="submit" value="Find Workout History" name="findWorkoutHistorySubmit">
    </form>

    <hr />

    <h2>Calculate Overall Average Metabolic Rate Across Different Age Groups</h2>
    <p>Nested Aggregation with Group By</p>

    <form method="POST" action="gym.php">
        <input type="hidden" name="calculateAverageRatesRequest">
        <input type="submit" value="Calculate Average" name="calculateAverageRatesSubmit">
    </form>
    <hr>


    <h2>Find Price of a Course</h2>
    <form method="POST" action="gym.php">
        <input type="hidden" id="findPriceRequest" name="findPriceRequest">
        Course ID: <input type=integer name="CourseID"> <br /><br />
        <input type="submit" value="Find Price" name="findPriceSubmit"></p>
    </form>

    <hr />

    <h2>Find Trainer of a Course</h2>
    <form method="POST" action="gym.php">
        <input type="hidden" id="findTrainerRequest" name="findTrainerRequest">
        Course ID: <input type=integer name="CourseID"> <br /><br />
        <input type="submit" value="Find Trainer" name="findTrainerSubmit"></p>
    </form>

    <hr />

    <h2>Show Total Workouts and Average Workout Duration</h2>
    <form method="POST" action="gym.php">
        <input type="hidden" id="workoutStatsRequest" name="workoutStatsRequest">
        Member ID: <input type="integer" name="MemberID"> <br /><br />
        Month (YYYY-MM): <input type="text" name="Month"> <br /><br />
        <input type="submit" value="Find workout details" name="workoutStatsSubmit"></p>
    </form>

    <hr />

    <?php
    //this tells the system that it's no longer just parsing html; it's now parsing PHP

    $success = True; //keep track of errors so it redirects the page only if there are no errors
    $db_conn = NULL; // edit the login credentials in connectToDB()
    $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

    function debugAlertMessage($message)
    {
        global $show_debug_alert_messages;

        if ($show_debug_alert_messages) {
            echo "<script type='text/javascript'>alert('" . $message . "');</script>";
        }
    }

    function executePlainSQL($cmdstr)
    { //takes a plain (no bound variables) SQL command and executes it
        //echo "<br>running ".$cmdstr."<br>";
        global $db_conn, $success;

        $result = OCIParse($db_conn, $cmdstr);
        //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

        if (!$result) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
            echo htmlentities($e['message']);
            $success = False;
        }

        $r = OCIExecute($result, OCI_DEFAULT);
        if (!$r) {
            echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
            $e = oci_error($result); // For OCIExecute errors pass the resulthandle
            echo htmlentities($e['message']);
            $success = False;
        }

        return $result;
    }

    function executeBoundSQL($cmdstr, $list)
    {
        /* Sometimes the same result will be executed several times with different values for the variables involved in the query.
      In this case you don't need to create the result several times. Bound variables cause a result to only be
      parsed once and you can reuse the result. This is also very useful in protecting against SQL injection.
      See the sample code below for how this function is used */

        global $db_conn, $success;
        $result = OCIParse($db_conn, $cmdstr);

        if (!$result) {
            echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
            $e = OCI_Error($db_conn);
            echo htmlentities($e['message']);
            $success = False;
        }

        foreach ($list as $tuple) {
            foreach ($tuple as $bind => $val) {
                //echo $val;
                //echo "<br>".$bind."<br>";
                OCIBindByName($result, $bind, $val);
                unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
            }

            $r = OCIExecute($result, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($result); // For OCIExecute errors, pass the resulthandle
                echo htmlentities($e['message']);
                echo "<br>";
                $success = False;
            }
        }
    }

    function connectToDB()
    {
        global $db_conn;

        // Your username is ora_(CWL_ID) and the password is a(student number). For example,
        // ora_platypus is the username and a12345678 is the password.
        $db_conn = OCILogon("ora_jhao2002", "a58301110", "dbhost.students.cs.ubc.ca:1522/stu");

        if ($db_conn) {
            debugAlertMessage("Database is Connected");
            return true;
        } else {
            debugAlertMessage("Cannot connect to Database");
            $e = OCI_Error(); // For OCILogon errors pass no handle
            echo htmlentities($e['message']);
            return false;
        }
    }

    function disconnectFromDB()
    {
        global $db_conn;

        debugAlertMessage("Disconnect from Database");
        OCILogoff($db_conn);
    }

    function excuteSingleSql($sql_file_path)
    {
        global $db_conn;

        if (file_exists($sql_file_path)) {
            echo "<br> executing SQL from file <br>";
            $sql_content = file_get_contents($sql_file_path);
            $sql_command = explode(';', $sql_content);

            foreach ($sql_command as $tmp_sql_command) {
                $tmp_sql_command = trim($tmp_sql_command);
                if (!empty($tmp_sql_command)) {
                    executePlainSQL($tmp_sql_command);
                }
            }
        } else {
            echo "<br> ERROR finding sql file <br>";
            return false;
        }
    }

    function handleResetRequest()
    {
        global $db_conn;

        $sql_file_path = 'gym.sql';
        excuteSingleSql($sql_file_path);
        OCICommit($db_conn);
    }

    function handleInsertRequest() {
        global $db_conn;
        $date = DateTime::createFromFormat('Y-m-d', $_POST['insMemDateJoined']);
        $formattedDate = $date ? $date->format('d-M-y') : null;
    
        if ($formattedDate) {
            $memberID = $_POST['insMemID'];
            $query = "SELECT * FROM Member WHERE MemberID = :bindID";
            $statement = OCIParse($db_conn, $query);
            OCIBindByName($statement, ":bindID", $memberID);
            OCIExecute($statement);
            if (OCIFetch($statement)) {
                echo "Error: A member with this ID already exists.";
            } else {
                $tuple = array(
                    ":bind1" => $memberID,
                    ":bind2" => $_POST['insMemName'],
                    ":bind3" => $formattedDate,
                    ":bind4" => $_POST['insMemFitnessGoal']
                );
    
                $alltuples = array($tuple);
    
                $insertResult = executeBoundSQL("INSERT INTO Member VALUES (:bind1, :bind2, :bind3, :bind4)", $alltuples);
                OCICommit($db_conn);
    
                $result = executePlainSQL("SELECT * FROM Member");
                printDisplayInsertResult($result);
            }
        } else {
            echo "Error: Invalid date format.";
        }
    }

    function printDisplayInsertResult($result)
    {
        if ($result === false) {
            echo "<p>Insert failed. Please check the data and try again.</p>";
        } else {
            
            echo "<br>Successfully inserted and retrieved data from table Member:<br>";
            echo "<table>";
            echo "<tr><th>MemberID</th><th>Name</th><th>DateJoined</th><th>FitnessGoal</th></tr>";

            $hasRows = false;
            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["MEMBERID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["DATEJOINED"] . "</td><td>" . $row["FITNESSGOAL"] . "</td></tr>"; //or just use "echo $row[0]"
            }
        }
        
    }

    function handleJoinRequest()
    {
        global $db_conn;
        $threshold = $_POST['numMem'];

        $result = executePlainSQL("SELECT t.CourseID, tr.TrainerID, tr.Name
                                      FROM Trainer tr, teach t 
                                      WHERE tr.TrainerID = t.TrainerID");
        printJoinResult($result);
    }

    function printJoinResult($result)
    {
        echo "<br>Joined the Trainer and teach tables<br>";
        echo "<table>";
        echo "<tr><th>CourseID</th><th>TrainerID</th><th>Name</th></tr>";

        $hasRows = false;
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["COURSEID"] . "</td><td>" . $row["TRAINERID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
        }

        echo "</table>";
    }

    function handleDisplayInsertRequest()
    {
        global $db_conn;
        $result = executePlainSQL("SELECT * FROM Member");
        printDisplayInsertResult($result);
    }

    function handleHavingRequest()
    {
        global $db_conn;
        $threshold = $_POST['numMem'];

        $result = executePlainSQL("SELECT CourseID, COUNT(MemberID) AS NumberOfMembers
                                      FROM purchase
                                      GROUP BY CourseID
                                      HAVING COUNT(MemberID) < $threshold");
        printHavingResult($result);
    }

    function printHavingResult($result)
    {
        echo "<br>Courses with number of members below the threshold:<br>";
        echo "<table>";
        echo "<tr><th>CourseID</th><th>NumberOfMembers</th></tr>";

        $hasRows = false;
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            $hasRows = true;
            echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
        }

        echo "</table>";

        if (!$hasRows) {
            echo "ALL courses has the number of members over the threshold!!";
        }
    }

    function handleDivisionRequest()
    {
        global $db_conn;
        $result = executePlainSQL("SELECT DISTINCT m.MemberID, m.Name
                                      FROM Member m 
                                      WHERE NOT EXISTS (
                                          (SELECT CourseID FROM GroupSession)
                                          MINUS
                                          (SELECT p.CourseID 
                                          FROM Purchase p, GroupSession gs
                                          WHERE p.CourseID = gs.CourseID AND 
                                                m.MemberID = p.MemberID)
                                      )");

        printDivisionResult($result);
    }

    function printDivisionResult($result)
    {
        echo "<br>Retrieved members who have purchased all group courses:<br>";
        echo "<table>";
        echo "<tr><th>MemberID</th><th>Name</th></tr>";

        $hasRows = false;
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            $hasRows = true;
            echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
        }

        echo "</table>";

        if (!$hasRows) {
            echo "No one satisfies this condition.";
        }
    }

    function handleUpdateRequest()
    {
        global $db_conn;

        $historyID = $_POST['HistoryID'];
        $newDuration = $_POST['newDuration'];
        $exerciseID = $_POST['ExerciseID'];
        $result = [
            'WorkoutHistory1' => 0,
            'WorkoutHistory2' => 0
        ];

        // Update the WorkoutHistory1 table
        $query1 = "UPDATE WorkoutHistory1 SET Duration='" . $newDuration . "' WHERE HistoryID='" . $historyID . "'";
        $result['WorkoutHistory1'] = executePlainSQL($query1);

        // Update the WorkoutHistory2 table
        $query2 = "UPDATE WorkoutHistory2 SET Duration='" . $newDuration . "' WHERE ExerciseID='" . $exerciseID . "'";
        $result['WorkoutHistory2'] = executePlainSQL($query2);

        oci_commit($db_conn);
        printUpdateResult($result);
    }


    function printUpdateResult($result)
    {
        if ($result === false) {
            echo "<p>Update failed. Please check the data and try again.</p>";
        } else {
            if ($result > 0) {
                echo "<p>Update successful!</p>";
                echo "<p>Number of rows updated: " . $result . "</p>";
            } else {
                echo "<p>Update operation was successful, but no rows were affected.</p>";
            }
        }
    }

    function handleDeleteRequest()
    {
        global $db_conn;

        $historyID = $_POST['deleteHistoryID'];
        $query = "DELETE FROM WorkoutHistory1 WHERE HistoryID='" . $historyID . "'";
        $result = executePlainSQL($query);

        oci_commit($db_conn);
        printDeleteResult($result);
    }

    function printDeleteResult($result)
    {
        if ($result) {
            echo "<p>Delete operation successful.</p>";
        } else {
            echo "<p>Delete operation failed. Please check the data and try again.</p>";
        }
    }

    function handleFindWorkoutHistoryRequest()
    {
        global $db_conn;
        $roomID = $_POST["roomID"];
        $query = "SELECT * FROM WorkoutHistory1 WHERE RoomID = :roomID";
        $result = oci_parse($db_conn, $query);
        oci_bind_by_name($result, ":roomID", $roomID);

        oci_execute($result);
        printFindWorkoutHistoryResult($result);
    }


    function printFindWorkoutHistoryResult($result)
    {
        echo "<br>Workout History in the specified room:<br>";
        echo "<table>";
        echo "<tr><th>HistoryID</th><th>DateTime</th><th>ExerciseID</th><th>Duration</th><th>RoomID</th><th>MemberID</th></tr>";

        $hasRows = false;
        while ($row = oci_fetch_array($result, OCI_ASSOC)) {
            $hasRows = true;
            echo "<tr><td>" . $row["HISTORYID"] . "</td><td>" . $row["DATETIME"] . "</td><td>" . $row["EXERCISEID"] . "</td><td>" . $row["DURATION"] . "</td><td>" . $row["ROOMID"] . "</td><td>" . $row["MEMBERID"] . "</td></tr>";
        }

        echo "</table>";

        if (!$hasRows) {
            echo "No workout history found for the specified room.";
        }
    }

    function handleCalculateAverage()
    {
        global $db_conn;

        $query = "SELECT AVG(AverageMetabolicRate) AS OverallAverageMetabolicRate
                  FROM (
                      SELECT 
                        CASE 
                          WHEN Age < 30 THEN 'Under 30'
                          WHEN Age BETWEEN 30 AND 40 THEN '30-40'
                          ELSE 'Over 40'
                        END AS AgeGroup,
                        AVG(MetabolicRate) AS AverageMetabolicRate
                      FROM assessed_BodyAnalysisRecord1
                      GROUP BY 
                        CASE 
                          WHEN Age < 30 THEN 'Under 30'
                          WHEN Age BETWEEN 30 AND 40 THEN '30-40'
                          ELSE 'Over 40'
                        END
                  )";


        $result = oci_parse($db_conn, $query);

        if (!$result || !oci_execute($result)) {
            echo "<p>Error executing query.</p>";
            return;
        }

        printCalculateAverageResult($result);
    }

    function printCalculateAverageResult($result)
    {
        echo "<h2>Overall Average Metabolic Rate Across Different Age Groups:</h2>";

        $row = oci_fetch_array($result, OCI_ASSOC);

        if ($row && isset($row['OVERALLAVERAGEMETABOLICRATE'])) {
            // Round the number to 2 decimal places
            $averageRate = round($row['OVERALLAVERAGEMETABOLICRATE'], 2);
            echo "<p>The overall average metabolic rate across age groups is around: " . htmlspecialchars($averageRate) . "</p>";
        } else {
            echo "<p>No data available.</p>";
        }
    }


    function handleFindPriceRequest()
    {
        global $db_conn;

        // Get the course name from the GET request
        $CourseID = $_POST['CourseID'];

        // Execute a query to retrieve the Price of the specified course
        $result = executePlainSQL("SELECT c.CourseID, c.Price FROM Course c WHERE c.CourseID = $CourseID");

        printFindPriceResult($result);
    }


    function printFindPriceResult($result)
    {
        $row = OCI_Fetch_Array($result, OCI_BOTH);

        if ($row) {
            echo "<p>CourseID: " . $row[0] . ", Price: " . $row[1] . "</p>";
        } else {
            echo "<p>This course doesn't exist!</p>";
        }
    }



    function handleFindTrainerRequest()
    {
        global $db_conn;

        $CourseID = $_POST['CourseID'];

        // Get the TrainerID and Name for the given CourseID using a single SQL query
        $query = "SELECT Trainer.TrainerID, Trainer.Name 
                    FROM teach 
                    JOIN Trainer ON teach.TrainerID = Trainer.TrainerID 
                    WHERE teach.CourseID = $CourseID";

        $result = executePlainSQL($query);
        printFindTrainerResult($result);
    }


    function printFindTrainerResult($result)
    {
        $row = OCI_Fetch_Array($result, OCI_BOTH);
        if ($row) {
            echo "<p>Trainer ID: " . $row[0] . ", Trainer Name: " . $row[1] . "</p>";
        } else {
            echo "<p>This course doesn't exist or has no assigned trainer!</p>";
        }
    }



    function handleWorkoutStatsRequest()
    {
        global $db_conn;

        $MemberID = $_POST['MemberID'];
        $Month = $_POST['Month'];

        // Execute a query to retrieve total workouts and average workout duration for the specified month and user
        $result = executePlainSQL("
              SELECT COUNT(*) AS TotalWorkouts, AVG(Duration) AS AverageDuration
              FROM WorkoutHistory1
              WHERE MemberID = $MemberID AND TO_CHAR(DateTime, 'YYYY-MM') = '$Month'
          ");

        printWorkoutStatsResult($result);
    }

    function printWorkoutStatsResult($result)
    {
        $row = OCI_Fetch_Array($result, OCI_BOTH);

        if ($row) {
            echo "<p>Total Workouts: " . $row['TOTALWORKOUTS'] . ", Average Duration: " . round($row['AVERAGEDURATION'], 2) . " minutes</p>";
        } else {
            echo "<p>No workouts found for the specified month and user!</p>";
        }
    }




    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('resetTablesRequest', $_POST)) {
                handleResetRequest();
            } else if (array_key_exists('insertMemberRequest', $_POST)) {
                handleInsertRequest();
            } else if (array_key_exists('joinTableRequest', $_POST)) {
                handlejoinRequest();
            } else if (array_key_exists('findCourseRequest', $_POST)) {
                handleHavingRequest();
            } else if (array_key_exists('findCustomerRequest', $_POST)) {
                handleDivisionRequest();
            } else if (array_key_exists('updateWorkoutRequest', $_POST)) {
                handleUpdateRequest();
            } else if (array_key_exists('deleteWorkoutRequest', $_POST)) {
                handleDeleteRequest();
            } else if (array_key_exists('findWorkoutHistoryRequest', $_POST)) {
                handleFindWorkoutHistoryRequest();
            } else if (array_key_exists('calculateAverageRatesRequest', $_POST)) {
                handleCalculateAverage();
            } else if (array_key_exists('findPriceRequest', $_POST)) {
                handleFindPriceRequest();
            } else if (array_key_exists('findTrainerRequest', $_POST)) {
                handleFindTrainerRequest();
            } else if (array_key_exists('workoutStatsSubmit', $_POST)) {
                handleWorkoutStatsRequest();
            }

            disconnectFromDB();
        }
    }

    // HANDLE ALL GET ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handleGETRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('findCustomerRequest', $_POST)) {
                handleDivisionRequest();
                handleCalculateAverage();
            }

            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['insertSubmit']) || isset($_POST['joinSubmit']) || isset($_POST['searchCourseSubmit']) || isset($_POST['searchCustomerSubmit']) || isset($_POST['updateDurationSubmit']) || isset($_POST['deleteRecordSubmit']) || isset($_POST['findWorkoutHistorySubmit']) || isset($_POST['calculateAverageRatesSubmit']) || isset($_POST['findPriceSubmit']) || isset($_POST['findTrainerSubmit']) || isset($_POST['workoutStatsSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['x'])) {
        handleGETRequest();
    }
    ?>
</body>

</html>