<?php

// (A) JWT COOKIE NOT SET!
if (!isset($_COOKIE["jwt"])) {
    header("Location: login");
    exit();
} else {
    include_once 'include/Database.php';
    include_once 'administration/users/models/User.php';

    $jwt = $_COOKIE["jwt"];

    require "vendor/autoload.php";
    try {
        $jwt = Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key(JWT_SECRET_KEY, JWT_ALGO));
        $valid = is_object($jwt);
    } catch (Exception $e) {
        unset($_COOKIE['jwt']);
        setcookie('jwt', null, -1, '/');
        header("Location: login");
        exit();
    }

    //Instantiate DB and Connect
    $database = new Database();
    $db = $database->connect();
    //Instantiate user object
    $user = new User($db);


    if ($valid) {
        $userDetails = $user->get_user($jwt->data->userId, $jwt->data->pin);
        $valid = is_array($userDetails);
    }

    if ($valid) {

        $row = $userDetails;
    }

    $settings_salis_details = $user->get_settings_salis($row['country_id']);
    $is_on_budget = $settings_salis_details['is_on_budget'];
}