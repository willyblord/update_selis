<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/SystemsDowntime.php';
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
    $downtime = new SystemsDowntime($db);

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
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_coo'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }


    //Get raw posted data
    //$data = json_decode(file_get_contents("php://input"));

    $downtime->this_user = $user_details['userId'];


    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $country = isset($_POST['country']) && $_POST['country'] != '' ? clean_data($_POST['country']) : NULL;
    $system = isset($_POST['system']) && $_POST['system'] != '' ? clean_data($_POST['system']) : NULL;
    $DateFrom = isset($_POST['DateFrom']) && $_POST['DateFrom'] != '' ? clean_data($_POST['DateFrom']) : NULL;
    $DateTo = isset($_POST['DateTo']) && $_POST['DateTo'] != '' ? clean_data($_POST['DateTo']) : NULL;

    $downtime->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $downtime->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $downtime->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $downtime->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $downtime->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$downtime->columnIndex]['data']) : "";
    $downtime->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $downtime->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $downtime->read_all_downtimes($country, $system, $DateFrom, $DateTo);

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}