<?php
// The REST API for the BRsquared project

require_once("br_model.php");                           // load in the data model for the project

//returns data as json
function retJson($data) {
    header('content-type: application/json');
    print json_encode($data);
    exit;
}

//get request method into $path variable
$method = strtolower($_SERVER['REQUEST_METHOD']);
if (isset($_SERVER['PATH_INFO']))
    $path  = $_SERVER['PATH_INFO'];
else $path = "";

//path comes in as /a/b/c - split it apart and make sure it passes basic checks
$pathParts = explode("/",$path);
if (count($pathParts) <2) {
    error_log( "Path: ". $path ." || Count: ". count($pathParts) );
    $ret = array('status'=>'FAIL','msg'=>'Invalid URL');
    retJson($ret);
}

/*
if ( $pathParts[1] !== "v1" || $pathParts[1] !== "items" ) {
    // If they are not using v1 or looking specificly for rest.php/items/token fail out
    $ret = array('status'=>'FAIL','msg'=>'Invalid url or version');
    retJson($ret);
}
*/

//get json data if any
$jsonData =array();
try {
  $rawData = file_get_contents("php://input");
  $jsonData = json_decode($rawData,true);
  if ($rawData !== "" && $jsonData==NULL) {
    $ret=array("status"=>"FAIL","msg"=>"invalid json");
    retJson($ret);
  }
} catch (Exception $e) {
};



// Get Token - rest.php/v1/user
if ($method==="post" && count($pathParts) == 3 && $pathParts[1] === "v1" && $pathParts[2] === "user") {
    error_log("User Loggon Request");
    /*
    Given user and password will get a token validating the user.
    If no user is present or the password does not match will return status == "FAIL"
    passwords are hashed using the php password_hash function

    json_in = user, password
    json_out = status: "OK" or "FAIL", msg: '', token: string
    Test:  curl -X 'POST' -d '{"user":"test","password":"test"}' https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/user
    */
   
    // make sure we have we have the correct JSON information we need to make the updated
    if ( !isset($jsonData['user']) || !isset($jsonData['password']) ) {
        $ret = array('status'=>'FAIL','msg'=>'json is invalid','token'=>'');
        retJson($ret);
    }

    // we were provided a user and password now we check if they are good
    if ( isUserAuth($jsonData['user'], $jsonData['password']) ) {
        // generate and return the token
        $ret = array('status'=>'OK','msg'=>'','token'=> genToken($jsonData['user']) );
        retJson($ret);
    }

    $ret = array('status'=>'FAIL', 'msg' =>'Username and Password not Found','token'=>'');
    retJson($ret);
}


// Get list of items - rest.php/v1/items
if ($method==="get" && count($pathParts) == 3 && $pathParts[1] === "v1" && $pathParts[2] === "items") {
    error_log("Items being tracked.");
    /*
    Return the set of items we are tracking and their key
    json_in: none
    json_out: status, msg, items[](pk, item)
    test: curl https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/items
    */
    $data = getTrackedItems();

    $ret = array('status'=>'OK', 'msg' =>'','items'=>$data);

    retJson($ret);
}


// Get Items User Consumed - rest.php/items/token
if ($method==="get" && count($pathParts) == 3 && $pathParts[1] === "items") {
    error_log("Items tracked by the user");
/*
    -	Call gets the tracked items for a given user
    -	limit to last 30 items
    -	JSON Response:
        -	status: OK or AUTH_FAIL or FAIL
        -	msg: text
        -	items[]
            -	pk
            -	item
            -	timestamp
    -	test https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/items/1db4342013a7c7793edd72c249893a6a095bca71
*/
    $data = getConsumedItems($pathParts[2], 30);                // get the last 30 items for the authorized user with the provided token

    $ret = array('status'=>'OK', 'msg' =>'','items'=>$data);

    retJson($ret);
}


// Get Summary of Items - rest.php/v1/itemsSummary/token
if ($method==="get" && count($pathParts) == 4 && $pathParts[1] === "v1" && $pathParts[2] === "itemsSummary") {
    error_log("Items Summary");
/*
    -	json_in: none
    -	json_out
        -	status
        -	msg
        -	items[]
            -	item
            -	count
    -	test
        -	https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/itemsSummary/1db4342013a7c7793edd72c249893a6a095bca71
*/
}


// Update Items Consumed - rest.php/v1/items
if ($method==="post" && count($pathParts) == 3 && $pathParts[1] === "v1" && $pathParts[2] === "items") {
    error_log("Item Consumed");
/*
    -	Updates item as being consumed
    -	JSON IN
        -	token: string token
        -	ItemFK: <key>
    -	JSON OUT
        -	status: OK or AUTH_FAIL or FAIL
        -	msg: text
    -	test
        -	curl -X 'POST' -d '{"token":"1db4342013a7c7793edd72c249893a6a095bca71","itemFK":2}' https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/items
*/

    // make sure we have we have the correct JSON information we need to make the updated
    if ( !isset($jsonData['token']) || !isset($jsonData['ItemFK']) ) {
        $ret = array('status'=>'FAIL','msg'=>'json is invalid');
        retJson($ret);
    }

    if ( consumeItem($jsonData['token'], $jsonData['ItemFK']) ) {
        $ret = array('status'=>'OK', 'msg'=>'Item Consumed');
        retJson($ret);
    }

    $ret = array('status'=>'AUTH_FAIL', 'msg'=>'User Not Authorized');
    retJson($ret);
}

// If we hit this point then they did not provide a valid call to the API
$ret = array('status'=>'FAIL','msg'=>'Invalid url or version');
retJson($ret);

?>