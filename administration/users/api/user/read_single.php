<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../../include/Database.php';
    include_once '../../models/User.php';

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
        $requiredPermissions = ['view_user'];
        $requiredModules = 'Administration';
        
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
        $user->userId = clean_data($_GET['id']);

        if(!($user->is_user_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $user->read_single();

        //create array
        $user_arr = array(
            'id' => $user->userId,
            'staffNumber' => $user->staffNumber,
            'name' => $user->name,
            'names' => $user->name.' '.$user->surname,
            'surname' => $user->surname,
            'country_name' => $user->country_name,
            'country_id' => $user->country_id,
            'division_name' => $user->division_name,
            'division_id' => $user->division_id,
            'department_name' => $user->department_name,
            'department_id' => $user->department_id,
            'unit_name' => $user->unit_name,
            'unit_id' => $user->unit_id,
            'section_name' => $user->section_name,
            'section_id' => $user->section_id,
            'roles' => $user->roles,
            'isOnLeave' => $user->isOnLeave,
            'email' => $user->email,
            'username' => $user->username,
            'status' => $user->status,
            'deactivated_by' => $user->deactivated_by,
            'deactivated_at' => $user->deactivated_at,
            'forgot_password_date' => $user->forgot_password_date,
            'created_by' => $user->registeredBy,
            'created_at' => $user->registerDate,
            'updated_by' => $user->editedBy,
            'updated_at' => $user->editedDate,
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