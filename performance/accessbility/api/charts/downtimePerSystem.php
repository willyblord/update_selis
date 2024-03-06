<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../../include/Database.php';
include_once '../../models/DowntimeChart.php';
include_once '../../../../administration/users/models/User.php';


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
    $chart = new DowntimeChart($db);

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
    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
    $requiredPermissions = [];
    $requiredModules = 'Performance';
    
    if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }


    //Get raw posted data
    $data = json_decode(file_get_contents("php://input"));

    $chart->this_user = $userDetails['userId'];    

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
         && (!empty($data->country) && !empty($data->system) && (!empty($data->DateFrom) && !empty($data->DateTo))) ) {

        $chart->filter_country = $data->country;
        $chart->filter_system = $data->system;
        $chart->filter_date_from = $data->DateFrom;
        $chart->filter_date_from = $data->DateFrom;
        $chart->filter_date_to = $data->DateTo;
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
            && (!empty($data->country) && !empty($data->system) && (empty($data->DateFrom) && empty($data->DateTo))) ) {
        $chart->filter_country = $data->country;
        $chart->filter_system = $data->system;
        $chart->filter_date_from = '';
        $chart->filter_date_to = '';
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
             && (!empty($data->country) && empty($data->system) && (!empty($data->DateFrom) && !empty($data->DateTo))) ) {
        $chart->filter_country = $data->country;
        $chart->filter_system = '';
        $chart->filter_date_from = $data->DateFrom;
        $chart->filter_date_to = $data->DateTo;
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
             && (empty($data->country) && !empty($data->system) && (!empty($data->DateFrom) && !empty($data->DateTo))) ) {
        $chart->filter_country = '';
        $chart->filter_system = $data->system;
        $chart->filter_date_from = $data->DateFrom;
        $chart->filter_date_to = $data->DateTo;
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
             && (empty($data->country) && !empty($data->system) && (empty($data->DateFrom) && empty($data->DateTo))) ) {
        $chart->filter_country = '';
        $chart->filter_system = $data->system;
        $chart->filter_date_from = '';
        $chart->filter_date_to = '';
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
             && (!empty($data->country) && empty($data->system) && (empty($data->DateFrom) && empty($data->DateTo))) ) {
        $chart->filter_country = $data->country;
        $chart->filter_system = '';
        $chart->filter_date_from = '';
        $chart->filter_date_to = '';
    }
    elseif ( (isset($data->country) && isset($data->system) && (isset($data->DateFrom) && isset($data->DateTo))) 
             && (empty($data->country) && empty($data->system) && (!empty($data->DateFrom) && !empty($data->DateTo))) ) {
        $chart->filter_country = '';
        $chart->filter_system = '';
        $chart->filter_date_from = $data->DateFrom;
        $chart->filter_date_to = $data->DateTo;
    }

    // query
    $output = $chart->systemsChart();

    echo json_encode($output);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}