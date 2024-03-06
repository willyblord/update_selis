<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/IndividualBsc.php';
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
        $individualBsc = new IndividualBsc($db);

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
        $requiredPermissions = ['view_bsc'];
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
        $individualBsc->parameter_id = clean_data($_GET['id']);

        if(!($individualBsc->is_bsc_parameter_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $individualBsc->read_single_parameter(); 

        //create array
        $user_arr = array(
            'id' => $individualBsc->parameter_id,
            'bsc_parameter_id' => $individualBsc->bsc_parameter_id,
            'parameter_weight' => $individualBsc->parameter_weight
        );
        
        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $user_arr,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }