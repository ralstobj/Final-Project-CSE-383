<?php
// The data model for the BRsquared Project

function getData($a) {
    $ret = array("data"=>strlen($a));
    return $ret;
}

/*
Database:
    -	users: user table
        -	pk
        -	user
        -	password -> hashed using the php password_hash function
        -	timestamp
    -	diary: Item Entries
        -	pk
        -	userFK -> foreign Key to user - not the user but the pk of the user
        -	itemFK -> foreign Key to item. Not the item but the PK of the item
        -	timestamp
    -	diaryItems: list of items
        -	pk: int
        -	item: tinytext
    -	tokens
        -	pk
        -	user - actual user string
        -	token - token string created randomly
        -	timestamp
*/


// check if the provided user and password are correct
function isUserAuth($user, $pass) {
    error_log("Is the User Authorized?");
    $mysqli = connectToDataBase();                          // create connection to database
    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $isAuth = FALSE;

    // prepare, bind, then run the sql SELECT in the next three lines
    $stmt = $mysqli->prepare("SELECT pk FROM users WHERE user=? AND password=?");
    $stmt->bind_param("ss", $user, $pass);
    $stmt->execute();

    $res = $stmt->get_result();                             // hold the results from the sql SELECT

    if (!$res) {
        // there was an error with the database query
        $isAuth = FALSE;
    } else {
        $isAuth = TRUE;
    }

    $stmt->close();                                         // close the statement
    mysqli_close($mysqli);                                  // close the DB connection
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
        error_log("Failed to connect to MySQL: ". mysqli_connect_error());
        error_log("Failed to connect to MySQL: ". mysqli_connect_error());
        die;
    }

    return $mysqli;
}

// will generate a token for a valid user and add it to the tokens table
function genToken($user) {
    error_log("Create User Token.");

    $mysqli = connectToDataBase();                          // create connection to database
    $token = random_str(42);                                // generate a random user token to be stored in the data base

    // prepare and bind
    $stmt = $mysqli->prepare("INSERT INTO tokens (user, token) VALUES (?, ?)");
    $stmt->bind_param("ss", $user, $token);
    $stmt->execute();

    $stmt->close();                                         // close the statement
    mysqli_close($mysqli);                                  // close connection to database

    // then return the token as a string
    return $token;
}


// will return an Array of all currently tracked itmes and their primary key
function getTrackedItems() {
    error_log("Build array of items from DB");

    $mysqli = connectToDataBase();                                  // create connection to database

    $data = array();                                                // will hold the returned results from the sql query
    $theSQLstring = "SELECT pk, item FROM diaryItems";              // the SQL query for the info we want
    $res = mysqli_query($mysqli, $theSQLstring);                    // run and hold the results of the sql query

    if (!$res) {
        // there was an error with the database query
        $data = "FAIL";
    } else {
        // Loop around the results row by row and add the data to the $data array
        while($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
    }

    mysqli_close($mysqli);                                          // close connection to database
    return $data;
}

/**
 * will return an Array of items consumed by the requested user
 *
 * @param string $token token for the authorized user
 * @param int $count maximum number of items to return
 * @return array
 */
function getConsumedItems($token, $count) {
    error_log("Get Consumed Items List");

    $mysqli = connectToDataBase();                                                          // create connection to database
    $data = array();                                                                        // will hold the returned results from teh sql querry

    // prepare and bind so we can pull the user name based on the token
    $stmt = $mysqli->prepare("SELECT user FROM tokens WHERE token = ?");                    // the SQL to get the user name from the tokens table
    $stmt->bind_param("s", $token);
    $userName = $stmt->execute();

    if (!$userName) {
        // there was an error with the database query
        $data = "FAIL";
        error_log("UserName not found in tokens: ". $userName);
    } else {
        $theSQLstring = "SELECT pk FROM users WHERE user='". $userName ."'";                        // the SQL query to pull the user's PK from the users table
        $userPK = mysqli_query($mysqli, $theSQLstring);

        $theSQLstring = "SELECT pk, item, timestamp FROM diary WHERE userFK='". $userPK ."'";       // String to get the items the user has consumed
        $res = mysqli_query($mysqli, $theSQLstring);                                                // run and hold the results of the sql query
        $rowCount = 0;

        // Loop around the results row by row and add the data to the $data array
        while( ($row = mysqli_fetch_assoc($res)) && ($rowCount < $count) ) {
            $data[] = $row;
            $rowCount++;
        }
    }

    $stmt->close();                                         // close the statement
    mysqli_close($mysqli);                                  // close connection to database
 
    // then return the token as a string
    return $data;
}


/**
 * Generate a random string, using random_int
 * requires PHP 7, random_int is a PHP core function
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters to select from
 * @return string
 */
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}
?>