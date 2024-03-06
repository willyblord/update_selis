<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../../include/Database.php';
    include_once '../../models/User.php';

    //Instantiate SB and Connect
    $database = new Database();

    if ($_SERVER["REQUEST_METHOD"] == 'GET') {

        if (!isset($_COOKIE["jwt"]) && !isset($_COOKIE["jwt_r"])) { 
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }

        $db = $database->connect();
        //Instantiate user object
        $user = new User($db);

        $result = $user->check_expiration($_COOKIE["jwt"]);

        if( $result ) {

            if($result < 1) {
                setcookie("jwt", null, -1, '/');
                setcookie("jwt_r", null, -1, '/');
                echo json_encode([
                    'success' => true,
                    'reload' => true,
                    'message' => 'Session expired'
                ]);
                die();

            } else if($result <= 60) {
                echo json_encode([
                    'success' => true,
                    'reload' => false,
                    'remaining' => $result,
                    'message' => 'Session is about to expire'
                ]);
                die();
            }

        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid Identity',
            ]);
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }