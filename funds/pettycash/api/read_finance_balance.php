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

if ($_SERVER["REQUEST_METHOD"] == 'GET') {

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
    if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1) {
        echo json_encode([
            'success' => false,
            'message' => "Unauthorized Resource"
        ]);
        die();
    }

    $country_details = $user->get_country($user_details['country']);

    $country = $user_details['country'];

    $bal = '0 '.$country_details['country_currency'];
    if($row = $pettycash->get_finance_balance($country)) {

        $bal = number_format($row['amount'],2).''.$country_details['country_currency'];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Data Found',
        'amount' => $bal,
    ]);

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}