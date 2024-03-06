<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/Strategy.php';
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
        $strategy = new Strategy($db);

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
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
        $requiredPermissions = ['view_group_strategy'];
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
        $strategy->id = clean_data($_GET['id']);

        if(!($strategy->is_strategy_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $strategy->read_single();   
        
        $extr = explode("-", $strategy->year_range, 2);
        $first = $extr[0];
        $second = $extr[1];

        //create array
        $user_arr = array(
            'id' => $strategy->id,
            'strategy_name' => $strategy->strategy_name,
			'year_range' => $strategy->year_range,
			'fromYear' => $first,
			'toYear' => $second,
			'vision' => $strategy->vision,
			'mission' => $strategy->mission,
			'status' => $strategy->status,
			'created_by' => $strategy->created_by,
			'created_at' => $strategy->created_at,
			'updated_by' => $strategy->updated_by,
			'updated_at' => $strategy->updated_at
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