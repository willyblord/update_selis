<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/BusinessInitiative.php';
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
        //Instantiate user object
        $user = new User($db);
        $businessInitiative = new BusinessInitiative($db);

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

        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
        $requiredPermissions = ['add_comment_strategy'];
        $requiredModules = 'Performance';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        
        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }               

        //Get ID
        $businessInitiative_id = clean_data($_GET['id']);

        //Get user
        $comments = $businessInitiative->get_department_comments($businessInitiative_id); 
        
        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $comments,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }