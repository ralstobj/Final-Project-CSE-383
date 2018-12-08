<?php
// The data model for the BRsquared Project
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


/**
 * Checks if a username and password pair are stored in the Database
 * 
 * @param string $user the logon name for the user
 * @param string $pass the unhashed password for the provided username
 * @return boolean returns TRUE if the username is found AND the password is correct for that user
 */
function isUserAuth($user, $pass) {
    $mysqli = connectToDataBase();                                                          // create connection to database
    $isAuth = FALSE;

    // prepare and bind so we can check if the user is authorized
    $stmt = $mysqli->prepare("SELECT password FROM users WHERE user=?");                    // the SQL to pull the hashed password from the DB
    $stmt->bind_param("s", $user);                                                          // bind $user to the SQL statement
    $stmt->execute();                                                                       // execute the statement (run the query)
    $res = $stmt->get_result();                                                             // get the results of the query

    if ($res->num_rows === 0) {
        // user name not found so loggon is not authorized
        $isAuth = FALSE;
        error_log("-----> User NOT found <-----");
    } else {
        $row = mysqli_fetch_assoc($res);
        $isAuth = password_verify($pass, $row['password']);                                 // will be TRUE if the password is is correct
        error_log("-----> User Authorization: ". $isAuth ." <-----");
    }

    $stmt->close();                                                                         // close the statement
    mysqli_close($mysqli);                                                                  // close the DB connection
    return $isAuth;
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


/**
 * Returns an Array listing all the item types currently being tracked
 * 
 * @return array An associative array with each row having pk as an int and item as a string
 */
function getTrackedItems() {
    error_log("Build array of items from DB");

    $mysqli = connectToDataBase();                                  // create connection to database

    $data = array();                                                // will hold the returned results from the sql query
    $theSQLstring = "SELECT pk, item                                 
                     FROM diaryItems
                     ORDER BY item";
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
 * list an item as consumed by the user
 * 
 * @param string $token the token for the authorized user
 * @param int $itemKey the key for the item that was consumed
 * @return boolean true if the item was consumed false if not
 */
function consumeItem($token, $itemKey) {
    error_log("Item being consumed");
    $consumedStatus = FALSE;                            // will set to true if the item is consumed
    $userPK = tokenToPK($token);

    $mysqli = connectToDataBase();                      // create connection to database
    
    // prepare and bind
    $stmt = $mysqli->prepare("INSERT INTO diary (userFK, itemFK) VALUES (?, ?)");
    $stmt->bind_param("ii", $userPK, $itemKey);
    $stmt->execute();

    $stmt->close();                                         // close the statement
    mysqli_close($mysqli);                                  // close connection to database
    $consumedStatus = TRUE;

    return $consumedStatus;
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
    $data = array();                                                                        // will hold the returned results from the sql querry
    $rowCount = 0;                                                                          // count how many rows we returned

    $userPK = tokenToPK($token);                                                            // the Primary Key for the user

    $theSQLstring = "SELECT diary.pk, diaryItems.item, diary.timestamp
                    FROM diary
                    INNER JOIN diaryItems ON diary.itemFK=diaryItems.pk
                    WHERE diary.userFK=". $userPK ." ORDER BY diary.timestamp DESC";
    
    $res = mysqli_query($mysqli, $theSQLstring);                                                // run and hold the results of the sql query

    if ($res) {
        // Loop around the results row by row and add the data to the $data array
        while( ($row = mysqli_fetch_assoc($res)) && ($rowCount < $count) ) {
            $data[] = $row;
            $rowCount++;
        }
    }

    mysqli_close($mysqli);                                  // close connection to database
    return $data;
}


// return an array of items and how many times that item type has been consumed by the user with provided token
function getItemSummary($token) {
    error_log("Get summary of Items consumed");

    $mysqli = connectToDataBase();                                                          // create connection to database
    $data = array();                                                                        // will hold the returned results from the sql querry
    $userPK = tokenToPK($token);                                                            // the Primary Key for the user

    $theSQLstring = "SELECT * FROM diaryItems ORDER BY item";                               // sql query to hold all the item types being tracked
    $itemRes = mysqli_query($mysqli, $theSQLstring);

    if($itemRes) {
        $stmt = $mysqli->prepare("SELECT * FROM diary WHERE userFK=? AND itemFK=?");                    // the SQL every entry of an item type consumed by the user
        // for each item type check how many times the user has consumed it
        while( $row = mysqli_fetch_assoc($itemRes)) {
            // prepare and bind so we can check if the user is authorized
            $stmt->bind_param("ii", $userPK, $row['pk']);                                               // bind $user to the SQL statement
            $stmt->execute();                                                                           // execute the statement (run the query)
            $res = $stmt->get_result();                                                                 // get the results of the query
            
            $data[] = array('item'=>$row['item'], 'count'=>$res->num_rows);
        }
    }

    $stmt->close();
    mysqli_close($mysqli);
    return $data;
}


//--------------------------------------------------------------------------------
//                                HELPER FUNCTIONS
//--------------------------------------------------------------------------------

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

/**
 * Checks if a provided token is currently authorized
 * 
 * @param string $token the token to check for validity
 * @return boolean returns TRUE if the token is valid
 */
function isTokenValid($token) {
    //return TRUE;
    $mysqli = connectToDataBase();                                                          // create connection to database
    $isValid = FALSE;

    // prepare and bind so we can check if the user is authorized
    $stmt = $mysqli->prepare("SELECT user FROM tokens WHERE token=?");                      // the SQL to pull the hashed password from the DB
    $stmt->bind_param("s", $token);                                                         // bind $user to the SQL statement
    $stmt->execute();                                                                       // execute the statement (run the query)
    $res = $stmt->get_result();                                                             // get the results of the query

    if ($res->num_rows === 0) {
        // Token not found so token is not Valid
        $isValid = FALSE;
        error_log("-----> Token NOT found <-----");
    } else {
        $row = mysqli_fetch_assoc($res);
        $isValid = TRUE;
        error_log("-----> Token Valid for: ". $row['user'] ." <-----");
    }

    $stmt->close();                                                                         // close the statement
    mysqli_close($mysqli);                                                                  // close the DB connection
    return $isValid;
}


/**
 * Checks if a provided item key is a valid listing in the Database
 * 
 * @param int $itemKey the token to check for validity
 * @return boolean returns TRUE if the itemKey is valid
 */
function isItemKeyValid($itemKey) {
    //return TRUE;
    $mysqli = connectToDataBase();                                                          // create connection to database
    $isValid = FALSE;

    // prepare and bind so we can check if the user is authorized
    $stmt = $mysqli->prepare("SELECT item FROM diaryItems WHERE pk=?");                     // the SQL to pull the item with a provided key
    $stmt->bind_param("i", $itemKey);                                                       // bind $itemKey to the SQL statement
    $stmt->execute();                                                                       // execute the statement (run the query)
    $res = $stmt->get_result();                                                             // get the results of the query

    if ($res->num_rows === 0) {
        // itemKey not found so itemKey is not Valid
        $isValid = FALSE;
        error_log("-----> itemKey NOT found <-----");
    } else {
        $row = mysqli_fetch_assoc($res);
        $isValid = TRUE;
        error_log("-----> itemKey Valid for: ". $row['item'] ." <-----");
    }

    $stmt->close();                                                                         // close the statement
    mysqli_close($mysqli);                                                                  // close the DB connection
    return $isValid;
}

/**
 * will retun the PK for an authorized user based on the token provided
 * 
 * @param string $token token for the authorized user
 * @return int
 */
function tokenToPK($token) {
    $userPK;

    $mysqli = connectToDataBase();                                                          // create connection to database

    // prepare and bind so we can pull the user name based on the token
    $stmt = $mysqli->prepare("SELECT user FROM tokens WHERE token=?");                      // the SQL to get the user name from the tokens table
    $stmt->bind_param("s", $token);                                                         // bind the token to the SQL
    $stmt->execute();                                                                       // execute the statement
    $stmt->bind_result($userName);                                                          // bind the results
    $stmt->fetch();                                                                         // fetch the data
    $stmt->close();                                                                         // close the statement
    
    if (!$userName) {
        error_log("User not found for token: ". $token);
        return;
    }

    $theSQLstring = "SELECT pk FROM users WHERE user='". $userName ."'";                // the SQL query to pull the user's PK from the users table
    $res  = mysqli_query($mysqli, $theSQLstring);
    $row = mysqli_fetch_assoc($res);
    $userPK = $row['pk'];
    error_log("User found: ". $userPK);

    mysqli_close($mysqli);                                  // close connection to database

    error_log("----------> USER PK: ". $userPK ." <----------");
    return $userPK;
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


function getData($a) {
    $ret = array("data"=>strlen($a));
    return $ret;
}
?>