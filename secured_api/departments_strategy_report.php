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

        $my_country = $userDetails['country_id'];
        $my_department = $userDetails['department_id'];

        $all = false;
        if( ($userDetails['can_be_super_user'] == 1) || (($userDetails['can_view_cash_reports'] == 1) && ($userDetails['can_be_cash_finance'] == 1 || $userDetails['can_be_cash_coo'] == 1 || $userDetails['can_be_cash_manager'] == 1)) ){
            $query = 'SELECT DISTINCT act.department, d.category AS dptName, d.id AS deptId
                            FROM strategy_country_level act
                            LEFT JOIN department_categories d ON act.department = d.id
                            WHERE act.country = '.$my_country.'
                        ';

            $all = true;
        }
        if($userDetails['can_view_cash_reports'] == 1 && $userDetails['can_be_cash_hod'] == 1){
        
            $query = 'SELECT DISTINCT act.department, d.category AS dptName, d.id AS deptId
                            FROM strategy_country_level act
                            LEFT JOIN department_categories d ON act.department = d.id
                            WHERE act.country = '.$my_country.' AND act.department = '.$my_department.'
                        ';

            $all = false;
        }

        function departments_list($db, $query){

            //Prepare statement
            $stmt = $db->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $deptId,
                    'department_name' => $dptName,
                );
            }

            return $data;
        }

        //query
        $output = departments_list($db, $query);

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