<?php

    use PHPMailer\PHPMailer\PHPMailer;  
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');

    include_once '../../../../include/Database.php';
    include_once '../../models/User.php';
    include '../../../../vendor/autoload.php';        

    

    //Instantiate DB and Connect
    $database = new Database();
           
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
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

        $errorMsg = '';         

        if (empty($data->country) || $data->country == "") {  
            $errorMsg = "Country is required";  
        } else {  
            $country = clean_data($data->country); 
        }
        
        if (empty($data->email) || $data->email == "") {  
            $errorMsg = "Email is required";  
        } else {  
            if(!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg = "Invalid Email Addresss.";
            } else {
                $user_email = clean_data($data->email); 
            } 
        }
        

        //Get user
        $datas = $user->login_by_email($country,$user_email);

        if (!$datas) {
            echo json_encode([
                'success' => false,
                'message' => 'Unknown Email Address',
            ]);
            die();
        }

        foreach ($datas as $row) {
            $userId = $row['userId'];
            $name = $row['name'];
            $surname = $row['surname'];
            $email = $row['email'];
            $forgot_pw = $row['forgot_password_date'];
            
            if( $userId == 1 ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized Reset.',
                ]);
            }
            elseif( ( $forgot_pw != null ) && ( date('Ymd') == date('Ymd', strtotime($forgot_pw)) ) ) {
                echo json_encode([
                    'success' => false,
                    'message' => 'You have already reset your password today. Please Contact the Admin.',
                ]);
            } elseif ( $row['status'] !== 'active') {
                echo json_encode([
                    'success' => false,
                    'message' => 'You are not allowed. Please contact the admin',
                ]);
            } else {
                
                function randomPassword()
                {
                    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                    $pass = array();
                    $alphaLength = strlen($alphabet) - 1;
                    for( $i = 0; $i < 8; $i++ )
                    {
                        $n = rand( 0, $alphaLength );
                        $pass[] = $alphabet[$n];
                    }
                    return implode($pass);
                }
                $passwordOwner = randomPassword();

                $passcode = $passwordOwner;
				$newPassword = password_hash($passcode, PASSWORD_DEFAULT);

                if($errorMsg == '') {
                    //update user
                   $response = $user->forgot_password($newPassword, $userId);

                    if($response ) {

                        $mail = new PHPMailer(TRUE);
                        //Server settings
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                   
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.office365.com';                    // Set the SMTP server to send through
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = 'noreply@smartapplicationsgroup.com';                     // SMTP username
                        $mail->Password   = 'Qaj88432';                               // SMTP password
                        $mail->SMTPSecure = 'tls';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                        $mail->Port       = 587;   

                        //Recipients
                        $mail->setFrom('noreply@smartapplicationsgroup.com', 'SERIS');
                        
                        $mail->addAddress($email, $name);               // Name is optional
                        // $mail->addCC($email_cc_to, $cc_name);
                        $mail->addReplyTo('noreply@smartapplicationsgroup.com', 'SERIS');
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = 'SERIS - PASSWORD RESET';
                        
                        $mail->Body = '<html><head>';
                        $mail->Body .=	'<title>SERIS - PASSWORD RESET.</title>';
                        $mail->Body .=	'</head>';
                        $mail->Body .=	' <body style="font-size:14px;">';
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$name.',</p><br/>';									
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You have reset your SERIS account password.</p><br/>';
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Your new password is <b style="color:#b01c2e; background:#fff2f2;">'.$passcode.'</b></p><br/>';		
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
                        $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
                        $mail->Body .= "</body></html>";
                        
                        $mail->send();
                            
                        echo json_encode(
                            array(
                                "success" => true,
                                "message" => "Your Password has been reset. Check your Email"
                            )
                        );
                        
                    } else {
                        echo json_encode(
                            array(
                                "success" => false,
                                "message" => 'Failed'
                            )
                        );
                    }

                } else {
                    echo json_encode(
                        array(
                            "success" => false,
                            "message" => $errorMsg
                        )
                    );
                }
            }
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }