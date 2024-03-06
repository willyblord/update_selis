<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method: PUT, POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../../../administration/users/models/User.php';
        include_once '../models/Pillar.php';
        include_once '../models/Strategy.php';


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
        $strategy = new Strategy($db);
        $pillar = new Pillar($db);

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
        
        $this_user_id = $userDetails['userId'];  

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

            if (empty($_POST["stratId"]) || $_POST["stratId"] == "") {
                $errorMsg = "Strategy is required";  
                die(json_encode(
                    array(
                        "success" => false,
                        "message" => $errorMsg
                    )
                )); 
            } else {  

                $strategy->id = clean_data($_POST["stratId"]); 

                if(!($strategy->is_strategy_exists())) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Strategy Not Found'
                    ]);
                    die();
                }        
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
                            $errorMsg = 'Strategy Pillar is required';
                        }
                        if ( empty($filesop[1]) || $filesop[1] == "" ) {
                            $errorMsg = 'Strategic Objective is required';
                        }
                        if ( empty($filesop[2]) || $filesop[2] == "" ) {
                            $errorMsg = 'Strategic Initiative is required';
                        }
                        if ( empty($filesop[3]) || $filesop[3] == "" ) {
                            $errorMsg = 'target is required';
                        }
                        if ( empty($filesop[4]) || $filesop[4] == "" ) {
                            $errorMsg = 'Picture of Success is required';
                        }
                        if ( empty($filesop[5]) || $filesop[5] == "" ) {
                            $errorMsg = 'Measure is required';
                        }
                        if ( empty($filesop[6]) || $filesop[6] == "" ) {
                            $errorMsg = 'Timeline is required';
                        }

                        $checkExis = $db->prepare('SELECT * FROM strategy_pillars_group_level WHERE strategy_pillar = :strategy_pillar');
                        $checkExis->bindParam(':strategy_pillar', $filesop[0]);
                        $checkExis->execute();
                        $num = $checkExis->rowCount();
                            
                        if( $num > 0 ){
                            $errorMsg = 'Some Uploaded Pillars already exist.';
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
                            $pillar->strategy_id = clean_data($_POST["stratId"]);  
                            $pillar->strategy_pillar = clean_data($filesop1[0]); 
                            $pillar->strategic_objective = clean_data($filesop1[1]); 
                            $pillar->strategic_initiative = clean_data($filesop1[2]); 
                            $pillar->target = clean_data($filesop1[3]); 
                            $pillar->picture_of_success = clean_data($filesop1[4]); 
                            $pillar->measure = clean_data($filesop1[5]); 
                            $pillar->timeline = clean_data($filesop1[6]); 

                            $pillar->this_user = $this_user_id;
                            

                            $ok = $pillar->upload();
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
                            "message" => "Properties Uploaded"
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
        