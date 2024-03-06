<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


function sessionStart($lifetime, $path, $domain, $secure, $httpOnly)
{
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

    $db = $database->connect();

    //Instantiate user object
    $user = new User($db);

    //get raw posted data
    $data = json_decode(urldecode(file_get_contents("php://input")));

    function clean_data($data)
    {
        $data = trim($data);
        $data = strip_tags($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $country = clean_data($data->country);
    $username = clean_data($data->username);
    $user_password = clean_data($data->password);

    if ($username === "parfaitluc" || $username === "admin.admin") {

        if (!isset($_SESSION['attempt'])) {
            $_SESSION['attempt'] = 0;
        }

        //Get user
        $datas = $user->login($country, $username);

        if (!$datas) {
            echo json_encode([
                'success' => false,
                'message' => 'Unsupported Credentials',
            ]);
            die();
        }

        foreach ($datas as $row) {
            $userId = $row['userId'];
            $name = $row['name'];
            $surname = $row['surname'];
            $country = $row['country'];
            $email = $row['email'];

            if ($row['status'] !== 'active') {
                echo json_encode([
                    'success' => false,
                    'message' => 'You are not allowed. Please contact the admin',
                ]);
            } elseif (!password_verify($user_password, $row['password'])) {

                $_SESSION['attempt'] += 1;

                if ($_SESSION['attempt'] == 2) {

                    echo json_encode([
                        'success' => false,
                        'message' => 'You have tried and failed 2 times. The 3rd time, your account will be deactivated.',
                    ]);
                    die();
                }

                if ($_SESSION['attempt'] == 3) {

                    $user->status = "inactive";
                    $user->userId = $userId;
                    $user->this_user = $userId;

                    if ($user->deactivate()) {

                        unset($_SESSION['attempt']);
                        $_SESSION = array();
                        session_regenerate_id();
                        session_destroy();

                        echo json_encode([
                            'success' => false,
                            'message' => 'You have tried and failed 3 times and your account has been deactivated. Contact the Admin.',
                        ]);
                        die();
                    }
                }

                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid Credentials',
                ]);
                die();
            } else {

                unset($_SESSION['attempt']);
                $_SESSION['count'] = 3;
                $_SESSION['login_disclaimer_confirmation'] = $userId;
                $OTP = rand(100000, 999999);

                $user->user_login_details($userId);

                // THIS TO REPLACE LOGIN EMAIL
                $user->update_OTP($OTP, $userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Login Successfull',
                ]);

                // if($user->update_OTP($OTP,$userId)) {

                //     $mail = new PHPMailer(TRUE);
                //     //Server settings
                //     // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                   
                //     $mail->isSMTP();
                //     $mail->Host       = 'smtp.office365.com';                    // Set the SMTP server to send through
                //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                //     $mail->Username   = 'noreply@smartapplicationsgroup.com';                     // SMTP username
                //     $mail->Password   = 'Qaj88432';                               // SMTP password
                //     $mail->SMTPSecure = 'tls';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
                //     $mail->Port       = 587;   

                //     //Recipients
                //     $mail->setFrom('noreply@smartapplicationsgroup.com', 'SERIS');

                //     $mail->addAddress($email, $name);               // Name is optional
                //     // $mail->addCC($email_cc_to, $cc_name);
                //     $mail->addReplyTo('noreply@smartapplicationsgroup.com', 'SERIS');
                //     $mail->isHTML(true);                                  // Set email format to HTML
                //     $mail->Subject = 'SERIS - LOGIN OTP';

                //     $mail->Body = '<html><head>';
                //     $mail->Body .=	'<title>SERIS - LOGIN OTP.</title>';
                //     $mail->Body .=	'</head>';
                //     $mail->Body .=	' <body style="font-size:14px;">';
                //     $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$name.',</p><br/>';									
                //     $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Your login OTP is <b style="color:#b01c2e;">'.$OTP.'</b>. It will be active for the next 5 minutes.</p><br/>';		
                //     $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
                //     $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
                //     $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
                //     $mail->Body .= "</body></html>";

                //     if($mail->send()) {
                //         echo json_encode([
                //             'success' => true,
                //             'message' => 'Login Successfull',
                //         ]);
                //     } else {
                //         unset($_SESSION['attempt'],$_SESSION['count'],$_SESSION['login_disclaimer_confirmation']);
                //         $_SESSION=array();
                //         session_regenerate_id(); 
                //         session_destroy();

                //         echo json_encode([
                //             'success' => false,  
                //             'message' => 'Unable to send the OTP. Check your internet and try again.',
                //         ]);
                //         die();
                //     }

                // } else {
                //     echo json_encode([
                //         'success' => false,
                //         'message' => 'OTP generation failed',
                //     ]);
                // }
            }
        }
    } else {
        // LDAP server details
        $ldapServer = "ldap://192.180.3.98";
        $ldapPort = 389;
        // $ldapUser = "jeanluc.niyigena";
        // $ldapPass = "Kigali@2020#";
        $ldapDomain = "smart.local";
        $ldapSearchBase = "dc=smart,dc=local";

        // Get user input from the login form
        $ldapUser = $username;
        $ldapPass = $user_password;

        // // Append the domain name to the username
        $fullUsername = "$ldapUser@$ldapDomain";

        // Connect to the LDAP server
        $ldapConn = ldap_connect($ldapServer, $ldapPort);

        if (!$ldapConn) {
            echo json_encode([
                'success' => false,
                'message' => 'Could not connect to LDAP server.',
            ]);
            die();
        } else {

            // Set LDAP options
            ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0);

            // Bind to the LDAP server with given credentials
            $ldapBind = @ldap_bind($ldapConn, "$ldapUser@$ldapDomain", $ldapPass);

            // Check if the binding was successful
            if (!$ldapBind) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User authentication failed.',
                ]);
                die();
            } else {

                // Search for the user entry in the LDAP server
                $userSearch = ldap_search($ldapConn, $ldapSearchBase, "(userPrincipalName=$fullUsername)");
                $userEntry = ldap_first_entry($ldapConn, $userSearch);
                $userDN = ldap_get_dn($ldapConn, $userEntry);

                // Get the user attributes from the LDAP server
                $userAttrs = ldap_get_attributes($ldapConn, $userEntry);

                // // Display some user attributes
                // echo "User DN: $userDN<br>";
                // echo "User Name: " . $userAttrs["displayName"][0] . "<br>";
                // echo "User Email: " . $userAttrs["mail"][0] . "<br>";

                $my_full_name = $userAttrs["displayName"][0];

                function extractNames($fullName)
                {
                    $nameParts = explode(' ', $fullName);

                    $firstName = $nameParts[0];

                    if (count($nameParts) == 2) {
                        $lastName = $nameParts[1];
                    } elseif (count($nameParts) >= 3) {
                        $firstName = $nameParts[0] . ' ' . $nameParts[1];
                        $lastName = $nameParts[2];
                    } else {
                        $lastName = 'Unknown';
                    }

                    return ['name' => $firstName, 'surname' => $lastName];
                }
                $get_name = extractNames($my_full_name);

                $my_name = $get_name['name'];
                $my_surname = $get_name['surname'];

                $my_email = $userAttrs["mail"][0];

                $user->email = $my_email;
                if ($user->is_email_exists()) {

                    //Get user
                    $datas = $user->login_by_email($country, $my_email);

                    if (!$datas) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Login failed! Please try using the correct country',
                        ]);
                        die();
                    }

                    foreach ($datas as $row) {
                        $userId = $row['userId'];
                        $name = $row['name'];
                        $surname = $row['surname'];
                        $country = $row['country'];
                        $email = $row['email'];

                        if ($row['status'] !== 'active') {
                            echo json_encode([
                                'success' => false,
                                'message' => 'You are not allowed. Please contact the system admin',
                            ]);
                        } else {

                            $_SESSION['count'] = 3;
                            $_SESSION['login_disclaimer_confirmation'] = $userId;
                            $OTP = rand(100000, 999999);

                            $user->user_login_details($userId);

                            if ($user->update_OTP($OTP, $userId)) {

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
                                $mail->Subject = 'SERIS - LOGIN OTP';

                                $mail->Body = '<html><head>';
                                $mail->Body .=    '<title>SERIS - LOGIN OTP.</title>';
                                $mail->Body .=    '</head>';
                                $mail->Body .=    ' <body style="font-size:14px;">';
                                $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear ' . $name . ',</p><br/>';
                                $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Your login OTP is <b style="color:#b01c2e;">' . $OTP . '</b>. It will be active for the next 5 minutes.</p><br/>';
                                $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
                                $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
                                $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
                                $mail->Body .= "</body></html>";

                                if ($mail->send()) {
                                    echo json_encode([
                                        'success' => true,
                                        'message' => 'Login Successfull',
                                    ]);
                                } else {
                                    unset($_SESSION['login_disclaimer_confirmation']);
                                    $_SESSION = array();
                                    session_regenerate_id();
                                    session_destroy();

                                    echo json_encode([
                                        'success' => false,
                                        'message' => 'Unable to send the OTP. Check your internet and try again.',
                                    ]);
                                    die();
                                }
                            } else {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'OTP generation failed',
                                ]);
                            }
                        }
                    }
                } else {

                    $user->name = $my_name;
                    $user->surname = $my_surname;
                    $user->country_id = $country;
                    $user->email = $my_email;
                    $user->username = $username;

                    function random_password()
                    {
                        $random_characters = 2;

                        $lower_case = "abcdefghijklmnopqrstuvwxyz";
                        $upper_case = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $numbers = "1234567890";
                        $symbols = "!@#$%^&*";

                        $lower_case = str_shuffle($lower_case);
                        $upper_case = str_shuffle($upper_case);
                        $numbers = str_shuffle($numbers);
                        $symbols = str_shuffle($symbols);

                        $random_password = substr($lower_case, 0, $random_characters);
                        $random_password .= substr($upper_case, 0, $random_characters);
                        $random_password .= substr($numbers, 0, $random_characters);
                        $random_password .= substr($symbols, 0, $random_characters);

                        return  str_shuffle($random_password);
                    }
                    $passcode = random_password();
                    $password = password_hash($passcode, PASSWORD_DEFAULT);
                    $user->password = $password;

                    $user->status = "active";

                    $userId = $user->auto_create();

                    if ($userId) {
                        $_SESSION['count'] = 3;
                        $_SESSION['login_disclaimer_confirmation'] = $userId;
                        $OTP = rand(100000, 999999);

                        $user->user_login_details($userId);

                        if ($user->update_OTP($OTP, $userId)) {

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

                            $mail->addAddress($my_email, $my_name);               // Name is optional
                            // $mail->addCC($email_cc_to, $cc_name);
                            $mail->addReplyTo('noreply@smartapplicationsgroup.com', 'SERIS');
                            $mail->isHTML(true);                                  // Set email format to HTML
                            $mail->Subject = 'SERIS - LOGIN OTP';

                            $mail->Body = '<html><head>';
                            $mail->Body .=    '<title>SERIS - LOGIN OTP.</title>';
                            $mail->Body .=    '</head>';
                            $mail->Body .=    ' <body style="font-size:14px;">';
                            $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear ' . $my_name . ',</p><br/>';
                            $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Your login OTP is <b style="color:#b01c2e;">' . $OTP . '</b>. It will be active for the next 5 minutes.</p><br/>';
                            $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
                            $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
                            $mail->Body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
                            $mail->Body .= "</body></html>";

                            if ($mail->send()) {
                                echo json_encode([
                                    'success' => true,
                                    'message' => 'Login Successfull',
                                ]);
                            } else {
                                unset($_SESSION['login_disclaimer_confirmation']);
                                $_SESSION = array();
                                session_regenerate_id();
                                session_destroy();

                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Unable to send the OTP. Check your internet and try again.',
                                ]);
                                die();
                            }
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'OTP generation failed',
                            ]);
                        }
                    }
                }
            }
        }

        // Close the LDAP connection
        ldap_close($ldapConn);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Access Denied',
    ]);
}