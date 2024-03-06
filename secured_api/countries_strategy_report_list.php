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
        $user_details = $user->validate($_COOKIE["jwt"]);
        if ($user_details === false) {
            setcookie("jwt", null, -1, '/');
            setcookie("jwt_r", null, -1, '/');
            echo json_encode([
                'success' => false,
                'message' => $user->error
            ]);
            die();
        }
        if ($user_details['can_be_super_user'] != 1 && $user_details['can_view_cash_reports'] != 1) {
            echo json_encode([
                'success' => false,
                'message' => "Unauthorized Resource"
            ]);
            die();
        }

        $my_country = $user_details['country'];

        
        $all = false;
        if( ($user_details['can_view_cash_reports'] == 1) && ($user_details['can_be_cash_hod'] == 1 || $user_details['can_be_cash_finance'] == 1 || $user_details['can_be_cash_coo'] == 1) ){
            $query = 'SELECT DISTINCT c.country AS country_name, s.country AS country_id 
                        FROM countries c 
                        INNER JOIN strategy_country_level s 
                        ON c.id = s.country WHERE s.country = '.$my_country.' ';
            $all = false;
        }
        if( ($user_details['can_be_super_user'] == 1) || (($user_details['can_view_cash_reports'] == 1) && ($user_details['can_be_cash_manager'] == 1)) ){
        
            $query = 'SELECT DISTINCT c.country AS country_name, s.country AS country_id 
                        FROM countries c 
                        INNER JOIN strategy_country_level s 
                        ON c.id = s.country ';

            $all = true;
        }

        function countries_list($db, $query){

            //Prepare statement
            $stmt = $db->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $country_id,
                    'country_name' => $country_name,
                );
            }

            return $data;
        }

        //query
        $output = countries_list($db, $query);

        echo json_encode([
            'all' => $all,
            'data' => $output,            
        ]);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>