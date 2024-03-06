<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method: PUT, POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../models/BusinessInitiative.php';
        include_once '../../../administration/users/models/User.php';

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
        //Instantiate user object
        $user = new User($db);
        $businessInitiative = new BusinessInitiative($db);

        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if($userDetails===false) {
            setcookie("jwt", null, -1);
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        }  
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
        $requiredPermissions = ['view_group_strategy'];
        $requiredModules = 'Performance';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        
        $businessInitiative->this_user = $userDetails['userId'];
       
        $mycountry = $userDetails['country'];
        $my_department = $userDetails["department_val"];

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        if($_POST["operation"] == "Upload"){           

            $errorMsg = ''; 

            if (empty($_POST["country_strategy_id"]) || $_POST["country_strategy_id"] == "") {  
                $errorMsg = "Departmental Strategy is required";  
                die(json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                )); 
            } else {  

                $businessInitiative->country_strategy_id = clean_data($_POST["country_strategy_id"]);                 
            }

            if(empty($_FILES['csv_file']['name'])){
                $errorMsg = "The uploaded file is empty!"; 
                die(json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                )); 
            }  else {
                $file = $_FILES['csv_file']['tmp_name'];
                $handle = fopen($file, "r");
                $ok = false;
                fgetcsv($handle);

                while(($filesop = fgetcsv($handle, 1000, ",")) !== false){
                    
                    $c = count($filesop);
                    $empty = true;
                    
                    for ($x = 0; $x < $c; $x++) { 
                        $empty = $empty && (empty($filesop[$x]));

                        if ( empty($filesop[0]) || $filesop[0] == "" ) {
                            $errorMsg = 'Initiative is required';
                        }
                        if ( empty($filesop[1]) || $filesop[1] == "" ) {
                            $errorMsg = 'Target is required';
                        }
                        if ( empty($filesop[2]) || $filesop[2] == "" ) {
                            $errorMsg = 'Value Impact is required';
                        }

                        $checkExis = $db->prepare('SELECT * FROM strategy_initiatives WHERE initiative = :initiative AND country_strategy_id=:country_strategy_id ');
                        $checkExis->bindParam(':initiative', $filesop[0]);
                        $checkExis->bindParam(':country_strategy_id', $_POST["country_strategy_id"]);
                        $checkExis->execute();
                        $num = $checkExis->rowCount();
                            
                        if( $num > 0 ){
                            $errorMsg = 'Some Uploaded Initiatives already exist.';
                        }
                        
                    }
                    
                    if ($empty) {
                        break;
                    }					
                }
                if($errorMsg == '') {                    

                    $file = $_FILES['csv_file']['tmp_name'];
                    $handle = fopen($file, "r");
                    $ok = false;
                    fgetcsv($handle);

                    while(($filesop1 = fgetcsv($handle, 1000, ",")) !== false){

                        $c1 = count($filesop1);
                        $empty1 = true;
                        
                        for ($x = 0; $x < $c1; $x++) { 
                            $empty1 = $empty1 && (empty($filesop1[$x]));                             
                            
                        }
                        
                        if ($empty1) {
                            break;
                        }
                        else
                        {
                            
                            
                            $businessInitiative->initiative = clean_data($filesop1[0]); 
                            $businessInitiative->target = clean_data($filesop1[1]);
                            $businessInitiative->value_impact = clean_data($filesop1[2]);                            

                            $ok = $businessInitiative->upload();
                        }					
                    }
                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $errorMsg
                        )
                    );
                }

                if($ok){
                    echo json_encode(
                        array(
                            "success" => true,
                            "message" => "Initiatives Uploaded"
                        )
                    );
                } else {
                    if($errorMsg == '') { 
                        echo json_encode(
                            array(
                                "success" => false,
                                "message" => "Upload Failed"
                            )
                        );
                    }
                }
            }            
          
        }
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        