<?php

//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Proposals.php';
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
    $proposal = new Proposals($db);
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
    $requiredPermissions = ['view_proposal'];
    $requiredModules = 'Funds';

    if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {

        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }


    $proposal->this_user = $userDetails['userId'];
    $proposal->country = $userDetails['country'];
    //$proposal->department = $userDetails['department_val'];

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
    $output = $proposal->read_all_proposals();

    echo json_encode($output);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}