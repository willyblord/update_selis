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

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    if (!isset($_COOKIE["jwt"])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please Login'
        ]);
        die();
    }
    $db = $database->connect();
    //Instantiate object
    $user = new User($db);
    $proposal = new Proposals($db);

    //Check jwt validation
    $user_details = $user->validate($_COOKIE["jwt"]);
    if ($user_details === false) {
        setcookie("jwt", null, -1, '/');
        setcookie("jwt_r", null, -1, '/');
        echo json_encode([
            'success' => false,
            'message' => $user->error
        ]);
        die();
    }

    if ($user_details['can_be_super_user'] != 1 && $user_details['can_view_cash_reports'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }
    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    // Get and sanitize input data
    $country = clean_data($_POST['country']);
    $department = clean_data($_POST['department']);
    $budget_line = clean_data($_POST['budget_line']);
    $statusCheck = clean_data($_POST['statusCheck']);
    $status = clean_data($_POST['status']);
    $created_by = clean_data($_POST['created_by']);
    $DateFrom = clean_data($_POST['DateFrom']);
    $DateTo = clean_data($_POST['DateTo']);

    // Set proposal properties
    $proposal->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $proposal->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $proposal->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $proposal->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $proposal->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$proposal->columnIndex]['data']) : "";
    $proposal->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir'])  : "";
    $proposal->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    // Fetch proposal report
    $output = $proposal->read_proposal_report($country, $department, $budget_line, $statusCheck, $status, $created_by, $DateFrom, $DateTo);
    echo json_encode($output);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}