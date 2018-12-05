<!-- br_model.php -->
<?php
// The data model for the BRsquared Project

function getData($a) {
    $ret = array("data"=>strlen($a));
    return $ret;
}


// returns a connection to the database and will echo the fail if there is one
function connectToDataBase() {
    $dbHost = "localhost";                  // localhost (since the code is running on ceclnx01)
    $dbUser = "cse383";                     // USER and PASSWORD should not be in this code but
    $dbPass = "HoABBHrBfXgVwMSz";           // assignment said to use specific files so here we are
    $dbName = "cse383";                     // name of the database to connect to

    $mysqli = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

    // check
    if (mysqli_connect_errno($mysqli)) {
        echo "<!-- ";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        echo " -->";
        die;
    }

    return $mysqli;
}
?>