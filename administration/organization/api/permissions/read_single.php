<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../../include/Database.php';
    include_once '../../models/User.php';
    include_once '../../models/Permission.php';

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
        $permission = new Permission($db);

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
        $permission->id = clean_data($_GET['id']);

        if(!($permission->is_permission_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $permission->read_single();

        //create array
        $permission_arr = array(
            'id' => $permission->id,
            'permission_name' => $permission->permission_name
        );
        
        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $permission_arr,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }