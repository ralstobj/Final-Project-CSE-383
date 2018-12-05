<!-- rest.php -->
<?php
// The REST API for the BRsquared project

require_once("br_model.php");                           // load in the data model for the project

$mysqli = connectToDataBase();                          // creat connection to database

//get request method into $path variable
$method = strtolower($_SERVER['REQUEST_METHOD']);
if (isset($_SERVER['PATH_INFO']))
    $path  = $_SERVER['PATH_INFO'];
else $path = "";

//path comes in as /a/b/c - split it apart and make sure it passes basic checks
$pathParts = explode("/",$path);
if (count($pathParts) <2) {
  $ret = array('status'=>'FAIL','msg'=>'Invalid URL');
  retJson($ret);
}
if ($pathParts[1] !== "v1") {
  $ret = array('status'=>'FAIL','msg'=>'Invalid url or version');
  retJson($ret);
}

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

//look for url /v1/user
if ($method==="post" && count($pathParts) ==  3 && $pathParts[2] === "user") {
    /*
    ■	Given user and password will get a token validating the user. If no user is present or the password does not match will return status will == "FAIL"
    ■	passwords are hashed using the php password_hash function
    ■	url: rest.php/v1/user
    ■	method: post
    ■	json_in:
        ●	user
        ●	password
    ■	json_out
        ●	status: "OK" or "FAIL"
        ●	msg:
        ●	token: string
    ■	Test:
        ●	curl -X 'POST' -d '{"user":"test","password":"test"}' https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/user
    */
    /*
    $status = "OK";                                         // test for status of data pull
    $data = array();                                        // will hold the returned results from teh sql query
    $theSQLstring = "SELECT keyName from KeyValue";         // the SQL query for the info we want
    $res = mysqli_query($mysqli, $theSQLstring);            // run and hold the results of the sql query

    if (!$res) {
        // there was an error with the database query
        $status = "FAIL";
    } else {
        // Loop around the results row by row and add the data to the $data array
        while($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
    }

    $ret = array('status'=>$status,'data' => $data);        // build the array we want to convert to JSON
    retJson($ret);                                          // convert the the return array to JSON
    */
}

/*
    ■	Return the set of items we are tracking and their key
    ■	rest.php/v1/items
    ■	method: get
    ■	json_in: none
    ■	json_out:
        ●	status
        ●	msg
        ●	items[]
            ○	pk
            ○	item
    ■	test:
        ●	https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/items
        ●	curl https://ceclnx01.cec.miamioh.edu/~campbest/cse383/finalProject/restFinal.php/v1/items

*/


?>