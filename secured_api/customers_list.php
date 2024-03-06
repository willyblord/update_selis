<?php 
    //Headers
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');    

    include_once '../include/Database.php';  
    include_once '../administration/users/models/User.php'; 

    if ($_SERVER["REQUEST_METHOD"] == 'GET') {

        if (!isset($_COOKIE["jwt"])) {
            echo json_encode([
                'success' => false,
                'message' => 'Please Login'
            ]);
            die();
        }

        $jwt = $_COOKIE["jwt"];
        
        require "../vendor/autoload.php";
        try {
            $jwt = Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key(JWT_SECRET_KEY, JWT_ALGO));
            $valid = is_object($jwt);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            die();
        }

        function customers_list($country){

            //Instantiate SB and Connect
            $database = new Database();
            $db = $database->connect();

            //Select Query
            $query = ' SELECT id, customerName FROM customers  WHERE country = :country ';

            //Prepare statement
            $stmt = $db->prepare($query);
            $stmt->bindParam(':country', $country);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $id,
                    'customerName' => $customerName,
                );
            }

            return $data;
        }

        //query
        $output = customers_list($jwt->data->country);

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>