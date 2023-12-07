<html>

<head>
    <meta charset="UTF-8">
    <style>
        <?php include 'css/member.css'; ?>
    </style>
    <title>Member Profile</title>
</head>

<body>
    <div class="header">
        <h1>Member Info</h1>
        <a href="dashboard.php" class="back-button">Back</a>
    </div>

    <form method="GET" action="member.php">
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


    function printTable($result)
    {
        echo "<br>Retrieved data from Member Table:<br>";
        echo '<table>';
        echo '<tr><th>Member ID</th><th>Date Joined</th><th>Fitness Goal</th></tr>';
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['MEMBERID']) . '</td>';
            echo '<td>' . htmlspecialchars($row['DATEJOINED']) . '</td>';
            echo '<td>' . htmlspecialchars($row['FITNESSGOAL']) . '</td>';
            echo '</tr>';
        }
        echo "</table>";
    }

    function printTable2($result)
    {
        echo "<br>Retrieved data from Body Analysis Record:<br>";
        echo '<table>';
        echo '<tr><th>Age</th><th>Weight</th><th>Height</th><th>Metabolic Rate</th></tr>';
        while ($row = oci_fetch_assoc($result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['AGE']) . '</td>';
            echo '<td>' . htmlspecialchars($row['WEIGHT']) . '</td>';
            echo '<td>' . htmlspecialchars($row['HEIGHT']) . '</td>';
            echo '<td>' . htmlspecialchars($row['METABOLICRATE']) . '</td>';
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

        if ($tableName == 'Member') {
            printTable($result);
        } elseif ($tableName == 'assessed_BodyAnalysisRecord1') {
            printTable2($result);
        }

        OCICommit($db_conn);
    }

    function handleInsertRequest()
    {
        global $db_conn;
        $date = DateTime::createFromFormat('Y-m-d', $_POST['insMemDateJoined']);
        $formattedDate = $date ? $date->format('d-M-y') : null;

        $memberID = $_POST['insMemID'];
        $memberName = $_POST['insMemName'];
        $fitnessGoal = $_POST['insMemFitnessGoal'];

        if (empty($memberID) || empty($memberName) || empty($fitnessGoal)) {
            echo "Error: All fields are required.";
            return;
        }

        if (!is_numeric($memberID)) {
            echo "Error: MemberID must be numeric.";
            return;
        }

        if (!is_string($memberName) || strlen($memberName) > 100) { 
            echo "Error: Invalid Member Name.";
            return;
        }
        if (!is_string($fitnessGoal) || strlen($fitnessGoal) > 100) {
            echo "Error: Invalid Fitness Goal.";
            return;
        }
        
        if ($formattedDate) {
            $memberID = $_POST['insMemID'];
            $query = "SELECT * FROM Member WHERE MemberID = :bindID";
            $statement = OCIParse($db_conn, $query);
            OCIBindByName($statement, ":bindID", $memberID);
            OCIExecute($statement);
            if (OCIFetch($statement)) {
                echo "Error: A member with this ID already exists.";
            } else {
                if (!OCIFetch($statement)) { 
                    $insertQuery = "INSERT INTO Member VALUES (:bind1, :bind2, :bind3, :bind4)";
                    $insertStmt = OCIParse($db_conn, $insertQuery);
    
                    OCIBindByName($insertStmt, ":bind1", $memberID);
                    OCIBindByName($insertStmt, ":bind2", $memberName);
                    OCIBindByName($insertStmt, ":bind3", $formattedDate);
                    OCIBindByName($insertStmt, ":bind4", $fitnessGoal);
        
                    if (OCIExecute($insertStmt)) {
                        OCICommit($db_conn);
                        echo "The member has been successfully inserted.";
                    } else {
                        echo "Error: The insert operation failed.";
                        $e = oci_error($insertStmt);
                        echo htmlentities($e['message']);
                    }
                }
            } 
        } else {
            echo "Error: Invalid date format.";
        }    
    }


    function handleDisplayInsertRequest()
    {
        global $db_conn;
        $result = executePlainSQL("SELECT * FROM Member");
        printDisplayInsertResult($result);
    }

    function printDisplayInsertResult($result)
    {       
        echo "<br>Successfully inserted and retrieved data from table Member:<br>";
        echo "<table>";
        echo "<tr><th>MemberID</th><th>Name</th><th>DateJoined</th><th>FitnessGoal</th></tr>";

        $hasRows = false;
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            echo "<tr><td>" . $row["MEMBERID"] . "</td><td>" . $row["NAME"] . "</td><td>" . $row["DATEJOINED"] . "</td><td>" . $row["FITNESSGOAL"] . "</td></tr>"; //or just use "echo $row[0]"
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

    function handleCalculateAverage()
    {
        global $db_conn;
        $result = executePlainSQL( 
                    "SELECT 
                        CASE 
                        WHEN Age < 14 THEN 'Youth (under 14)'
                        WHEN Age BETWEEN 14 AND 17 THEN 'Teens (14~17)'
                        WHEN Age BETWEEN 18 AND 29 THEN 'Yougn Adults (18~29)'
                        WHEN Age BETWEEN 30 AND 39 THEN 'Adults (30~39)'
                        ELSE 'Older Adults (Over 49)'
                        END AS AgeGroup,
                        AVG(MetabolicRate) AS AverageMetabolicRate
                    FROM assessed_BodyAnalysisRecord1
                    GROUP BY 
                        CASE 
                        WHEN Age < 14 THEN 'Youth (under 14)'
                        WHEN Age BETWEEN 14 AND 17 THEN 'Teens (14~17)'
                        WHEN Age BETWEEN 18 AND 29 THEN 'Yougn Adults (18~29)'
                        WHEN Age BETWEEN 30 AND 39 THEN 'Adults (30~39)'
                        ELSE 'Older Adults (Over 49)'
                        END");

        printCalculateAverageResult($result);
    }

    function printCalculateAverageResult($result)
    {
        echo "<br>Average Metabolic Rates For Different Age Groups:<br>";
        echo "<table>";
        echo "<tr><th>AGEGROUP</th><th>AVERAGEMETABOLICRATE</th></tr>";

        $hasRows = false;
        while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
            $hasRows = true;
            echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>";
        }

        echo "</table>";

        if (!$hasRows) {
            echo "No data available.";
        }
    }

    function handleUpdateRequest()
    {
        global $db_conn;

        $memberID = $_POST['MemberID'];
        $new_fitness_goal = $_POST['newFitnessGoal']; // Assuming the form has a 'newFitnessGoal' field

        // Validate inputs
        if (!isset($memberID) || !is_numeric($memberID)) {
            echo "Invalid input: MemberID is required and must be numeric.";
            return; 
        }
        if (!isset($new_fitness_goal) || !is_string($new_fitness_goal) || strlen($new_fitness_goal) > 100) {
            // Example length check of 100 characters
            echo "Invalid input: New Fitness Goal is required and must be a valid string.";
            return; 
        }

        // Update the FitnessGoal in the Member table
        $query = "UPDATE Member SET FitnessGoal='" . $new_fitness_goal . "' WHERE MemberID='" . $memberID . "'";
        $result = executePlainSQL($query);

        // Check if the update was successful
        $rowsAffected = oci_num_rows($result);
        if ($rowsAffected > 0) {
            oci_commit($db_conn);
            echo "Update successful. $rowsAffected row(s) updated.";

            // Optionally, verify the update in the database
            $verifyQuery = "SELECT FitnessGoal FROM Member WHERE MemberID='" . $memberID . "'";
            $verifyResult = executePlainSQL($verifyQuery);
            $row = oci_fetch_array($verifyResult, OCI_BOTH);
            if ($row) {
                echo " New Fitness Goal is now: " . $row['FitnessGoal'];
            } else {
                echo " But unable to verify the update in the database.";
            }
        } else {
            echo "Update failed or no changes were made.";
        }
    }




    // HANDLE ALL POST ROUTES
    // A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
    function handlePOSTRequest()
    {
        if (connectToDB()) {
            if (array_key_exists('resetTablesRequest', $_POST)) {
                handleResetRequest();
            } elseif (array_key_exists('insertMemberRequest', $_POST)) {
                handleInsertRequest();
                handlePrintTableRequest('Member');
            } elseif (array_key_exists('findCustomerRequest', $_POST)) {
                handleDivisionRequest();
            } else if (array_key_exists('calculateAverageRatesRequest', $_POST)) {
                handleCalculateAverage();
            } else if (array_key_exists('updateGoalRequest', $_POST)) {
                handleUpdateRequest();
                handlePrintTableRequest('Member');
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
                handlePrintTableRequest('Member');
                handlePrintTableRequest('assessed_BodyAnalysisRecord1');
            }
            disconnectFromDB();
        }
    }

    if (isset($_POST['reset']) || isset($_POST['insertSubmit']) || isset($_POST['updateSubmit']) || isset($_POST['searchCustomerSubmit']) || isset($_POST['calculateAverageRatesSubmit'])) {
        handlePOSTRequest();
    } else if (isset($_GET['printTableRequest'])) {
        handleGETRequest();
    }
    ?>
    <h2>Reset</h2>
    <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>
    <form method="POST" action="member.php">
        <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
        <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
        <button type="submit" name="reset" id="function-button">Reset</button>
    </form>
    <hr />

    <h2>Insert the new member</h2>
    <p>(Insert)</p>
    <p>Press Insert button to add a member record</p>

    <form method="POST" action="member.php"> <!--refresh page when submitted-->
        <input type="hidden" id="insertMemberRequest" name="insertMemberRequest">
        MemberID: <input type=integer name="insMemID"> <br /><br />
        Name: <input type="text" name="insMemName"> <br /><br />
        DateJoined: <input type="date" name="insMemDateJoined">
        <p>(DateJoined in the YYYY-MM-DD formate)</p>
        FitnessGoal: <input type="text" name="insMemFitnessGoal"> <br /><br />
        <button type="submit" name="insertSubmit" id="function-button">Insert</button>
    </form>

    <hr />


    <h2>Update the Member's Fitness Goal</h2>
    <p>(Update)</p>

    <form method="POST" action="member.php">
        <input type="hidden" id="updateGoalRequest" name="updateGoalRequest">
        Member ID: <input type="integer" name="MemberID"> <br /><br />
        New Goal: <input type="text" name="newFitnessGoal"> <br /><br />
        <button type="submit" name="updateSubmit" id="function-button">Update</button>
    </form>

    <hr />


    <h2>Display members who have purchased all group courses: </h2>
    <p>(Division)</p>

    <form method="POST" action="member.php"> <!--refresh page when submitted-->
        <input type="hidden" id="findCustomerRequest" name="findCustomerRequest">
        <button type="submit" name="searchCustomerSubmit" id="function-button">Display</button>
    </form>

    <hr />

    <h2>Calculate Average Metabolic Rates For Different Age Groups</h2>
    <p>(Nested Aggregation with Group By)</p>

    <form method="POST" action="member.php">
        <input type="hidden" id="calculateAverageRatesRequest" name="calculateAverageRatesRequest">
        <button type="submit" name="calculateAverageRatesSubmit" id="function-button">Calculate Average</button>
    </form>
    <hr>

</body>

</html>