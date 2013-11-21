<!--Test Oracle file for UBC CPSC304 2011 Winter Term 2
  Created by Jiemin Zhang
  Modified by Simona Radu
  This file shows the very basics of how to execute PHP commands
  on Oracle.   
  specifically, it will drop a table, create a table, insert values
  update values, and then query for values
 
  IF YOU HAVE A TABLE CALLED "tab1" IT WILL BE DESTROYED
 
  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the  
  OCILogon below to be your ORACLE username and password -->
 
<p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>
<form method="POST" action="EventsHardCode.php">
    
<p><input type="submit" value="Reset" name="reset"></p>
</form>
 
<p>Insert values into event below:</p>
<p><font size="2"> Number&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  
Name</font></p>
<form method="POST" action="EventsHardCode.php">
<!--refresh page when submit-->

 <!--watch out for START TIME AND END TIME MUST BE WORKED OUT BETTER!!! -->
   <p><input type="text" name="eid" size="9"><input type="text" name="etitle"  
size="300"><input type="text" name="edescription"  
size="300"><input type="text" name="startTime"  
size="10"><input type="text" name="end"  
size="10"><input type="text" name="street_address"  
size="100"><input type="text" name="building"  
size="100"><input type="text" name="userid"  
size="9">
<!--define two variables to pass the value-->
    
<input type="submit" value="insert" name="insertsubmit"></p>
</form>
<!-- create a form to pass the values. See below for how to  
get the values-->  
 
<p> Update the name by inserting the old and new values below: </p>
<p><font size="2"> Old Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  
New Name</font></p>
<form method="POST" action="EventsHardCode.php">
<!--refresh page when submit-->
 
   <p><input type="text" name="oldName" size="6"><input type="text" name="newName"  
size="18">
<!--define two variables to pass the value-->
    
<input type="submit" value="update" name="updatesubmit"></p>
<input type="submit" value="run hardcoded queries" name="dostuff"></p>
</form>
 
<?php
 
//this tells the system that it's no longer just parsing  
//html; it's now parsing PHP
 
$success = True; //keep track of errors so it redirects the page only if there are no errors
$db_conn = OCILogon("ora_p4s7", "a57854101", "ug");
 
function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
    //echo "<br>running ".$cmdstr."<br>";
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work
    echo "the value of the parsed string is:";
    var_dump($statment);
 
    if (!$statement) {
       echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
       $e = OCI_Error($db_conn); // For OCIParse errors pass the      
       // connection handle
       echo htmlentities($e['message']);
       $success = False;
    }
 
    $r = OCIExecute($statement, OCI_DEFAULT);
    if (!$r) {
       echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
       $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
       echo htmlentities($e['message']);
       $success = False;
    } else {
 
    }
    return $statement;
 
}
 
function executeBoundSQL($cmdstr, $list) {
    /* Sometimes a same statement will be excuted for severl times, only
     the value of variables need to be changed.
     In this case you don't need to create the statement several times;  
     using bind variables can make the statement be shared and just  
     parsed once. This is also very useful in protecting against SQL injection. See example code below for     how this functions is used */
 
    global $db_conn, $success;
    $statement = OCIParse($db_conn, $cmdstr);
 
    if (!$statement) {
       echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
       $e = OCI_Error($db_conn);
       echo htmlentities($e['message']);
       $success = False;
    }
 
    foreach ($list as $tuple) {
       foreach ($tuple as $bind => $val) {
          //echo $val;
          //echo "<br>".$bind."<br>";
          OCIBindByName($statement, $bind, $val);
          unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
 
       }
       $r = OCIExecute($statement, OCI_DEFAULT);
       if (!$r) {
          echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
          $e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
          echo htmlentities($e['message']);
          echo "<br>";
          $success = False;
       }
    }
 
}
 
function printResult($result) { //prints results from a select statement
    echo "<br>Got data from table event:<br>";
    echo "<table>";
    echo "<tr><th>EventID</th><th>EventName</th></tr>";
    while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
       echo "<tr><td>" . $row[0] . "</td><td>" . $row[1] . "</td></tr>"; //or just use "echo $row[0]"
       echo "the value of row in the loop is:";
       var_dump($row);  
    }
    echo "the value of row after the loop is:";
    var_dump($row);
    echo "</table>";
 
}
 
// Connect Oracle...
if ($db_conn) {
 
    if (array_key_exists('reset', $_POST)) {
       // Drop old table...
       echo "<br> dropping table <br>";
       executePlainSQL("Drop table event cascade constraints");
 
       // Create new table...
       echo "<br> creating new table <br>";
       executePlainSQL("create table event (eid number(9,0), etitle varchar2(300), edescription varchar2(400), 
     startTime DATE, end DATE, street_address varchar2(100), building varchar2(100), userid number(9,0),
     primary key (eid),
     foreign key (userid) references users(userid))");
       OCICommit($db_conn);
 
    } else
       if (array_key_exists('insertsubmit', $_POST)) {
          //Getting the values from user and insert data into the table
          $tuple = array (
             ":bind1" => $_POST['eid'],
             ":bind2" => $_POST['etitle'],
             ":bind3" => $_POST['edescription'],
             ":bind4" => $_POST['startTime)'],
             ":bind5" => $_POST['end'],
             ":bind6" => $_POST['street_address'],
             ":bind7" => $_POST['building'],
             ":bind8" => $_POST['userid'],
          );
          $alltuples = array (
             $tuple
          );
          executeBoundSQL("insert into event values (:bind1, :bind2, :bind3, :bind4, :bind5, :bind6, :bind7, :bind8)", $alltuples);
          OCICommit($db_conn);
 
       } else
          if (array_key_exists('updatesubmit', $_POST)) {
             // Update tuple using data from user
             $tuple = array (
                ":bind1" => $_POST['oldName'],
                ":bind2" => $_POST['newName']
             );
             $alltuples = array (
                $tuple
             );
             executeBoundSQL("update event set etitle=:bind2 where etitle=:bind1", $alltuples);
             OCICommit($db_conn);
 
          } else
             if (array_key_exists('dostuff', $_POST)) {
                // Insert data into table...
              executePlainSQL("insert into event values (000000001, 'Computation And Sustainability: Beyond Green IT - FLS Talk By Alan Mackworth, UBC/CS', 
                'Alan Mackworth, Professor and Canada Research Chair in AI, UBC Computer Science', 
                TO_DATE('30-AUG-13 3:30PM', 'DD-MON-YY HH:MIPM'), 
                TO_DATE('30-AUG-13 6:30PM','DD-MON-YY HH:MIPM'), 
                '110-6245 Agronomy Rd.', 
                'Hugh Dempster Pavilion', 000000001)");
              executePlainSQL("insert into event values (000000002, 'UBC FilmSoc Wicker Man Beer Garden', 
                'Wonder why weve been counting down to Cagemas? Well, for our beer garden of course.', 
                TO_DATE('13-FEB-13 12:00AM','DD-MON-YY HH:MIAM'), 
                TO_DATE('13-FEB-13 1:30PM','DD-MON-YY HH:MIPM'), 
                '130-6138 Student Union Blvd', 
                'Student Union Building', 000000003)");
              executePlainSQL("insert into event values (000000003, 'Career Fair 2014', 'Join us for the chance to interface with your future masters!', 
                TO_DATE('24-SEP-13 1:30PM','DD-MON-YY HH:MIPM'),
                TO_DATE('24-SEP-13 5:30PM','DD-MON-YY HH:MIPM'), 
                '1137 Alumni Ave',
                'Life Sciences Building', 000000001)");
              executePlainSQL("insert into event values (000000004, 'COGS Meet the Profs!', 'Join us for a chance to try to network with your fellow students and professors!', 
                TO_DATE('04-OCT-13 10:30AM','DD-MON-YY HH:MIAM'), 
                TO_DATE('04-OCT-13 11:30AM','DD-MON-YY HH:MIAM'), 
                '6371 Crescent Rd', 
                'Leo and Thea Koerner Graduate Student Centre', 000000002)");
              executePlainSQL("insert into event values (000000005, 'CPSC 304 group meeting', 'Doug, Dane, Greg, and Gaia are meeting to work on project', 
                TO_DATE('02-NOV-13 6:30PM','DD-MON-YY HH:MIPM'), 
                TO_DATE('02-NOV-13 8:30PM','DD-MON-YY HH:MIPM'), 
                '2366 Main Mall', 
                'ICICS', 000000004)");
                // Inserting data into table using bound variables
                /*$list1 = array (
                   ":bind1" => 6,
                   ":bind2" => "All"
                );
                $list2 = array (
                   ":bind1" => 7,
                   ":bind2" => "John"
                );
                $allrows = array (
                   $list1,
                   $list2
                );
                executeBoundSQL("insert into event values (:bind1, :bind2)", $allrows); //the function takes a list of lists
                // Update data...
                //executePlainSQL("update tab1 set nid=10 where nid=2");
                // Delete data...
                //executePlainSQL("delete from tab1 where nid=1");*/
                OCICommit($db_conn);
             }
 
    if ($_POST && $success) {
       //POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
       header("location: EventsHardCode.php");
    } else {
       // Select data...
       $result = executePlainSQL("select * from event");
       printResult($result);
       echo "did we do it?";
    }
 
    //Commit to save changes...
    OCILogoff($db_conn);
} else {
    echo "cannot connect";
    $e = OCI_Error(); // For OCILogon errors pass no handle
    echo htmlentities($e['message']);
}
