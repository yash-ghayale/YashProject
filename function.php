<?php 
function isTheseParametersAvailable($required_fields)
    {
        $error = false;
        $error_fields = "";
        $request_params = $_REQUEST;
        //print_r($request_params);
     
        foreach ($required_fields as $field) {
            if (!isset($request_params[$field]) || strlen($request_params[$field]) <= 0) {
                $error = true;
                $error_fields .= $field . ', ';
               
            }
        }
     
        if ($error) {
            $response = array();
            $response["error"] = true;
            $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
            //echo json_encode($response);
            return false;
        }

        return $request_params[$field];
    }

function removeEscapeChar($str){
    return str_replace("'","''",$str);
}


function authenticateUser($conn, $mobile, $appName)
    {
        $sql1 = "SELECT Mobile, UserName, ExpDate, AppName FROM Survey_Entry_Data..User_Master 
        where Mobile = '$mobile' AND AppName = '$appName';";
        $result1 = sqlsrv_query($conn, $sql1);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            if (!checkUserDeActiveStatus($conn, $mobile, $appName)) {
                if (!checkUserLicenseStatus($conn, $mobile, $appName)) {
                    $result = USER_LOGIN_SUCCESS;
                } else {
                    $result = USER_LICENSE_EXPIRED;
                }
            } else {
                $result = USER_STATUS_NOT_ACTIVE;
            }
        }else{
            $result = USER_LOGIN_FAILED;
        }

        return $result;
    }

    function isUserBlocked($conn, $Executive_Cd, $appName){
        $result = false;

        $tsql = "SELECT ExecutiveName, Designation FROM Survey_Entry_Data..Executive_Master 
        WHERE Executive_Cd = $Executive_Cd AND IsBlocked = 1;";
        $result1 = sqlsrv_query($conn, $tsql);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    function isUserDeActive($conn, $UserName, $appName){
        $result = false;

        $tsql = "SELECT Mobile, UserName, ClientName, ExpDate, AppName FROM Survey_Entry_Data..User_Master 
        WHERE UserName = '$UserName' AND AppName = '$appName' AND DeactiveFlag = 'D';";
        $result1 = sqlsrv_query($conn, $tsql);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    function isUserValid($conn, $Executive_Cd, $appName){
        $result = false;

        $tsql = "SELECT ExecutiveName, Designation FROM Survey_Entry_Data..Executive_Master 
        WHERE Executive_Cd = $Executive_Cd AND EmpStatus = 'NA';";
        $result1 = sqlsrv_query($conn, $tsql);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    function isUserLicenseStatusValid($conn, $UserName, $appName){
        $today = date("Y-m-d");
        $result = false;
        $tsql = "SELECT Mobile, UserName, ClientName, ExpDate, AppName FROM Survey_Entry_Data..User_Master 
        WHERE UserName = '$UserName' AND AppName = '$appName' AND CONVERT(date, ExpDate, 103) < '$today';";

        $result1 = sqlsrv_query($conn, $tsql);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

	function checkUserDeActiveStatus($conn, $mobile, $appName){
        $result = false;

        $tsql = "SELECT Mobile, UserName, ClientName, ExpDate, AppName FROM Survey_Entry_Data..User_Master 
        WHERE Mobile = '$mobile' AND AppName = '$appName' AND DeactiveFlag = 'D'";
        $result1 = sqlsrv_query($conn, $tsql);
        //$numrows1 = sqlsrv_has_rows($result1);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    function checkUserLicenseStatus($conn, $mobile, $appName){
        $today = date("Y-m-d");
        $result = false;
        $tsql = "SELECT Mobile, UserName, ClientName, ExpDate, AppName FROM Survey_Entry_Data..User_Master 
        WHERE Mobile = '$mobile' AND AppName = '$appName' 
          AND CONVERT(date, ExpDate, 103) <  '$today';";

        $result1 = sqlsrv_query($conn, $tsql);
        //$numrows1 = sqlsrv_has_rows($result1);

        $numrows = sqlsrv_has_rows($result1);
        if ($numrows != 0) {
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    function insertUpdateRecord($conn, $query)
    {
        $data = false;
        if(sqlsrv_query($conn,$query) !== false){
            $data = true;
        } else {
            $data = false;
        }
        return $data;
    }

    function runquery($conn, $query)
    {
        $data = false;
        if(sqlsrv_query($conn,$query) !== false){
            $data = true;
        } else {
            $data = false;
        }
        return $data;
    }

    function getSingleQueryData($conn, $query)
    {
        $data = array();
		$tsql = '{CALL Sp_0001_PHP_Execute_Query(?)}';
        $params = array($query);
        $data = getDataInRowWithConnAndQueryAndParamsForSingleRecord($conn, $tsql, $params);
        return $data;
    }

    function getMultiQueryData($conn, $query)
    {
        $data = array();
		$tsql = '{CALL Sp_0001_PHP_Execute_Query(?)}';
        $params = array($query);
        $data = getDataInRowWithConnAndQueryAndParams($conn, $tsql, $params);
        return $data;
    }

    function getLoggedInUserDetails1($conn, $mobile, $appName, $electionName)
    {
        $data = array();
        $query = "SELECT COALESCE(em.Executive_Cd, 0) AS Executive_Cd, 
        COALESCE(em.ExecutiveName, '') AS ExecutiveName, 
        COALESCE(em.Designation, '') AS Designation, 
        COALESCE(em.Gender, '') AS Gender, 
        COALESCE(em.Age, 0) AS Age, 
        COALESCE(um.User_Id, 0) AS User_Id, 
        COALESCE(um.AppName, '') AS AppName, 
        COALESCE(um.UserName, '') AS UserName, 
        COALESCE(em.ElectionName, '') AS ElectionName, 
        COALESCE(tm.LogoImage, '') AS LogoImage, 
        COALESCE(um.Mobile, '') AS Mobile 
                FROM Survey_Entry_Data..Executive_Master AS em 
                INNER JOIN Survey_Entry_Data..User_Master AS um ON (em.Executive_Cd = um.Executive_Cd)
                INNER JOIN Survey_Entry_Data..TreeCensusCorporationMaster AS tm ON (em.ElectionName = tm.ElectionName)    
                WHERE Mobile = '$mobile' AND AppName = '$appName';";

            $tsql = '{CALL Sp_0001_PHP_Execute_Query(?)}';
               
            // echo $query;
            $params = array($query);
            $data = getDataInRowWithConnAndQueryAndParamsForSingleRecord($conn, $tsql, $params);        

        return $data;
    }

    function getLoggedInUserDetails($conn, $mobile, $appName, $electionName)
    {
        $data = array();
        $query = "SELECT COALESCE(em.Executive_Cd, 0) AS Executive_Cd, 
        COALESCE(em.ExecutiveName, '') AS ExecutiveName, 
        COALESCE(em.Designation, '') AS Designation, 
        COALESCE(em.Gender, '') AS Gender, 
        COALESCE(em.Age, 0) AS Age, 
        COALESCE(um.User_Id, 0) AS User_Id, 
        COALESCE(um.AppName, '') AS AppName, 
        COALESCE(um.UserName, '') AS UserName, 
        COALESCE(um.Mobile, '') AS Mobile 
				FROM Survey_Entry_Data..Executive_Master AS em 
				INNER JOIN Survey_Entry_Data..User_Master AS um ON (em.Executive_Cd = um.Executive_Cd)  
                WHERE Mobile = '$mobile' AND AppName = '$appName';";

            $tsql = '{CALL Sp_0001_PHP_Execute_Query(?)}';
               
            // echo $query;
            $params = array($query);
            $data = getDataInRowWithConnAndQueryAndParamsForSingleRecord($conn, $tsql, $params);        

        return $data;
    }

    function SendOTPToLoggedInUser($conn, $mobile, $appName, $appKey, $electionName)
    {
        $data = array();
        $query = "SELECT COALESCE(em.Executive_Cd, 0) AS Executive_Cd, 
        COALESCE(em.ExecutiveName, '') AS ExecutiveName, 
        COALESCE(em.Designation, '') AS Designation, 
        COALESCE(em.Gender, '') AS Gender, 
        COALESCE(em.Age, 0) AS Age, 
        COALESCE(um.User_Id, 0) AS User_Id, 
        COALESCE(um.AppName, '') AS AppName, 
        COALESCE(um.UserName, '') AS UserName, 
        COALESCE(um.Mobile, '') AS Mobile 
                FROM Survey_Entry_Data..Executive_Master AS em 
                INNER JOIN Survey_Entry_Data..User_Master AS um ON (em.Executive_Cd = um.Executive_Cd)  
                WHERE Mobile = '$mobile' AND AppName = '$appName';";

            $tsql = '{CALL Sp_0001_PHP_Execute_Query(?)}';
               
            // echo $query;
            $params = array($query);
            $data = getDataInRowWithConnAndQueryAndParamsForSingleRecord($conn, $tsql, $params);

            if (sizeof($data) > 0) {
                $otp = rand(1000, 9999);
                $User_Id = $data["User_Id"];
                $sendOTP = sendOTP($conn, $mobile, $otp, $User_Id, $appKey, $appName, $electionName);
            }
        

        return $data;
    }

    function sendOTP($conn, $mobileNo, $otp, $User_Id, $appKey, $appName, $electionName){
        $data = array();
        if(sqlsrv_query($conn,"UPDATE Survey_Entry_Data..User_Master set OTP = '$otp' where Mobile = '$mobileNo' AND AppName = '$appName';") !== false){
            $data = requestMobileOTPForVerification1($conn, $mobileNo, $otp, $appKey);
        }
        return $data;
    }

    function requestMobileOTPForVerification($conn, $mobileNo, $otp, $appKey){
        $data = array();

        $msg = '<#> Your OTP is: '.$otp.' '.$appKey.',Chanakya';
        $message = urlencode($msg);

        $url = 'http://173.45.76.227/send.aspx?username=ornettech&pass=Orc2829tech&route=trans1&templateid=1707161579329093013&senderid=CHANKR&numbers='.$mobileNo.'&message='.$message;
        $response1 = file_get_contents($url);
        $ary = explode("|",$response1);
        if($ary[0] == 1){
            $url2 = 'http://173.45.76.227/status.aspx?username=ornettech&pass=Orc2829tech&msgid='.$ary[2];
            $response2 = file_get_contents($url2);
            $ary2 = explode("|",$response2);
            if($ary2[0] == 1){
                $data['error'] = false;
                $data['message'] = 'Message Sent Succesfully';
                $data['otp'] = $otp;
            }else{
                $data['error'] = false;
                $data['message'] = 'Message Sent Succesfully';
                $data['otp'] = $otp;
            }
        }else{
            $data['error'] = true;
            $data['message'] = 'Message not sent!';
            $data['otp'] = $otp;
        }

        return $data;
    }


    function requestMobileOTPForVerification1($conn, $mobileNo, $otp, $appKey){
        $data = array();

        $msg = 'Your OTP is: '.$otp.' '.$appKey.',ORNET';
        $message = urlencode($msg);

        $url = 'http://45.114.141.83/api/mt/SendSMS?username=ornettech&password=ornet@3214&senderid=ORNETT&type=0&destination='.$mobileNo.'&peid=1701161892254896671&text='.$message;
        $response = file_get_contents($url);
        $obj = json_decode($response);
        if($obj->ErrorMessage == 'Done'){
                $JobId = $obj->JobId;
                $url1 = 'http://45.114.141.83/api/mt/GetDelivery?user=ornettech&password=ornet@3214&jobid='.$JobId;
                sleep(5);
                $response1 = file_get_contents($url1);
                $obj1 = json_decode($response1);
                if($obj1->DeliveryReports[0]->DeliveryStatus == 'Sent' || $obj1->DeliveryReports[0]->DeliveryStatus == 'Delivered'){
                    $data['error'] = false;
                    $data['message'] = 'Message Sent Succesfully';
                    $data['otp'] = $otp;
                }else{
                    $data['error'] = true;
                    $data['message'] = 'Message not sent!';
                    $data['otp'] = $otp;
                }
        }else{
            $data['error'] = true;
            $data['message'] = 'Message not sent!';
            $data['otp'] = $otp;
        }

        return $data;
    }


    function getDataInRowWithConnAndQueryAndParamsForSingleRecord($conn, $query, $params){
        $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
        $getDetail = sqlsrv_query($conn, $query, $params); 

        if ($getDetail == FALSE)  {  
            echo "Error in executing statement 3.\n";  
            die(print_r(sqlsrv_errors(), true));  
            }  else{

        $row_count = sqlsrv_num_rows( $getDetail ); 

        $data = array();

            while($row = sqlsrv_fetch_array($getDetail, SQLSRV_FETCH_ASSOC)){
                    $data = $row;
                } 
        }
            sqlsrv_free_stmt($getDetail);  

        return $data;
    }

    function getDataInRowWithConnAndQueryAndParams($conn, $query, $params){
        $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
        $getDetail = sqlsrv_query($conn, $query, $params); 

        if ($getDetail == FALSE)  {   
            die( print_r( sqlsrv_errors(), true));  
            }  else{

        $row_count = sqlsrv_num_rows( $getDetail ); 

        $data = array();

            while($row = sqlsrv_fetch_array($getDetail, SQLSRV_FETCH_ASSOC)){
                    $data[] = $row;
                } 
        }
            sqlsrv_free_stmt($getDetail); 

        return $data;
    }

    function checkOTP($conn, $otp, $mobile, $appName){
        $result = false;
        $query = "SELECT TOP(1) COALESCE(OTP, '0000') AS OTP FROM Survey_Entry_Data..User_Master 
            WHERE Mobile = '$mobile' AND AppName = '$appName';";
        $result1 = sqlsrv_query($conn, $query);
        $numrows = sqlsrv_has_rows($result1);
                if ($numrows > 0) {
                    while ($row =sqlsrv_fetch_array($result1)) {
                        $OTP1 = $row['OTP'];
                        if($OTP1 == $otp){
                            sqlsrv_query($conn, "UPDATE User_Master SET OTP = NULL 
                            WHERE Mobile = '$mobile' AND AppName = '$appName' AND OTP = '$otp';");
                            $result = true;
                        }else{
                            $result = false;
                        }
                    }
                }
        return $result;
    }
?>