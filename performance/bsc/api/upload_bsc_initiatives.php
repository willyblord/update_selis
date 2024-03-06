<?php

        //Headers
        header('Access-Control-Allow-Origin:*');
        header('Access-Control-Allow-Method: PUT, POST');
        header('Content-Type:application/json');

        include_once '../../../include/Database.php';
        include_once '../models/BSCInitiatives.php';
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
        $bscInitiatives = new BSCInitiatives($db);

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
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
        $requiredPermissions = ['add_bsc'];
        $requiredModules = 'Performance';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }
        
        $bscInitiatives->this_user = $userDetails['userId'];
       
        $mycountry = $userDetails['country_id'];
        $my_department = $userDetails["department_id"];

        //get raw posted data
        // $data = json_decode(urldecode(file_get_contents("php://input")));
        $data = json_decode($_POST['tableData'], true);

        
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tableData'])) {         

            $errorMsg = ''; 
            $ok = false;

            if (empty($data)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You submitted empty file'
                ]);
                die();
            }
            
            // Define expected column names
            $expectedColumnNames = ["TARGET", "OBJECTIVE", "TIMELINE", "MEASURE", "FIGURE_COUNT_PERCENTAGE", "WEIGHT"];

            // Validate column names
            $columnNames = array_keys($data[0]);
            if ($columnNames !== $expectedColumnNames) {
                // Column names do not match expected names
                echo json_encode([
                    'success' => false,
                    'message' => 'Column names in the submitted data do not match the expected format.'
                ]);
                die();
            } else {

                function formatDateToYYYYMMDD($date) {
                    // Attempt to parse the date using strtotime()
                    $timestamp = strtotime($date);                
                    // If strtotime() fails, return false
                    if ($timestamp === false) {
                        return false;
                    }                
                    // Convert the timestamp to YYYY-MM-DD format
                    $formattedDate = date('Y-m-d', $timestamp);
                    
                    return $formattedDate;
                }

                $bscInitiatives->individual_bsc_id = 1;
                // Validate each row of data
                foreach ($data as $rowData) {
                    // Perform validation for each field
                    $errors = [];
                    
                    // Example validation: check if required fields are not empty
                    if (empty($rowData['TARGET'])) {
                        $errors[] = 'Target is required.';
                    } else {
                        $bscInitiatives->target = $rowData['TARGET'];
                    }

                    if (empty($rowData['OBJECTIVE'])) {
                        $errors[] = 'Objective/Measures is required.';
                    } else {
                        $bscInitiatives->value_impact = $rowData['OBJECTIVE'];
                    }

                    if (empty($rowData['TIMELINE'])) {
                        $errors[] = 'Field3 must be a valid date in the format YYYY-MM-DD.';
                    } else {
                        $bscInitiatives->timeline = formatDateToYYYYMMDD($rowData['TIMELINE']);
                    }

                    if (empty($rowData['FIGURE_COUNT_PERCENTAGE'])) {
                        $errors[] = 'rowData is required.';
                    } else {
                        $bscInitiatives->figure = $rowData['FIGURE_COUNT_PERCENTAGE'];
                    }

                    if (empty($rowData['MEASURE'])) {
                        $errors[] = 'Target Figure is required.';
                    } else {
                        $bscInitiatives->measure = $rowData['MEASURE'];
                    }

                    if (empty($rowData['WEIGHT'])) {
                        $errors[] = 'Weight Figure is required.';
                    } else {                    
                        $bscInitiatives->weight = $rowData['WEIGHT'];
                    }

                    // Check if any errors occurred during validation
                    if (!empty($errors)) {
                        // Handle validation errors (e.g., log errors, return error response to client)
                        $errorMessages = implode(' ', $errors);
                        echo json_encode(
                            array(
                                "success" => false,
                                "message" => $errorMessages
                            )
                        );
                        die(); // Stop processing further
                    }

                    // If validation passed, proceed to insert data into the database
                    // Assuming you have a function to insert data into the database
                    // You would replace this with your own code
                    $ok = $bscInitiatives->upload();
                }
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
        else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }
        