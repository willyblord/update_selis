<?php

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json'); 
    include '../../../../vendor/autoload.php';
    
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {        

        //get raw posted data
        $data = json_decode(urldecode(file_get_contents("php://input")));

        function clean_data($data) {  
            $data = trim($data);  
            $data = strip_tags($data);  
            $data = stripslashes($data);
            $data = htmlspecialchars($data);  
            return $data;  
        }

        if(isset($data->logout)) {

            define("JWT_SECRET_KEY", "Kh6Ya7JYUuHYScodAiRY7EulERx/NjJMAUi/GhyIPGyBp+bZ+9N3FqANdtrjcbq3wedscx/23edsxzae2JBbdH");
            define("JWT_ALGO", "HS256");

            $now = strtotime("now");
            $payload = [
                'iss' => "localhost",
                'aud' => 'localhost',
                'exp' => $now - 70000000,
                'data' => [],
            ];
            $jwt = JWT::encode($payload, JWT_SECRET_KEY, JWT_ALGO);

            setcookie("jwt", $jwt, -1, '/');
            setcookie("jwt_r", $jwt, -1, '/');
            
            setcookie("jwt", "", -1, '/');
            setcookie("jwt_r", "", -1, '/');

            unset($_COOKIE['jwt']); 
            unset($_COOKIE['jwt_r']); 


            echo json_encode([
                'success' => true,
                'message' => 'Logged out'
            ]);
            die();
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }