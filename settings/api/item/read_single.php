<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../../../administration/users/models/User.php';
    include_once '../../models/Item.php';

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
        $item = new Item($db);

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
        $item->id = clean_data($_GET['id']);

        // if(!($item->is_item_exists())) {
        //     echo json_encode([
        //         'success' => false,
        //         'message' => 'Not Found'
        //     ]);
        //     die();
        // } 

        //Get user
        $item->read_single();

        //create array
        $item_arr = array(
            'id' => $item->id,
            'grouping_id' => $item->grouping_id,
            'group_name' => $item->group_name,
            'item_name' => $item->item_name,
            'unit' => $item->unit,
            'price_per_unit' => $item->price_per_unit,
            'created_by' => $item->created_by,
            'created_at' => $item->created_at,
            'updated_by' => $item->updated_by,
            'updated_at' => $item->updated_at
        );

        //make JSON
        echo json_encode([
            'success' => true,
            'message' => 'Data Found',
            'data' => $item_arr,
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }


    ?>