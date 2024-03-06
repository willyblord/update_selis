<?php

    function sessionStart($lifetime, $path, $domain, $secure, $httpOnly) {	
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        session_start();
    }  
    sessionStart(0, '/', 'localhost', true, true);

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../../include/Database.php';
    include_once '../../models/User.php';
    include '../../../../vendor/autoload.php';
        
    use \Firebase\JWT\JWT;

    //Instantiate DB and Connect
    $database = new Database();
           
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {        

        if ( isset($_SESSION['login_disclaimer_confirmation']) && isset($_SESSION['count']) ) {

            $db = $database->connect();

            //Instantiate user object
            $user = new User($db);

            //get raw posted data
            $data = json_decode(urldecode(file_get_contents("php://input")));

            function clean_data($data) {  
                $data = trim($data);  
                $data = strip_tags($data);  
                $data = stripslashes($data);
                $data = htmlspecialchars($data);  
                return $data;  
            }
            
            $userId = $_SESSION['login_disclaimer_confirmation']; 

            $datas = $user->user_auth_details($userId);
            if (!$datas) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unsupported Credentials',
                ]);
                die();
            }

            $input_otp = clean_data($data->OTP); 

            foreach ($datas as $row) {

                $userId = $row['userId'];
                $name = $row['name'];
                $surname = $row['surname'];
                $country = $row['country'];
                $OTP = $row['OTP'];
                $OTP_created_at = $row['OTP_created_at'];
                
                $login_otp_duration = 300; 
	            $current_time = time(); 
                $createdat_otp = strtotime($OTP_created_at);

                
                if($input_otp != $OTP){

                    $_SESSION['count'] = $_SESSION['count'] - 1;

                    if($_SESSION['count'] == 1) {

                        echo json_encode([
                            'success' => false,                       
                            'redirect' => false,
                            'message' => 'You have tried and failed 2 times. The 3rd time, your account will be deactivated.',
                        ]);
                        die();
                    }

                    if($_SESSION['count'] == 0) {

                        $user->status = "inactive";
                        $user->userId = $userId;
                        $user->this_user = $userId;

                        if($user->deactivate()) {
                            
                            unset($_SESSION['login_disclaimer_confirmation'],$_SESSION['count']);
                            $_SESSION=array();
                            session_regenerate_id(); 
                            session_destroy();

                        
                            echo json_encode([
                                'success' => false,                       
                                'redirect' => true,
                                'message' => 'You have tried and failed 3 times and your account has been deactivated. Contact the Admin.',
                            ]);
                            die();
                        }
                    }

                    echo json_encode([
                        'success' => false,                        
                        'redirect' => false,
                        'message' => 'Incorrect OTP!',
                    ]);
                    die();
                } 
                
                if ( ( $current_time - $createdat_otp) > $login_otp_duration ) {

                    unset($_SESSION['login_disclaimer_confirmation'],$_SESSION['count']);
                    $_SESSION=array();
                    session_regenerate_id(); 
                    session_destroy();

                    echo json_encode([
                        'success' => false,                     
                        'redirect' => true,
                        'message' => 'OTP Expired! You are being redirected to Login page.',
                    ]);
                    die();
                }
                
                $now = strtotime("now");

                $payload = [
                    'iss' => "localhost",
                    'aud' => 'localhost',
                    'exp' => $now + 3000, //5 minutes
                    'data' => [
                        'userId' => $userId,
                        'name' => $name,
                        'surname' => $surname,
                        'country' => $country,
                        'pin' => $OTP,
                    ],
                ];

                $payload2 = [
                    'iss' => "localhost",
                    'aud' => 'localhost',
                    'exp' => $now + 9000, //15 minutes
                    'data' => [
                        'userId' => $userId,
                        'name' => $name,
                        'surname' => $surname,
                        'country' => $country,
                        'pin' => $OTP,
                    ],
                ];

                $jwt = JWT::encode($payload, JWT_SECRET_KEY, JWT_ALGO);
                $jwt2 = JWT::encode($payload2, JWT_SECRET_KEY, JWT_ALGO);

                setcookie("jwt", $jwt, 0, "/","",false,true);
                setcookie("jwt_r", $jwt2, 0, "/","",false,true);


                unset($_SESSION['login_disclaimer_confirmation'],$_SESSION['count']);
                $_SESSION=array();
                session_regenerate_id(); 

                if(session_destroy()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login Successfull',
                    ]);
                }
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Access Denied',
            ]);
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }