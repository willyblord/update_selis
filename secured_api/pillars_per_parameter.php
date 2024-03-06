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
        
        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }  
        //Get ID
        $paramter_id = clean_data($_GET['id']);

        function pillars_list($db, $paramter_id){

            $query = 'SELECT p.id AS pillar_id, s.pillar_name
                        FROM strategy_pillars_group_level p 
                        LEFT JOIN strategies st ON p.strategy_id = st.id
                        LEFT JOIN strategy_pillars s ON p.strategy_pillar = s.id
                        LEFT JOIN strategy_individual_bsc_param sp ON s.bsc_parameter_id = sp.bsc_parameter_id
                        LEFT JOIN strategy_individual_bsc ib ON sp.individual_bsc_id = ib.id AND ib.group_strategy_id = p.strategy_id
                        WHERE sp.id = :paramter_id AND st.`status` = "active"
                    ';

            //Prepare statement
            $stmt = $db->prepare($query);
            $stmt->bindParam(':paramter_id', $paramter_id);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $pillar_id,
                    'pillar_name' => htmlspecialchars_decode($pillar_name),
                );
            }

            return $data;
        }

        //query
        $output = pillars_list($db, $paramter_id);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>