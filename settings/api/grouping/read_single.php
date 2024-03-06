<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../../../administration/users/models/User.php';
    include_once '../../models/Grouping.php';

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
        $grouping = new Grouping($db);

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

        if($user_details['can_be_super_user'] != 1 && $user_details['can_be_admin'] != 1 && $user_details['can_see_settings'] != 1) {
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
        $grouping->id = clean_data($_GET['id']);

        if(!($grouping->is_grouping_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $grouping->read_single();

        //create array
        $grouping_arr = array(
            'id' => $grouping->id,
            'group_name' => $grouping->group_name,
            'valuation_method' => $grouping->valuation_method,
            'created_by' => $grouping->created_by,
            'created_at' => $grouping->created_at,
            'updated_by' => $grouping->updated_by,
            'updated_at' => $grouping->updated_at
        );

        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $grouping_arr,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }


    ?>