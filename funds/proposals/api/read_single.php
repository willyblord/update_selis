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

if ($_SERVER["REQUEST_METHOD"] == 'GET') {

    if (!isset($_COOKIE["jwt"])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please Login'
        ]);
        die();
    }

    $db = $database->connect();
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
    $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
    $requiredPermissions = ['view_proposal'];
    $requiredModules = 'Funds';

    if (!$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {

        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    //Get ID
    $proposal->id = clean_data($_GET['id']);

    if (!($proposal->is_proposal_exists())) {
        echo json_encode([
            'success' => false,
            'message' => 'Not Found'
        ]);
        die();
    }
    //Get Proposal
    $proposal->read_single();
    //create array
    $proposal_data = array(
        'id' => $proposal->id,
        'refNo' => $proposal->refNo,
        'proposal_date' => $proposal->proposal_date,
        'subject' => $proposal->subject,
        'price' => $proposal->price,
        'total' => $proposal->total,
        'budget_line' => $proposal->budget_line,
        'remaining_amount' => $proposal->remaining_amount,
        'department' => $proposal->department,
        'onbehalf_of' => $proposal->onbehalf_of,
        'objective' => $proposal->objective,
        'introduction' => $proposal->introduction,
        'additional_doc' => $proposal->additional_doc,
        'returnReason' => $proposal->returnReason,
        'FTotal' => $proposal->FTotal,
        'supplier' => $proposal->supplier,
        'proposalItem_array' => $proposal->proposalItem_array,
        'commentArray' => $proposal->commentArray,
    );

    //make JSON
    echo json_encode([
        'success' => true,
        'message' => 'Data Found',
        'data' => $proposal_data,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}