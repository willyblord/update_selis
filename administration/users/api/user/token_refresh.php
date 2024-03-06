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

        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if($userDetails===false) {
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
        $requiredModules = '';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $result = $user->check_expiration($_COOKIE["jwt"]);

        if( $result ) {

            $refresh = $user->refresh_token($_COOKIE["jwt_r"]);

            if(!$refresh) {
                setcookie("jwt", null, -1, '/');
                setcookie("jwt_r", null, -1, '/');

                echo json_encode([
                    'success' => false,
                    'message' => 'Token refresh failed'
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