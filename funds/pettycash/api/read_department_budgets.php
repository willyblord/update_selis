<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Budget.php';
include_once '../../../administration/users/models/User.php';


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
    //Instantiate Project object
    $user = new User($db);
    $budget = new Budget($db);

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
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 && $user_details['can_be_cash_coo'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }


    //Get raw posted data
    //$data = json_decode(file_get_contents("php://input"));

    $budget->this_user = $user_details['userId'];

    $budget->country = $user_details['country'];
    $budget->department = $user_details['department_val'];

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $budget->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $budget->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $budget->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $budget->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $budget->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$budget->columnIndex]['data']) : "";
    $budget->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $budget->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $budget->read_all_budgets_at_hod();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}