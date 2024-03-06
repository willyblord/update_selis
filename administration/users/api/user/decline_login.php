<?php

    function sessionStart($lifetime, $path, $domain, $secure, $httpOnly) {	
        session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        session_start();
    }  
    sessionStart(0, '/', 'localhost', true, true);

    header('Access-Control-Allow-Origin:*');
    header('Access-Control-Allow-Method:POST');
    header('Content-Type:application/json');
           
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        unset($_SESSION['login_disclaimer_confirmation']);
        $_SESSION=array();
        session_regenerate_id(); 

        if(session_destroy())
        {
            echo json_encode([
                'success' => true,
                'message' => 'Login Successfull',
            ]);
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Access Denied',
        ]);
    }