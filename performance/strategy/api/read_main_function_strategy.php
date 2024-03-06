<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/CountryStrategy.php';
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
    $countryStrategy = new CountryStrategy($db);

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

    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
    $requiredPermissions = ['view_business_plan_strategy'];
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

    $countryStrategy->this_user = $userDetails['userId'];

    $mycountry = $userDetails['country_id'];
    $my_department = $userDetails["department_id"];
    $my_division_id = $userDetails["division_id"];
    $my_division_name = $userDetails["division"];

    $countryStrategy->country = $mycountry;
    $countryStrategy->department  = $my_department;
    $countryStrategy->division  = $my_division_id;

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $countryStrategy->draw = isset($_POST['draw']) ? clean_data($_POST['draw']) : "";
    $countryStrategy->start = isset($_POST['start']) ? clean_data($_POST['start']) : "";
    $countryStrategy->rowperpage = isset($_POST['length']) ? clean_data($_POST['length']) : "";
    $countryStrategy->columnIndex = isset($_POST['order']) ? clean_data($_POST['order'][0]['column']) : "";
    $countryStrategy->columnName = isset($_POST['columns']) ? clean_data($_POST['columns'][$countryStrategy->columnIndex]['data']) : "";
    $countryStrategy->columnSortOrder = isset($_POST['order']) ? clean_data($_POST['order'][0]['dir']) : "";
    $countryStrategy->searchValue = isset($_POST['search']['value']) ? clean_data($_POST['search']['value']) : '';

    //Projects query
    $output = $countryStrategy->read_main_function_strategies();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}