<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Proposals.php';
include_once '../../../users/models/User.php';

//Instantiate SB and Connect
$database = new Database();

if (!isset($_COOKIE["jwt"])) {
    header("Location: login");
    exit();
}


$db = $database->connect();
//Instantiate Project object
$user = new User($db);

//Check jwt validation
$user_details = $user->validate($_COOKIE["jwt"]);
if ($user_details === false) {
    header("Location: login");
    exit();
}

if ($user_details['can_be_super_user'] != 1 && $user_details['can_view_cash_reports'] != 1) {
    header("Location: login");
    exit();
}

function fetch_data($db)
{
    //Instantiate SB and Connect
    $database = new Database();
    $db = $database->connect();

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $country = clean_data($_POST['country']);
    $department = clean_data($_POST['department']);
    $statusCheck = clean_data($_POST['statusCheck']);
    $status = clean_data($_POST['status']);
    $created_by = clean_data($_POST['created_by']);
    $DateFrom = clean_data($_POST['DateFrom']);
    $DateTo = clean_data($_POST['DateTo']);


    //Filters Checking
    $where_qr = '';

    if (
        $country != ""
    ) {
        $where_qr .=  ' AND (b.country = :country)';
    }
    if ($department != "") {
        $where_qr .=  ' AND (b.department = :department)';
    }

    if (
        $status != ""
    ) {
        $where_qr .=  ' AND (b.status = :status)';
    }
    if (
        $created_by != ""
    ) {
        $where_qr .=  ' AND (b.created_by = :created_by)';
    }
    if ($statusCheck != "") {
        if ($statusCheck == "disbursed") {
            $where_qr .=  ' AND ( b.status IN ("completed", "cleared", "clearing", "clearDenied") ) ';
        } elseif ($statusCheck == "inprogress") {
            $where_qr .=  ' AND ( b.status IN ("approved","pending", "@FinanceFromHOD","@GMD", "@HOD","@COO","@CFO","@FINANCE", "suspended", "@returnedFromHOD", "returnedFromFinance", "returnedFromCOO", "returnedFromGMD", "@FinanceFromCOO", "@FinanceFromGMD", "@GMDfromCOO", "@GMDfromCOO") )';
        } elseif ($statusCheck == "declined") {
            $where_qr .=  ' AND ( b.status IN ("rejected", "cancelled") )';
        }
    }

    if (
        $DateFrom != "" && $DateTo != ""
    ) {
        if (($statusCheck != "") && ($statusCheck == "disbursed")
        ) {
            $where_qr .= ' AND( cast(b.financeReleaseDate as date) BETWEEN :dateFrom AND :dateTo )';
        } elseif (($statusCheck != "") || ($statusCheck == "inprogress")
        ) {
            $where_qr .= ' AND( cast(b.created_at as date) BETWEEN :dateFrom AND :dateTo )';
        } elseif (($statusCheck != "") || ($statusCheck == "declined")
        ) {
            $where_qr .= ' AND( cast(b.created_at as date) BETWEEN :dateFrom AND :dateTo )';
        }
    }

    $query = ' SELECT
            b.*,
        b.id AS pty_id,
        ctry.currency ,
        u.name AS req_by_name,
        u.surname AS req_by_surname,
        u1.name AS disb_by_name,
        u1.surname AS disb_by_surname,
        bc.name AS budget_line,
        u.name AS onbehalf_of,
        dept.category AS department
        FROM
            proposal b
        LEFT JOIN 
            users u ON b.created_by = u.userId
        LEFT JOIN 
            users u1 ON b.financeRelease = u1.userId
        LEFT JOIN 
            countries ctry ON b.country = ctry.id
        LEFT JOIN 
            department_categories dept ON b.department=dept.id 
        LEFT JOIN
            budget_categories bc ON b.budget_line = bc.id 
        WHERE 1
            ' . $where_qr . '
        ORDER BY b.id ASC
    ';

    //Prepare statement
    $stmt = $db->prepare($query);

    if ($country != "") $stmt->bindParam(':country', $country);
    if ($department != "") $stmt->bindParam(':department', $department);
    if ($status != "") $stmt->bindParam(':status', $status);
    if ($created_by != "") $stmt->bindParam(':created_by', $created_by);
    if ($DateFrom != "" && $DateTo != "") {
        $stmt->bindParam(':dateFrom', $DateFrom);
        $stmt->bindParam(
            ':dateTo',
            $DateTo
        );
    }

    //Execute Query
    $stmt->execute();
    $num = $stmt->rowCount();

    if ($num > 0) {
        $data_array = array();
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data_array[] = $data;
        }
        $output = '

        <h3 style="text-align:center; text-decoration:underline;">SERIS - PROROSALS REPORT</h3> <br/><br/>
            
    
            <table border="1" cellspacing="0" cellpadding="5">  
                <tr style="font-weight:bold;">  
                    <th>Ref. Number</th>  
                    <th>Department</th> 
                    <th>Amount</th> 
                    <th>Subject</th> 
                    <th>Status</th> 
                    <th>Proposes By</th>  
                    <th>On Behalf</th>  
                    <th>Request by</th>  
                    <th>Proposed Date</th>  
                </tr>

                ';

        $tot_grand_total = 0;
        $currency = '';

        foreach ($data_array as $row) {

            $currency = $row['currency'];
            $tot_grand_total = $tot_grand_total + $row["FTotal"];
            $releaseD = $row["financeReleaseDate"] ? $row["disb_by_surname"] . ' ' . $row["disb_by_name"] : 'N/A';
            $userRel = $row["financeReleaseDate"] ? date("F j, Y g:i a", strtotime($row["financeReleaseDate"])) : 'N/A';
            $budget_cat = $row["budget_line"] ? $row["budget_line"] : 'N/A';

            $output .=
                '                         
                        <tr nobr="true">  
                            <td>' . $row["refNo"] . '</td> 
                            <td>' . $row["department"] . '</td>
                            <td>' . number_format($row["FTotal"], 2) . ' ' . $row['currency'] . ' </td>  
                            <td>' . $row["subject"] . '</td> 						
                            <td>' . $row["status"] . '</td>  
                            <td>' . $row["req_by_name"] . ' ' . $row["req_by_surname"] . '</td> 
                            <td>' . $row["created_by"] . '</td> 
                            <td>' . $row["onbehalf_of"] . '</td> 
                            <td>' . date("F j, Y g:i a", strtotime($row['created_at'])) . '</td>
                        </tr>  
                    ';
        }

        $output .= '

                <tr>
                    <td colspan="11"></td>
                </tr>
                <tr style="background-color:#dff1ff;">
                    <td colspan="2"><h3>TOTAL</h3></td>
                    <td colspan="9"><b style="color:#c90000;">' . number_format($tot_grand_total, 2) . ' ' . $currency . '</b></td>
                </tr>
            </table>
        ';
        return $output;
    }
}
if (isset($_POST["exportExcel"])) {
    header("Content-type: application/octet-stream");
    header("Content-Disposition: attachment; filename=SERIS Proposal Report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    $content = fetch_data($db);
    echo $content;
}