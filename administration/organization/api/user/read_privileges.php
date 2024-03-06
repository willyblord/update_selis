<?php

    //Headers
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
        if($user_details['can_be_super_user'] != 1 && $user_details['can_give_privileges'] != 1) {
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
        $user->userId = $_GET['id'];

    //    if(!($user->is_privilege_exists())) {
    //         echo json_encode([
    //             'success' => false,
    //             'message' => 'Not Found'
    //         ]);
    //         die();
    //     } 

        //Get user
        $user->read_user_privileges();

        //create array
        $user_arr = array(
            'names' => $user->name.' '.$user->surname,
            'email' => $user->email,
            'userId'  => $user->userId,
            'can_add_user' => $user->can_add_user, 
            'can_view_user' => $user->can_view_user, 
            'can_edit_user' => $user->can_edit_user, 
            'can_deactivate_user' => $user->can_deactivate_user, 
            'can_reset_user_password' => $user->can_reset_user_password, 
            'can_delete_user' => $user->can_delete_user, 
            'can_give_privileges' => $user->can_give_privileges, 
            'can_see_settings' 	=> $user->can_see_settings, 
            'can_update_notifications' => $user->can_update_notifications, 
            'can_be_country_manager' => $user->can_be_country_manager, 
            'can_be_exco' => $user->can_be_exco, 
            'can_be_gmd' => $user->can_be_gmd, 
            'can_be_coo' => $user->can_be_coo, 
            'can_add_activity' => $user->can_add_activity, 
            'can_be_activity_hod' => $user->can_be_activity_hod, 
            'can_be_activity_coo' => $user->can_be_activity_coo, 
            'can_be_activity_country_manager' => $user->can_be_activity_country_manager, 
            'can_be_activity_md' => $user->can_be_activity_md, 							
            'can_view_activities_reports' => $user->can_view_activities_reports,
            'can_be_incident_pro_manager' => $user->can_be_incident_pro_manager, 	
            'can_view_incident_reports' => $user->can_view_incident_reports, 	
            'can_add_ideas' => $user->can_add_ideas, 	
            'can_do_ideas_funneling' => $user->can_do_ideas_funneling, 	
            'can_do_ideas_sharktank' => $user->can_do_ideas_sharktank, 	
            'can_view_ideas_reports' => $user->can_view_ideas_reports, 
            'can_add_booking' => $user->can_add_booking, 	
            'can_be_approver' => $user->can_be_approver, 							
            'can_view_book_reports' => $user->can_view_book_reports, 
            'can_add_cash_requests' => $user->can_add_cash_requests, 
            'can_be_cash_hod' => $user->can_be_cash_hod, 
            'can_be_cash_coo' => $user->can_be_cash_coo, 
            'can_be_cash_manager' => $user->can_be_cash_manager, 
            'can_prosess_flight' => $user->can_prosess_flight, 
            'can_be_cash_finance' => $user->can_be_cash_finance, 						
            'can_view_cash_reports' => $user->can_view_cash_reports, 											
            'can_add_equip_requests' => $user->can_add_equip_requests, 					
            'can_be_equip_hod' 	=> $user->can_be_equip_hod, 					
            'can_be_equip_inn' 	=> $user->can_be_equip_inn, 					
            'can_be_equip_country_manager' => $user->can_be_equip_country_manager, 					
            'can_be_equip_coo' => $user->can_be_equip_coo, 					
            'can_be_equip_operations' => $user->can_be_equip_operations, 					
            'can_be_equip_gmd' => $user->can_be_equip_gmd, 					
            'can_view_equip_reports' => $user->can_view_equip_reports, 
            'can_view_monitoring_tasks' => $user->can_view_monitoring_tasks, 					
            'can_be_monitoring_pro_manager' => $user->can_be_monitoring_pro_manager, 					
            'can_view_monitoring_reports' => $user->can_view_monitoring_reports, 
            'can_view_my_minutes' => $user->can_view_my_minutes, 
            'can_add_all_minutes' => $user->can_add_all_minutes, 
            'can_view_minute_reports' => $user->can_view_minute_reports, 	
            'can_view_copay' => $user->can_view_copay, 	
            'can_add_copay' => $user->can_add_copay, 
            'can_activate_copay' => $user->can_activate_copay, 
            'can_view_copay_reports' => $user->can_view_copay_reports,
            'can_add_strategy_bsc' => $user->can_add_strategy_bsc, 
            'can_be_strategy_hod' => $user->can_be_strategy_hod, 
            'can_be_strategy_division' => $user->can_be_strategy_division, 
            'can_be_strategy_hr' => $user->can_be_strategy_hr
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