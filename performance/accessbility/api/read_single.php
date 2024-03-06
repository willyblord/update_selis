<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/SystemsDowntime.php';
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
        $downtime = new SystemsDowntime($db);

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
        if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_coo'] != 1) {
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
        $downtime->id = clean_data($_GET['id']);

        if(!($downtime->is_downtime_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $downtime->read_single();        

        //create array
        $user_arr = array(
            'id' => $downtime->id,
            'refNo' => $downtime->refNo,
			'country' => $downtime->country,
			'country_val' => $downtime->country_val,
			'system' => $downtime->system,
			'system_val' => $downtime->system_val,
			'downtime' => $downtime->downtime,
			'time_started' => $downtime->time_started,
			'time_resolved' => $downtime->time_resolved,
			'tat_in_minutes' => $downtime->tat_in_minutes,
			'hours_in_minutes' => $downtime->hours_in_minutes,
			'rca' => $downtime->rca,
			'created_by' => $downtime->created_by,
			'created_at' => $downtime->created_at,
			'updated_at' => $downtime->updated_at,
			'updated_by' => $downtime->updated_by
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