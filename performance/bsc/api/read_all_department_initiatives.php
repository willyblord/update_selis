<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/BSCInitiatives.php';
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
    $bscInitiatives = new BSCInitiatives($db);

    //Check jwt validation
    $userDetails = $user->validate($_COOKIE["jwt"]);
    if ($userDetails === false) {
        setcookie("jwt", null, -1, '/');
        setcookie("jwt_r", null, -1, '/');
        echo json_encode([
            'success' => false,
            'message' => $user->error
        ]);
        die(); 
    }

    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HOD_ROLE'];
    $requiredPermissions = ['view_bsc', 'approve_bsc'];
    $requiredModules = 'Performance';
    
    if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }  

    //Get raw posted data
    //$data = json_decode(file_get_contents("php://input"));

    $bscInitiatives->this_user = $userDetails['userId'];

    $mycountry = $userDetails['country_id'];
    $my_department = $userDetails["department_id"];
    $my_division_id = $userDetails["division_id"];
    $my_division_name = $userDetails["division"];

    $bscInitiatives->country = $mycountry;
    $bscInitiatives->department  = $my_department;
    $bscInitiatives->division  = $my_division_id;

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $bscInitiatives->individual_bsc_id = isset($_POST['stratId']) ? clean_data($_POST['stratId']) : "";

    $bscInitiatives->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $bscInitiatives->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $bscInitiatives->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $bscInitiatives->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $bscInitiatives->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$bscInitiatives->columnIndex]['data']) : "";
    $bscInitiatives->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $bscInitiatives->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $bscInitiatives->read_all_department_initiatives();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}