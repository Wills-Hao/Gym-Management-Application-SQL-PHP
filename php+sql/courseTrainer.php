<html>

<head>
    <meta charset="UTF-8">
    <style>
        <?php include 'css/courseTrainer.css'; ?>
    </style>
    <title>Course and Trainer</title>
</head>

<body>
    <div class="header">
        <h1>Course and Trainer Info</h1>
        <a href="dashboard.php" class="back-button">Back</a>
    </div>

    <form method="GET" action="courseTrainer.php">
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
        echo "<br>Retrieved data from Course Table:<br>";
        echo '<table>';
        echo '<tr><th>Course ID</th><th>Start Date</th><th>Price</th><th>Duration</th></tr>';
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['COURSEID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['START_DATE']) . '</td>';
            echo '<td>' . htmlspecialchars($row['PRICE']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DURATION']) . '</td>';
            echo '</tr>';
        }
        echo "</table>";
    }

    function printTable_2($result)
    {
        echo "<br>Retrieved data from Trainer Table:<br>";
        echo "<table>";
        echo "<tr><th>Trainer ID</th><th>Name</th><th>Expertise</th><th>Available Hours Daily</th></tr>";
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['TRAINERID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['NAME']) . '</td>';
            echo '<td>' . htmlspecialchars($row['EXPERTISE']) . '</td>';
            echo '<td>' . htmlspecialchars($row['AVAILABLEHOURSDAILY']) . '</td>';
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



    function handlePrintTableRequest($tableName)
    {
        global $db_conn;

        $result = executePlainSQL("SELECT * FROM $tableName");

        if ($tableName == 'Course') {
            printTable_1($result);
        } elseif ($tableName == 'Trainer') {
            printTable_2($result);
        }

        OCICommit($db_conn);
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

    function handleHavingRequest()
    {
        global $db_conn;
        $threshold = $_POST['numMem'];

        // Validate input: check if threshold is set and is numeric
        if (!isset($threshold) || !is_numeric($threshold)) {
            echo "<p>Invalid input: Threshold (number of members) is required and must be numeric.</p>";
            return;
        }

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

    function handleFindPriceRequest()
    {
        global $db_conn;

        $CourseID = $_POST['CourseID'];

        // Validate input: check if CourseID is set and is numeric
        if (!isset($CourseID) || !is_numeric($CourseID)) {
            echo "<p>Invalid input: CourseID is required and must be numeric.</p>";
            return;
        }

        // Execute a query to retrieve the Price of the specified course
        $query = "SELECT c.CourseID, c.Price FROM Course c WHERE c.CourseID = $CourseID";
        $result = executePlainSQL($query);

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

        // Validate input: check if CourseID is set and is numeric
        if (!isset($CourseID) || !is_numeric($CourseID)) {
            echo "<p>Invalid input: CourseID is required and must be numeric.</p>";
            return; // Exit the function if input is invalid
        }

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

    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('resetTablesRequest', $_POST)) {
                handleResetRequest();
            } elseif (array_key_exists('joinTableRequest', $_POST)) {
                handlejoinRequest();
            } elseif (array_key_exists('findCourseRequest', $_POST)) {
                handleHavingRequest();
            } else if (array_key_exists('findPriceRequest', $_POST)) {
                handleFindPriceRequest();
            } else if (array_key_exists('findTrainerRequest', $_POST)) {
                handleFindTrainerRequest();
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
                handlePrintTableRequest('Course');
                handlePrintTableRequest('Trainer');
            }
            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['joinSubmit']) || isset($_POST['searchCourseSubmit']) || isset($_POST['findPriceSubmit']) || isset($_POST['findTrainerSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest'])) {
        handleGETRequest();
    }
    ?>
    <h2>Reset</h2>
    <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>
    <form method="POST" action="courseTrainer.php">
        <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
        <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
        <button type="submit" name="reset" id="function-button">Reset</button>
    </form>
    <hr />

    <h2>Display a list of courses along with the assigned trainers</h2>
    <p>(Join)</p>

    <form method="POST" action="courseTrainer.php"> <!--refresh page when submitted-->
        <input type="hidden" id="joinTableRequest" name="joinTableRequest">
        <button type="submit" name="joinSubmit" id="function-button">Display</button>
    </form>

    <hr />

    <h2>Find the course that has number of members less than the threshold</h2>
    <p>(Aggregation with Having)</p>

    <form method="POST" action="courseTrainer.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findCourseRequest" name="findCourseRequest">
        Threshold: <input type=integer name="numMem"> <br /><br />
        <button type="submit" name="searchCourseSubmit" id="function-button">Search</button>
    </form>
    <hr />

    <h2>Find Price of a Course</h2>
    <p>(Projection)</p>
    <form method="POST" action="courseTrainer.php">
        <input type="hidden" id="findPriceRequest" name="findPriceRequest">
        Course ID: <input type=integer name="CourseID"> <br /><br />
        <button type="submit" name="findPriceSubmit" id="function-button">Find Price</button>
    </form>

    <hr />

    <h2>Find Trainer of a Course</h2>
    <p>(Join&Projection)</p>
    <form method="POST" action="courseTrainer.php">
        <input type="hidden" id="findTrainerRequest" name="findTrainerRequest">
        Course ID: <input type=integer name="CourseID"> <br /><br />
        <button type="submit" name="findTrainerSubmit" id="function-button">Find Trainer</button>
    </form>

    <hr />

</body>

</html>