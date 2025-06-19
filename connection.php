<?php
define('USER_LOGIN_SUCCESS', 101);
define('USER_LOGIN_FAILED', 102);

define('USER_INSTALLATION_EXPIRED', 103);
define('USER_LICENSE_EXPIRED', 104);

define('UPDATE_SUCCESS', 105);
define('UPDATE_FAILURE', 106);

define('USER_STATUS_ACTIVE', 107);
define('USER_STATUS_NOT_ACTIVE', 108);

$DBName = '';
$ServerName = '';
$ServerPwd = '';
$ServerUser = '';
$usermaindb = '';
$serverName = "92.204.137.146"; 
$connectionString = array("Database"=> "Survey_Entry_Data", "CharacterSet" => "UTF-8",   
                    "Uid"=> "sa", "PWD"=>"146@2023SQL#ORNET05");

$conn = sqlsrv_connect($serverName, $connectionString); 

$appName = $_POST["appName"];
$electionName = $_POST["electionName"];


$treename = "LocalName";
if($electionName == 'INDORE'){
    $treename = "HindiName";
}else{
    $treename = "LocalName";
}


if($conn) {
    //$query = "SELECT DbName,ServerName,ServerPwd,ServerUser from User_Master where AppName='$appName';";
    $query = "SELECT ServerName, ServerId AS ServerUser, ServerPwd, DBName AS DbName 
    FROM TreeCensusCorporationMaster WHERE ElectionName = 'MOCK';";
    $result = sqlsrv_query($conn, $query);
    $numrows = sqlsrv_has_rows($result);
    if ($numrows != 0) {
        while ($row =sqlsrv_fetch_array($result)) {
            $usermaindb = $row['DbName'];
            $DBName = $row['DbName'];
            $ServerName = $row['ServerName'];
            $ServerPwd = $row['ServerPwd'];
            $ServerUser = $row['ServerUser'];
        }
    }
    sqlsrv_close($conn);
}

$serverName = $ServerName; 
$connectionString = array("Database"=> $DBName, "CharacterSet" => "UTF-8",   
                    "Uid"=> $ServerUser, "PWD"=> $ServerPwd);

$con = sqlsrv_connect($serverName, $connectionString); 

?>