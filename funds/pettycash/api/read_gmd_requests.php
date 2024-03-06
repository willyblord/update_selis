<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Pettycash.php';
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
    $pettycash = new Pettycash($db);

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
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_manager'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }


    //Get raw posted data
    //$data = json_decode(file_get_contents("php://input"));

    $pettycash->this_user = $user_details['userId'];

    $pettycash->country = $user_details['country'];
    $pettycash->department = $user_details['department_val'];

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $pettycash->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $pettycash->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $pettycash->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $pettycash->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $pettycash->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$pettycash->columnIndex]['data']) : "";
    $pettycash->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $pettycash->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $pettycash->group_requests();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}