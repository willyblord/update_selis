<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/IndividualBsc.php';
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
    $individualBsc = new IndividualBsc($db);

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
    
    $mycountry = $userDetails['country_id'];
    $my_department = $userDetails["department_id"];
    $my_division_id = $userDetails["division_id"];
    $my_division_name = $userDetails["division"];
    $my_unit_id = $userDetails["unit_id"];
    $my_section_id = $userDetails["section_id"];

    $individualBsc->country = $mycountry;
    $individualBsc->department  = $my_department;
    $individualBsc->division  = $my_division_id;
    $individualBsc->unit  = $my_unit_id;
    $individualBsc->section  = $my_section_id;


    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'SECTION_LEADER_ROLE'];
    $requiredPermissions = ['view_bsc'];
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

    $individualBsc->this_user = $userDetails['userId'];

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $individualBsc->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $individualBsc->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $individualBsc->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $individualBsc->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $individualBsc->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$individualBsc->columnIndex]['data']) : "";
    $individualBsc->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $individualBsc->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $individualBsc->read_all_section_bsc();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}