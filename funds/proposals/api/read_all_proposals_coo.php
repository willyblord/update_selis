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
    //Instantiate Project object
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
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_hod'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }

    //Get raw posted data
    //$data = json_decode(file_get_contents("php://input"));

    $proposal->this_user = $user_details['userId'];

    $proposal->country = $user_details['country'];
    $proposal->department = $user_details['department_val'];

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $proposal->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $proposal->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $proposal->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $proposal->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $proposal->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$proposal->columnIndex]['data']) : "";
    $proposal->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $proposal->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $proposal->read_all_proposals_coo();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}