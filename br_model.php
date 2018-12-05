<!-- br_model.php -->
<?php
// The data model for the BRsquared Project

$mysqli = connectToDataBase();                          // create connection to database

function getData($a) {
    $ret = array("data"=>strlen($a));
    return $ret;
}

/*
Database:
    ●	users: user table
        ○	pk
        ○	user
        ○	password -> hashed using the php password_hash function
        ○	timestamp
    ●	diary: Item Entries
        ○	pk
        ○	userFK -> foreign Key to user - not the user but the pk of the user
        ○	itemFK -> foreign Key to item. Not the item but the PK of the item
        ○	timestamp
    ●	diaryItems: list of items
        ○	pk: int
        ○	item: tinytext
    ●	tokens
        ○	pk
        ○	user - actual user string
        ○	token - token string created randomly
        ○	timestamp
*/


// check if the provided user and password are correct
function isUserAuth($user, $pass) {
    // the SQL query for the info we want
    $pass = password_hash($pass);
    $isAuth = FALSE;

    $theSQLstring = "SELECT pk
                     from users
                     where user='". $user ."' AND password='". $pass ."'";

    $res = mysqli_query($mysqli, $theSQLstring);            // run and hold the results of the sql query

    if (!$res) {
        // there was an error with the database query
        $isAuth = FALSE;
    } else {
        $isAuth = TRUE;
    }

    return $isAuth;
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