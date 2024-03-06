<?php 
    //Headers
    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json'); 

    include_once '../include/Database.php';   

    if ($_SERVER["REQUEST_METHOD"] == 'GET') {

        function countries_list(){

            //Instantiate SB and Connect
            $database = new Database();
            $db = $database->connect();

            //Select Query
            $query = ' SELECT id, country FROM countries ';

            //Prepare statement
            $stmt = $db->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $id,
                    'country_name' => $country,
                );
            }

            return $data;
        }

        //query
        $output = countries_list();

        echo json_encode($output);

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }
?>