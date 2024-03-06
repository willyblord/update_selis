<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method:POST');
        header('Content-Type:application/json');
        

        include_once '../../../../include/Database.php';
        include_once '../../models/User.php';

        //Instantiate SB and Connect
        $database = new Database();

        if (!isset($_COOKIE["jwt"])) { 
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }

        $db = $database->connect();
        //Instantiate idea object
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
        $this_user_id = $user_details['userId'];
        $reply_email_to = $user_details['email'];
        $reply_name = $user_details['name'];
        $reply_surname = $user_details['surname']; 

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }
        
        // $user->this_user = $_SESSION['userIdentification'];

        if( isset($data->operation) && $data->operation == 'deactivate') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_user'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "inactive";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->deactivate()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Deactivated"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
                        )
                    );                    
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }

        } elseif( isset($data->operation) && $data->operation == 'activate') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_add_user'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "active";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->activate()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Activated"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
                        )
                    );                    
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }

        } elseif( isset($data->operation) && $data->operation == 'reset_password') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_reset_user_password'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $user->userId = clean_data($data->id); 
                $user_id = clean_data($data->id);
            } 

            function random_password(){
                $random_characters = 2;
                
                $lower_case = "abcdefghijklmnopqrstuvwxyz";
                $upper_case = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $numbers = "1234567890";
                $symbols = "!@#$%^&*";
                
                $lower_case = str_shuffle($lower_case);
                $upper_case = str_shuffle($upper_case);
                $numbers = str_shuffle($numbers);
                $symbols = str_shuffle($symbols);
                
                $random_password = substr($lower_case, 0, $random_characters);
                $random_password .= substr($upper_case, 0, $random_characters);
                $random_password .= substr($numbers, 0, $random_characters);
                $random_password .= substr($symbols, 0, $random_characters);
                
                return  str_shuffle($random_password);
            }
            $passcode = random_password();
            
            $password = password_hash($passcode, PASSWORD_DEFAULT);
            $user->password = $password; 

            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->reset_password()) {

                    // Email Notification Logic
                    $to_name = $to_surname = $email_to = '';

                    if($user_data = $user->user_auth_details($user_id)){
                        foreach ($user_data as $row) {
                            $to_name = $row['name'];
                            $to_surname = $row['surname'];
                            $email_to =$row['email'];

                            $title = 'PASSWORD RESET | SERIS';
                            $sender = $this_user_id;

                            $message = 'This is to notify you that your SERIS password has been reset by
                                        <b>'.$reply_name.' '.$reply_surname.'</b> and your password is:
                                        <br>
                                        Password: <b>'.$passcode.'</b>
                                    ';
                            
                            $user->save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender );
                        }
                    }

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Password Reset"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
                        )
                    );                    
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }

        } elseif( isset($data->operation) && $data->operation == 'delete') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_delete_user'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 

            $user->status = "deleted";
            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                //update idea
                if($user->delete()) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "User Deleted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
                        )
                    );                    
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }

        }  elseif( isset($data->operation) && $data->operation == 'permissions') {

            if($user_details['can_be_super_user'] != 1 && $user_details['can_give_privileges'] != 1) {
                echo json_encode([
                    'success' => false,
                    'message' => "Unauthorized Resource"
                ]);
                die();
            }

            $errorMsg = '';

            if (empty($data->id)) {  
                $errorMsg = "ID is required";  
            } else { 
                // Set ID to update 
                $user->userId = clean_data($data->id); 
            } 
            
            $user->can_add_user =  (isset($data->can_add_user) && $data->can_add_user == "1") ? 1 : 0;	
			$user->can_view_user =  (isset($data->can_view_user) && $data->can_view_user == "1") ? 1 : 0;
			$user->can_edit_user =  (isset($data->can_edit_user) && $data->can_edit_user == "1") ? 1 : 0;			
			$user->can_deactivate_user =  (isset($data->can_deactivate_user) && $data->can_deactivate_user == "1") ? 1 : 0;				
			$user->can_reset_user_password =  (isset($data->can_reset_user_password) && $data->can_reset_user_password == "1") ? 1 : 0;	
			$user->can_delete_user =  (isset($data->can_delete_user) && $data->can_delete_user == "1") ? 1 : 0;			
			$user->can_give_privileges =  (isset($data->can_give_privileges) && $data->can_give_privileges == "1") ? 1 : 0;	 
			$user->can_see_settings =  (isset($data->can_see_settings) && $data->can_see_settings == "1") ? 1 : 0;	 
			$user->can_update_notifications =  (isset($data->can_update_notifications) && $data->can_update_notifications == "1") ? 1 : 0;	 
            
			$user->can_be_country_manager =  (isset($data->can_be_country_manager) && $data->can_be_country_manager == "1") ? 1 : 0;	
			$user->can_be_exco =  (isset($data->can_be_exco) && $data->can_be_exco == "1") ? 1 : 0;	
			$user->can_be_gmd =  (isset($data->can_be_gmd) && $data->can_be_gmd == "1") ? 1 : 0;	
			$user->can_be_coo =  (isset($data->can_be_coo) && $data->can_be_coo == "1") ? 1 : 0;	
			
			$user->can_add_activity =  (isset($data->can_add_activity) && $data->can_add_activity == "1") ? 1 : 0;		 
			$user->can_be_activity_hod =  (isset($data->can_be_activity_hod) && $data->can_be_activity_hod == "1") ? 1 : 0;	 
			$user->can_be_activity_coo =  (isset($data->can_be_activity_coo) && $data->can_be_activity_coo == "1") ? 1 : 0;	 
			$user->can_be_activity_country_manager =  (isset($data->can_be_activity_country_manager) && $data->can_be_activity_country_manager == "1") ? 1 : 0;		
			$user->can_be_activity_md =  (isset($data->can_be_activity_md) && $data->can_be_activity_md == "1") ? 1 : 0;				
			$user->can_view_activities_reports =  (isset($data->can_view_activities_reports) && $data->can_view_activities_reports == "1") ? 1 : 0;
			
			$user->can_be_incident_pro_manager =  (isset($data->can_be_incident_pro_manager) && $data->can_be_incident_pro_manager == "1") ? 1 : 0;
			$user->can_view_incident_reports =  (isset($data->can_view_incident_reports) && $data->can_view_incident_reports == "1") ? 1 : 0;
			
			$user->can_add_ideas =  (isset($data->can_add_ideas) && $data->can_add_ideas == "1") ? 1 : 0;
			$user->can_do_ideas_funneling =  (isset($data->can_do_ideas_funneling) && $data->can_do_ideas_funneling == "1") ? 1 : 0;
			$user->can_do_ideas_sharktank =  (isset($data->can_do_ideas_sharktank) && $data->can_do_ideas_sharktank == "1") ? 1 : 0;
			$user->can_view_ideas_reports =  (isset($data->can_view_ideas_reports) && $data->can_view_ideas_reports == "1") ? 1 : 0;
			
			$user->can_add_booking =  (isset($data->can_add_booking) && $data->can_add_booking == "1") ? 1 : 0;	
			$user->can_be_approver =  (isset($data->can_be_approver) && $data->can_be_approver == "1") ? 1 : 0;				
			$user->can_view_book_reports =  (isset($data->can_view_book_reports) && $data->can_view_book_reports == "1") ? 1 : 0;	
			
			$user->can_add_cash_requests =  (isset($data->can_add_cash_requests) && $data->can_add_cash_requests == "1") ? 1 : 0;		 
			$user->can_be_cash_hod =  (isset($data->can_be_cash_hod) && $data->can_be_cash_hod == "1") ? 1 : 0;	 
			$user->can_be_cash_coo =  (isset($data->can_be_cash_coo) && $data->can_be_cash_coo == "1") ? 1 : 0;	 
			$user->can_be_cash_manager =  (isset($data->can_be_cash_manager) && $data->can_be_cash_manager == "1") ? 1 : 0;	
			$user->can_prosess_flight =  (isset($data->can_prosess_flight) && $data->can_prosess_flight == "1") ? 1 : 0;		
			$user->can_be_cash_finance =  (isset($data->can_be_cash_finance) && $data->can_be_cash_finance == "1") ? 1 : 0;					
			$user->can_view_cash_reports =  (isset($data->can_view_cash_reports) && $data->can_view_cash_reports == "1") ? 1 : 0;				
					
			$user->can_add_equip_requests =  (isset($data->can_add_equip_requests) && $data->can_add_equip_requests == "1") ? 1 : 0;		
			$user->can_be_equip_hod =  (isset($data->can_be_equip_hod) && $data->can_be_equip_hod == "1") ? 1 : 0;		
			$user->can_be_equip_inn =  (isset($data->can_be_equip_inn) && $data->can_be_equip_inn == "1") ? 1 : 0;		
			$user->can_be_equip_country_manager =  (isset($data->can_be_equip_country_manager) && $data->can_be_equip_country_manager == "1") ? 1 : 0;		
			$user->can_be_equip_coo =  (isset($data->can_be_equip_coo) && $data->can_be_equip_coo == "1") ? 1 : 0;		
			$user->can_be_equip_operations =  (isset($data->can_be_equip_operations) && $data->can_be_equip_operations == "1") ? 1 : 0;		
			$user->can_be_equip_gmd =  (isset($data->can_be_equip_gmd) && $data->can_be_equip_gmd == "1") ? 1 : 0;		
			$user->can_view_equip_reports =  (isset($data->can_view_equip_reports) && $data->can_view_equip_reports == "1") ? 1 : 0;

			$user->can_view_monitoring_tasks =  (isset($data->can_view_monitoring_tasks) && $data->can_view_monitoring_tasks == "1") ? 1 : 0;			
			$user->can_be_monitoring_pro_manager =  (isset($data->can_be_monitoring_pro_manager) && $data->can_be_monitoring_pro_manager == "1") ? 1 : 0;			
			$user->can_view_monitoring_reports =  (isset($data->can_view_monitoring_reports) && $data->can_view_monitoring_reports == "1") ? 1 : 0;
			$user->can_view_my_minutes =  (isset($data->can_view_my_minutes) && $data->can_view_my_minutes == "1") ? 1 : 0;		
			$user->can_add_all_minutes =  (isset($data->can_add_all_minutes) && $data->can_add_all_minutes == "1") ? 1 : 0;		
			$user->can_view_minute_reports =  (isset($data->can_view_minute_reports) && $data->can_view_minute_reports == "1") ? 1 : 0;	
			$user->can_view_copay =  (isset($data->can_view_copay) && $data->can_view_copay == "1") ? 1 : 0;		
			$user->can_add_copay =  (isset($data->can_add_copay) && $data->can_add_copay == "1") ? 1 : 0;	
			$user->can_activate_copay =  (isset($data->can_activate_copay) && $data->can_activate_copay == "1") ? 1 : 0;		
			$user->can_view_copay_reports =  (isset($data->can_view_copay_reports) && $data->can_view_copay_reports == "1") ? 1 : 0;
            
			$user->can_add_strategy_bsc =  (isset($data->can_add_strategy_bsc) && $data->can_add_strategy_bsc == "1") ? 1 : 0;	
			$user->can_be_strategy_hod =  (isset($data->can_be_strategy_hod) && $data->can_be_strategy_hod == "1") ? 1 : 0;	
			$user->can_be_strategy_division =  (isset($data->can_be_strategy_division) && $data->can_be_strategy_division == "1") ? 1 : 0;
			$user->can_be_strategy_hr =  (isset($data->can_be_strategy_hr) && $data->can_be_strategy_hr == "1") ? 1 : 0;

            $user->this_user = $this_user_id;

            if($errorMsg =='') {
                if($user->is_privilege_exists()) {
                    $query = $user->update_privileges();  
                } else {
                    
                    $query = $user->create_privileges();  
                }

                if($query) {

                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Permissions Granted"
                        )
                    );
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => "Changes Failed"
                        )
                    );                    
                }
            } else {
                echo json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                );
            }

        } else {
            echo json_encode(
                array(
                    "success" => false,
                    "message" => "Unkown Operation"
                )
            );
        }