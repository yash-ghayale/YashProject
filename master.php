<?php
include 'connection.php';
$data = array();
$ExistsData = array();
if (isset($_POST["operation"])) {
    $operation = $_POST["operation"];

    if($operation == 'list'){
        
        $query = "SELECT 
                    COALESCE(id, 0) AS id, 
                    COALESCE(name, '') AS name, 
                    COALESCE(email, '') AS email, 
                    COALESCE(password, '') AS password,
                    COALESCE(CONVERT(VARCHAR,dob,105), '') AS dob,
                    COALESCE(IsActive, 0) AS IsActive
                FROM MyInfo
                WHERE IsActive = 1
                ORDER BY name ASC;";

        $result = sqlsrv_query($con, $query);
        $srNo = 0;
        while ($row1 = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $TableData[] = $row1;
        }

        if(sizeof($TableData)>0){
            echo json_encode(['error' => false,'statusCode' => 200,'data' => $TableData]);
        }else{
            echo json_encode(['error' => true,'statusCode' => 204,'message' => 'Data not found !']);
        }
     
    }else if($operation == 'fetch'){
        if (isset($_POST["id"])) {
            $id = $_POST["id"];

            $query = "SELECT 
                        COALESCE(id, 0) AS id, 
                        COALESCE(name, '') AS name, 
                        COALESCE(email, '') AS email, 
                        COALESCE(password, '') AS password,
                        COALESCE(CONVERT(VARCHAR,dob,105), '') AS dob,
                        COALESCE(IsActive, 0) AS IsActive
                    FROM MyInfo
                    WHERE IsActive = 1 AND id = $id
                    ORDER BY name ASC;";

            $result = sqlsrv_query($con, $query);
            $srNo = 0;
            while ($row1 = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $TableData[] = $row1;
            }

            if(sizeof($TableData)>0){
                echo json_encode(['error' => false,'statusCode' => 200,'data' => $TableData]);
            }else{
                echo json_encode(['error' => true,'statusCode' => 204,'message' => 'Data not found !']);
            }
        } else {
            echo json_encode(['error' => true,'statusCode' => 400,'message' => 'ID parameter is missing !']);
        }
     
    }else if($operation == 'insert'){
        if (isset($_POST["name"]) &&
            isset($_POST["email"]) &&
            isset($_POST["password"]) &&
            isset($_POST["dob"])
        ) {

            $name = $_POST["name"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $dob = $_POST["dob"];

            $queryifexists = "SELECT name FROM MyInfo WHERE IsActive = 1 AND name = '$name';";

            $result = sqlsrv_query($con, $queryifexists);
            
            while ($row1 = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $ExistsData[] = $row1;
            }

            if(sizeof($ExistsData)>0){
                echo json_encode(['error' => false,'statusCode' => 204,'message' => 'Name already Exists !']);
            } else {
                $query = "INSERT INTO MyInfo (name,email,password,dob,IsActive) 
                        VALUES ('$name','$email','$password','$dob',1)";

                $InsertData = sqlsrv_query($con, $query);

                if($InsertData){
                    echo json_encode(['error' => false,'statusCode' => 200,'message' => 'Added Successfully !']);
                }else{
                    echo json_encode(['error' => true,'statusCode' => 204,'message' => 'Failed to Add !']);
                }
            }
        }else{
            echo json_encode(['error' => true,'statusCode' => 404,'message' => 'Required parameter Missing !']);
        }
    }else if($operation == 'update'){
        if (isset($_POST["id"]) &&
            isset($_POST["name"]) &&
            isset($_POST["password"])
        ) {
            $id = $_POST["id"];
            $name = $_POST["name"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $dob = $_POST["dob"];
        
            $query = "UPDATE MyInfo 
                    SET name = '$name',
                        email = '$email',
                        password = '$password',
                        dob = '$dob'
                    WHERE id = $id";

            $UpdateData = sqlsrv_query($con, $query);

            if($UpdateData){
                echo json_encode(['error' => false,'statusCode' => 200,'message' => 'Updated Successfully !']);
            }else{
                echo json_encode(['error' => true,'statusCode' => 204,'message' => 'Failed to update !']);
            }
        }else{
            echo json_encode(['error' => true,'statusCode' => 404,'message' => 'Required parameter Missing !']);
        }
    }else if($operation == 'delete'){
        if(isTheseParametersAvailable(array('PocketCd'))){
            $PocketCd = $_POST["PocketCd"];
            if(insertUpdateRecord($con, "UPDATE PocketMaster SET IsActive = 0, DeActiveDate = GETDATE() WHERE PocketCd = $PocketCd;")){
                $data["error"] = false;
                $data["message"] = "Record deleted successfully !";
                $data["PocketList"] = $empty;
            }else{
                $data["error"] = true;
                $data["message"] = "Unable to delete record !\nPlease try again.";
                $data["PocketList"] = $empty;
            }
        }else{
            $data["error"] = true;
            $data["message"] = "Pocket_Cd parameter is missing !";
            $data["PocketList"] = $empty;
        }
    }else {
        echo json_encode(['error' => true,'statusCode' => 404,'message' => 'Invalid Operation !']);
    }
}else{
    echo json_encode(['error' => true,'statusCode' => 404,'message' => 'Opeartion not found !']);
}
?>