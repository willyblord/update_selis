<?php 
    //Headers
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json'); 

    include_once '../include/Database.php';
    include_once '../administration/users/models/User.php'; 

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
        //Instantiate Project object
        $user = new User($db);
    
        //Check jwt validation
        $userDetails = $user->validate($_COOKIE["jwt"]);
        if ($userDetails === false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        } 
        
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
        $requiredPermissions = [];
        $requiredModules = '';
        
        if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $this_user = $userDetails['userId'];
        $mycountry = $userDetails['country_id'];
        $my_division_id = $userDetails["division_id"];
        $my_department = $userDetails["department_id"];
        $my_unit = $userDetails["unit_id"];
        $my_section = $userDetails["section_id"];
        $managerId = $userDetails['managerId'];
        
        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }  
        //Get ID
        $pillar_id = clean_data($_GET['id']);

        $requiredRoles = ['SUPER_USER_ROLE'];
        $requiredPermissions = [];
        $requiredModules = '';
        
        if ( $user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

            $query = ' SELECT i.id, i.group_initiative 
                        FROM strategy_group_initiatives i
                        LEFT JOIN strategy_initiatives s ON i.id = s.initiative_id
                        WHERE i.pillar_id = :pillar_id AND ( (i.type = 1 AND s.created_by = '.$this_user.') OR (i.type = 2 AND i.created_by = '.$this_user.'))
            
                    ';
        }
        elseif($userDetails['approvalLevel'] == 2 || $userDetails['approvalLevel'] == 3 || $userDetails['approvalLevel'] == 4 || $userDetails['approvalLevel'] == 5){
        
            $query = ' SELECT i.id, i.group_initiative 
                        FROM strategy_group_initiatives i
                        LEFT JOIN strategy_initiatives s ON i.id = s.initiative_id
                        WHERE i.pillar_id = :pillar_id AND ( (i.type = 1 AND s.created_by = '.$this_user.') OR (i.type = 2 AND i.created_by = '.$this_user.'))
            
                    ';
        }
        elseif( ($userDetails['approvalLevel'] == 6 || $userDetails['approvalLevel'] == 7 || $userDetails['approvalLevel'] == 8) && ($userDetails['managerId'] != '')){
        
            $query = 'SELECT i.id, i.group_initiative 
                        FROM strategy_group_initiatives i
                        LEFT JOIN strategy_individual_initiatives_bsc s ON i.id = s.initiative_id
                        LEFT JOIN strategy_individual_bsc bs ON s.individual_bsc_id = bs.id
                        LEFT JOIN approval_hierarchy p ON bs.bsc_owner = p.manager_id
                        WHERE i.pillar_id = :pillar_id AND ((p.manager_id = '.$managerId.') OR (i.type = 2 AND i.created_by = '.$this_user.'))
                    ';
        } 
        elseif($userDetails['approvalLevel'] == "") {

            if($my_section != "") {
                $query = 'SELECT i.id, i.group_initiative 
                            FROM strategy_group_initiatives i
                            LEFT JOIN strategy_individual_initiatives_bsc s ON i.id = s.initiative_id
                            LEFT JOIN strategy_individual_bsc bs ON s.individual_bsc_id = bs.id
                            LEFT JOIN approval_hierarchy ah ON bs.bsc_owner = ah.user_id
                            LEFT JOIN approvals a ON ah.approval_id = a.id
                            WHERE i.pillar_id = :pillar_id AND ((bs.country = '.$mycountry.' AND bs.division = '.$my_division_id.' AND bs.department = '.$my_department.'
                             AND bs.unit = '.$my_unit.' AND bs.section = '.$my_section.' AND a.approval_level = 8) OR (i.type = 2 AND i.created_by = '.$this_user.'))
                        ';
            }
            elseif($my_section = "" && $my_unit != "") {
                $query = 'SELECT i.id, i.group_initiative 
                            FROM strategy_group_initiatives i
                            LEFT JOIN strategy_individual_initiatives_bsc s ON i.id = s.initiative_id
                            LEFT JOIN strategy_individual_bsc bs ON s.individual_bsc_id = bs.id
                            LEFT JOIN approval_hierarchy ah ON bs.bsc_owner = ah.user_id
                            LEFT JOIN approvals a ON ah.approval_id = a.id
                            WHERE i.pillar_id = :pillar_id AND ((bs.country = '.$mycountry.' AND bs.division = '.$my_division_id.' AND bs.department = '.$my_department.'
                             AND bs.unit = '.$my_unit.' AND a.approval_level = 7) OR (i.type = 2 AND i.created_by = '.$this_user.'))
                        ';
            }
            elseif($my_section = "" && $my_unit == "" && $my_department != "") {
                $query = 'SELECT i.id, i.group_initiative 
                            FROM strategy_group_initiatives i
                            LEFT JOIN strategy_individual_initiatives_bsc s ON i.id = s.initiative_id
                            LEFT JOIN strategy_individual_bsc bs ON s.individual_bsc_id = bs.id
                            LEFT JOIN approval_hierarchy ah ON bs.bsc_owner = ah.user_id
                            LEFT JOIN approvals a ON ah.approval_id = a.id
                            WHERE i.pillar_id = :pillar_id AND ((bs.country = '.$mycountry.' AND bs.division = '.$my_division_id.' AND bs.department = '.$my_department.'
                             AND a.approval_level = 6) OR (i.type = 2 AND i.created_by = '.$this_user.'))
                        ';
            }
        }

        function initiatives_list($db, $pillar_id, $query){

            //Prepare statement
            $stmt = $db->prepare($query);
            $stmt->bindParam(':pillar_id', $pillar_id);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $id,
                    'group_initiative' => htmlspecialchars_decode($group_initiative),
                );
            }

            return $data;
        }

        //query
        $output = initiatives_list($db, $pillar_id, $query);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>