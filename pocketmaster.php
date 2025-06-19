<?php
header('Content-Type: text/html; charset=utf-8');

date_default_timezone_set('Asia/Kolkata');
include 'function.php';
$data = array();
$empty = array();

if (isTheseParametersAvailable(array('appName', 'electionName', 'UserName', 'Designation', 'operation'))) {
    include 'connection.php';
    $UserName = $_POST["UserName"];
    $Designation = $_POST["Designation"];
    $operation = $_POST["operation"];

    if($operation == 'select'){
        if($Designation == "Manager" || $Designation == "SP" || $Designation == "Survey Supervisor" || 
            $Designation == "Sr Manager" || $Designation == "Admin and Other" || 
            $Designation == "CEO/Director" || $Designation == "Software Developer"){
            $query = "SELECT 
                  COALESCE(pm.PocketCd, 0) AS PocketCd, 
                  COALESCE(PocketName, '') AS PocketName, 
                  COALESCE(PocketNameMar, '') AS PocketNameMar, 
                  COALESCE(pm.WardCd, 0) AS WardCd, 
                  COALESCE(wm.WardNameOrNum, '') AS WardNameOrNum,
                  COALESCE(Address1, '') AS Address1, 
                  COALESCE(Address2, '') AS Address2, 
                  COALESCE(pm.Lattitude, '') AS Lattitude, 
                  COALESCE(pm.Longitude, '') AS Longitude, 
                  COALESCE(pm.KMLFile_Url, '') AS KMLFile_Url, 
                  COALESCE(CONVERT(VARCHAR, pm.AddedDate, 100), '') AS AddedDate, 
                  --COALESCE(um.ExecutiveName, '') AS ExecutiveName,
                  '' AS ExecutiveName,
                  (SELECT COUNT(*) FROM TreeCensus WHERE PocketCd = pm.PocketCd AND IsActive = 1 AND AddedBy = '$UserName') as Treecount
                  FROM PocketMaster AS pm 
                  INNER JOIN Survey_Entry_Data..User_Master AS um ON (pm.AddedBy = um.UserName) 
                  INNER JOIN WardMaster As wm ON (wm.WardCd = pm.WardCd)
                  WHERE pm.IsActive = 1 AND pm.IsCompleted = 0 AND SRExecutiveCd <> 0 AND SRExecutiveCd IS NOT NULL 
                  ORDER BY pm.AddedDate DESC;";
            $data["error"] = false;
            $data["message"] = "List !";
            $data["PocketList"] = getMultiQueryData($con, $query);
            //AND pm.AddedBy = '$UserName' line 33
        }else{
            $query = "SELECT TOP(1) 
                  COALESCE(pm.PocketCd, 0) AS PocketCd, 
                  COALESCE(PocketName, '') AS PocketName, 
                  COALESCE(PocketNameMar, '') AS PocketNameMar, 
                  COALESCE(pm.WardCd, 0) AS WardCd, 
                  COALESCE(wm.WardNameOrNum, '') AS WardNameOrNum, 
                  COALESCE(Address1, '') AS Address1, 
                  COALESCE(Address2, '') AS Address2, 
                  COALESCE(pm.Lattitude, '') AS Lattitude, 
                  COALESCE(pm.Longitude, '') AS Longitude, 
                  COALESCE(pm.KMLFile_Url, '') AS KMLFile_Url, 
                  COALESCE(CONVERT(VARCHAR, pm.AddedDate, 100), '') AS AddedDate, 
                  COALESCE(um.ExecutiveName, '') AS ExecutiveName,
                  (SELECT COUNT(*) FROM TreeCensus WHERE PocketCd = pm.PocketCd AND IsActive = 1) as Treecount
                  FROM PocketMaster AS pm 
                  --INNER JOIN Survey_Entry_Data..User_Master AS um ON (pm.AddedBy = um.UserName) 
                  INNER JOIN Survey_Entry_Data..Executive_Master AS um ON (pm.SRExecutiveCd = um.Executive_Cd) 
                  INNER JOIN WardMaster As wm ON (wm.WardCd = pm.WardCd)
                  WHERE pm.IsActive = 1 AND pm.IsCompleted = 0 AND SRExecutiveCd = $Executive_Cd
                  ORDER BY pm.AddedDate DESC;";
            $data["error"] = false;
            $data["message"] = "List !";
            $data["PocketList"] = getMultiQueryData($con, $query);
        } 
    }else if($operation == 'insert'){
        if(isTheseParametersAvailable(array('PocketName', 'PocketNameMar', 'WardCd', 'Address1', 'Address2', 'Lattitude', 
            'Longitude', 'UserName'))){

            $PocketName = $_POST["PocketName"];
            $PocketNameMar = "";
            $WardCd = $_POST["WardCd"];
            $Address1 = $_POST["Address1"];
            $Address2 = $_POST["Address2"];
            $Lattitude = $_POST["Lattitude"];
            $Longitude = $_POST["Longitude"];
            $UserName = $_POST["UserName"];

            if($_POST["PocketNameMar"] != ""){
                $PocketNameMar = "N'".$_POST["PocketNameMar"]."'";
            }else{
                $PocketNameMar = "NULL";
            }

            if(sizeof(getSingleQueryData($con, "SELECT * FROM PocketMaster WHERE PocketName = '$PocketName' AND IsActive = 1;")) > 0){
                $data["error"] = true;
                $data["message"] = "Pocket Name already present!\nPlease try using other pocket name.";
                $data["PocketList"] = $empty;
            } else {
                $query = "INSERT INTO PocketMaster (PocketName,PocketNameMar,WardCd,Address1,Address2,
                Lattitude,Longitude,IsActive,AddedBy,AddedDate) VALUES ('$PocketName', $PocketNameMar, $WardCd, N'$Address1', 
                N'$Address2', '$Lattitude', '$Longitude', 1, '$UserName', GETDATE())";

                if(insertUpdateRecord($con, $query)){
                    $data["error"] = false;
                    $data["message"] = "Record added successfully !";
                    $data["PocketList"] = $empty;
                }else{
                    $data["error"] = true;
                    $data["message"] = "Unable to add record !\nPlease try again.";
                    $data["PocketList"] = $empty;
                }
            }
        }else{
            $data["error"] = true;
            $data["message"] = "Required parameters are missing !";
            $data["PocketList"] = $empty;
        }
    }else if($operation == 'update'){
        if(isTheseParametersAvailable(array('PocketCd', 'PocketName', 'PocketNameMar', 'WardCd', 'Address1', 'Address2', 'UserName'))){

            $PocketCd = $_POST["PocketCd"];
            $PocketName = $_POST["PocketName"];
            $PocketNameMar = "";
            $WardCd = $_POST["WardCd"];
            $Address1 = $_POST["Address1"];
            $Address2 = $_POST["Address2"];
            $UserName = $_POST["UserName"];

            if($_POST["PocketNameMar"] != ""){
                $PocketNameMar = "N'".$_POST["PocketNameMar"]."'";
            }else{
                $PocketNameMar = "NULL";
            }

            if(sizeof(getSingleQueryData($con, "SELECT * FROM PocketMaster WHERE PocketName = '$PocketName' AND PocketCd <> $PocketCd AND IsActive = 1;")) > 0){
                $data["error"] = true;
                $data["message"] = "Pocket Name already present!\nPlease try using other pocket name.";
                $data["PocketList"] = $empty;
            } else {
                $query = "UPDATE PocketMaster SET PocketName = '$PocketName', PocketNameMar = $PocketNameMar, WardCd = $WardCd, 
                    Address1 = N'$Address1', Address2 = N'$Address2',
                    UpdatedBy = '$UserName', UpdatedDate = GETDATE() WHERE PocketCd = $PocketCd";

                if(insertUpdateRecord($con, $query)){
                    $data["error"] = false;
                    $data["message"] = "Record updated successfully !";
                    $data["PocketList"] = $empty;
                }else{
                    $data["error"] = true;
                    $data["message"] = "Unable to update record !\nPlease try again.";
                    $data["PocketList"] = $empty;
                }
            }
        }else{
            $data["error"] = true;
            $data["message"] = "Required parameters are missing !";
            $data["PocketList"] = $empty;
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
    }else if($operation == 'dropdown'){
        $query = "SELECT WardCd, WardNameOrNum, NodeName FROM WardMaster WHERE IsActive = 1;";
        $data["PocketMasterDropDownList"] = getMultiQueryData($con, $query);
    }

        
    } else {
        $data["error"] = true;
        $data["message"] = "Required parameters are missing !";
        $data["PocketList"] = $empty;
    }

sqlsrv_close($con); 
echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>