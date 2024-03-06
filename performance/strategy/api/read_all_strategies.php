<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Strategy.php';
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
    $strategy = new Strategy($db);

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
    
    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
    $requiredPermissions = ['view_group_strategy'];
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

    $strategy->this_user = $userDetails['userId'];

    $strategy->country = $userDetails['country'];

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $strategy->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $strategy->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $strategy->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $strategy->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $strategy->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$strategy->columnIndex]['data']) : "";
    $strategy->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $strategy->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $strategy->read_all_strategies();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}