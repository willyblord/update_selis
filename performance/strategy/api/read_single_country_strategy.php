<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/CountryStrategy.php';
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
        $countryStrategy = new CountryStrategy($db);

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
        
        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }               

        //Get ID
        $countryStrategy->id = clean_data($_GET['id']);

        if(!($countryStrategy->is_strategy_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $countryStrategy->read_single(); 

        //create array
        $user_arr = array(
            'id' => $countryStrategy->id,
            'group_strategy_id' => $countryStrategy->group_strategy_id,
			'strategy_name' => $countryStrategy->strategy_name,
			'country_name' => $countryStrategy->country_name,
			'country' => $countryStrategy->country,
			'division_name' => $countryStrategy->division_name,
			'division' => $countryStrategy->division,
			'year' => $countryStrategy->year,
			'status' => $countryStrategy->status,
			'created_by' => $countryStrategy->created_by,
			'created_at' => $countryStrategy->created_at,
			'updated_by' => $countryStrategy->updated_by,
			'updated_at' => $countryStrategy->updated_at,            
			'returned_by' => $countryStrategy->returned_by,
			'returned_at' => $countryStrategy->returned_at,
			'return_reason' => $countryStrategy->return_reason,
			'rejected_by' => $countryStrategy->rejected_by,
			'rejected_at' => $countryStrategy->rejected_at,
			'reject_reason' => $countryStrategy->reject_reason,
			'division_approved_by' => $countryStrategy->division_approved_by,
			'division_approved_at' => $countryStrategy->division_approved_at,
			'coo_approved_by' => $countryStrategy->coo_approved_by,
			'coo_approved_at' => $countryStrategy->coo_approved_at,
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