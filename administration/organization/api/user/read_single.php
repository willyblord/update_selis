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
        $user_details = $user->validate($_COOKIE["jwt"]);
        if($user_details===false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');

            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 
        if($user_details['can_be_super_user'] != 1 && $user_details['can_view_user'] != 1) {
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
            'country' => $user->country,
            'country_val' => $user->country_val,
            'department' => $user->department,
            'department_val' => $user->department_val,
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