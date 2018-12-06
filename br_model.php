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

    // the SQL query for the info we want
    $theSQLstring = "SELECT pk
                     FROM users
                     WHERE user='". $user ."' AND password='". $pass ."'";

    $res = mysqli_query($mysqli, $theSQLstring);            // run and hold the results of the sql query

    if (!$res) {
        // there was an error with the database query
        $isAuth = FALSE;
    } else {
        $isAuth = TRUE;
    }

    mysqli_close($mysqli);
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

// will generate a token for a valid user and add it to the tokens table
function genToken($user) {
    error_log("Create User Token.");
    $mysqli = connectToDataBase();                          // create connection to database
    $token = random_str(42);                                // generate a random user token to be stored in the data base

    // add the token to the table
    $theSQLstring = "INSERT INTO tokens (user, token)
                     VALUES ('". $user ."', '". $token ."')";

    mysqli_query($mysqli, $theSQLstring);                   // run the sql query
    mysqli_close($mysqli);                                  // close connection to database

    // then return the token as a string
    return $token;
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