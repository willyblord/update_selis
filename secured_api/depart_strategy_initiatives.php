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
        $country = clean_data($_GET['country']);
        $department = clean_data($_GET['department']);
        $year = clean_data($_GET['year']);

        function initiatives_list($db, $country, $department, $year){

            $query = 'SELECT i.id AS init_id, i.initiative 
                        FROM strategy_initiatives i
                        LEFT JOIN strategy_country_level c ON i.country_strategy_id = c.id
                        WHERE c.status = "approved" AND c.country = :country AND c.department = :department AND c.year = :strat_year
                    ';

            //Prepare statement
            $stmt = $db->prepare($query);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':department', $department);
            $stmt->bindParam(':strat_year', $year);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $init_id,
                    'initiative' => htmlspecialchars_decode($initiative),
                );
            }

            return $data;
        }

        //query
        $output = initiatives_list($db, $country, $department, $year);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>