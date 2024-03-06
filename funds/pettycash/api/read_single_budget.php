<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../include/Database.php';
    include_once '../models/Budget.php';
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
        $budget = new Budget($db);

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
        if ($user_details['can_be_super_user'] != 1 && $user_details['can_be_cash_finance'] != 1 && $user_details['can_be_cash_coo'] != 1) {
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
        $budget->id = clean_data($_GET['id']);

        if(!($budget->is_budget_exists())) {
            echo json_encode([
                'success' => false,
                'message' => 'Not Found'
            ]);
            die();
        } 

        //Get user
        $budget->read_single();

        //create array
        $user_arr = array(
            'id' => $budget->id,
            'department' => $budget->department,
			'department_val' => $budget->department_val,
			'budget_category' => $budget->budget_category,
			'budget_category_val' => $budget->budget_category_val,
			'start_date' => $budget->start_date,
			'end_date' => $budget->end_date,
			'initial_amount' => $budget->initial_amount,
			'initial_amount_Val' => $budget->initial_amount_Val,
			'topup_amount' => $budget->topup_amount,
			'topup_amount_Val' => $budget->topup_amount_Val,
			'deducted_amount' => $budget->deducted_amount,
			'deducted_amount_Val' => $budget->deducted_amount_Val,
			'total_amount' => $budget->total_amount,
			'total_amount_Val' => $budget->total_amount_Val,
			'used_amount' => $budget->used_amount,
			'used_amount_Val' => $budget->used_amount_Val,
			'remaining_amount' => $budget->remaining_amount,
			'remaining_amount_Val' => $budget->remaining_amount_Val,
			'status' => $budget->status,
			'insterted_by' => $budget->insterted_by,
			'inserted_at' => $budget->inserted_at,
			'updated_at' => $budget->updated_at,
			'updated_by' => $budget->updated_by
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