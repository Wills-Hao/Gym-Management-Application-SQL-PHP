<html>

<head>
    <meta charset="UTF-8">
    <style>
        <?php include 'css/workoutHistory.css'; ?>
    </style>
    <title>Workout History</title>
</head>

<body>
    <div class="header">
        <h1>Workout History</h1>
        <a href="dashboard.php" class="back-button">Back</a>
    </div>

    <form method="GET" action="workoutHistoryTable.php">
        <input type="hidden" id="printTableRequest" name="printTableRequest">
        <button type="submit" name="printTable" id="function-button">Print Table</button>
    </form>



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


    function printTable_1($result)
    {
        echo "<br>Retrieved data from Workout History Table 1:<br>";
        echo '<table>';
        echo '<tr><th>History ID</th><th>Date and Time</th><th>Exercise ID</th><th>Duration</th><th>Room ID</th><th>Member ID</th></tr>';
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['HISTORYID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DATETIME']) . '</td>';
            echo '<td>' . htmlspecialchars($row['EXERCISEID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DURATION']) . '</td>';
            echo '<td>' . htmlspecialchars($row['ROOMID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['MEMBERID']) . '</td>';
            echo '</tr>';
        }
        echo "</table>";
    }

    function printTable_2($result)
    {
        echo "<br>Retrieved data from Workout History Table 2:<br>";
        echo "<table>";
        echo "<tr><th>Exercise ID</th><th>Duration</th><th>Calories Burned</th></tr>";
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['EXERCISEID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DURATION']) . '</td>';
            echo '<td>' . htmlspecialchars($row['CALORIESBURNED']) . '</td>';
            echo '</tr>';
        }
        echo "</table>";
    }



    function handleResetRequest()
    {
        global $db_conn;

        $sql_file_path = 'gym.sql';
        excuteSingleSql($sql_file_path);
        OCICommit($db_conn);
    }

    function handleDeleteRequest()
    {
        global $db_conn;

        $historyID = $_POST['deleteHistoryID'];

        // Validate input: check if historyID is set and is a valid format (e.g., numeric)
        if (!isset($historyID) || !is_numeric($historyID)) {
            echo "<p>Invalid input: HistoryID is required and must be numeric.</p>";
            return; // Exit the function if input is invalid
        }

        $query = "DELETE FROM WorkoutHistory1 WHERE HistoryID='" . $historyID . "'";
        $result = executePlainSQL($query);

        // Get the number of rows affected by the DELETE operation
        $affectedRows = oci_num_rows($result);

        oci_commit($db_conn);
        printDeleteResult($result, $affectedRows);
    }

    function printDeleteResult($result, $affectedRows)
    {
        if ($result && $affectedRows > 0) {
            echo "<p>Delete operation successful.</p>";
        } else if ($result && $affectedRows === 0) {
            echo "<p>Delete operation failed: HistoryID not found.</p>";
        } else {
            echo "<p>Delete operation failed. Please check the data and try again.</p>";
        }
    }

    function handlePrintTableRequest($tableName)
    {
        global $db_conn;
        $result = executePlainSQL("SELECT * FROM $tableName");

        if ($tableName == 'WorkoutHistory1') {
            printTable_1($result);
        } elseif ($tableName == 'WorkoutHistory2') {
            printTable_2($result);
        }

        OCICommit($db_conn);
    }


    function handleFindWorkoutHistoryRequest()
    {
        global $db_conn;
        $roomID = $_POST["roomID"];

        // Validate input: check if roomID is set and is a valid format (e.g., numeric)
        if (!isset($roomID) || !is_numeric($roomID)) {
            echo "<p>Invalid input: RoomID is required and must be numeric.</p>";
            return; // Exit the function if input is invalid
        }

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


    function handleWorkoutStatsRequest()
    {
        global $db_conn;

        $MemberID = $_POST['MemberID'];
        $Month = $_POST['Month'];

        // Validate input: check if MemberID is set and is numeric
        if (!isset($MemberID) || !is_numeric($MemberID)) {
            echo "<p>Invalid input: MemberID is required and must be numeric.</p>";
            return; // Exit the function if MemberID input is invalid
        }

        // Validate input: check if Month is set and matches the format 'YYYY-MM'
        if (!isset($Month) || !preg_match('/^\d{4}-\d{2}$/', $Month)) {
            echo "<p>Invalid input: Month is required and must be in 'YYYY-MM' format.</p>";
            return; // Exit the function if Month input is invalid
        }

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
            } elseif (array_key_exists('deleteWorkoutRequest', $_POST)) {
                handleDeleteRequest();
                handlePrintTableRequest('WorkoutHistory1');
            } else if (array_key_exists('findWorkoutHistoryRequest', $_POST)) {
                handleFindWorkoutHistoryRequest();
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
            if (array_key_exists('countTuples', $_GET)) {
                // handleCountRequest();
            } elseif (array_key_exists('printTableRequest', $_GET)) {
                handlePrintTableRequest('WorkoutHistory1');
            }
            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['deleteRecordSubmit']) || isset($_POST['findWorkoutHistorySubmit']) || isset($_POST['workoutStatsSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest'])) {
        handleGETRequest();
    }
    ?>
    <h2>Reset</h2>
    <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>
    <form method="POST" action="workoutHistoryTable.php">
        <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
        <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
        <button type="submit" name="reset" id="function-button">Reset</button>
    </form>
    <hr />


    <h2>Delete a Workout History Record:</h2>
    <p>(Delete)</p>
    <form method="POST" action="workoutHistoryTable.php">
        <input type="hidden" id="deleteWorkoutRequest" name="deleteWorkoutRequest">

        <label for="deleteHistoryID">History ID to Delete:</label>
        <input type="text" id="deleteHistoryID" name="deleteHistoryID" required><br><br>
        <button type="submit" name="deleteRecordSubmit" id="function-button">Delete Record</button>
    </form>
    <hr />

    <h2>Find Workout History in a Specific Room</h2>
    <p>(Selection)</p>

    <form method="POST" action="workoutHistoryTable.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findWorkoutHistoryRequest" name="findWorkoutHistoryRequest">

        <label for="roomID">Room ID:</label>
        <input type="text" id="roomID" name="roomID" required><br><br>
        <button type="submit" name="findWorkoutHistorySubmit" id="function-button">Find Workout History</button>
    </form>
    <hr />


    <h2>Show Total Workouts and Average Workout Duration</h2>
    <p>(Aggregation with Group By)</p>
    <form method="POST" action="workoutHistoryTable.php">
        <input type="hidden" id="workoutStatsRequest" name="workoutStatsRequest">
        Member ID: <input type="integer" name="MemberID"> <br /><br />
        Month (YYYY-MM): <input type="text" name="Month"> <br /><br />
        <button type="submit" name="workoutStatsSubmit" id="function-button">Find workout details</button>
    </form>
    <hr />

</body>

</html>