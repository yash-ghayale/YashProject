<?php
$serverName = 'ORNET83';
$connectionString = array("Database"=> 'PersonalInfo', "CharacterSet" => "UTF-8",   
                    "Uid"=> 'sa', "PWD"=> 'manager');

$con = sqlsrv_connect($serverName, $connectionString); 
// if($con){
//     echo 'Connection successful';
// } else {
//     echo 'Connection failed';
// }

?>