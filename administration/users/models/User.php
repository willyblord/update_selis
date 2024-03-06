<?php

    class User {
        //DB Stuff
        private $conn;
        private $table = "users";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $userId;
        public $name;
        public $surname;
        public $staffNumber;
        public $role;
        public $country;
        public $country_val;
        public $country_name;
        public $country_id;
        public $division_name;
        public $division_id;
        public $department;
        public $department_val;
        public $department_name;
        public $department_id;
        public $unit_name;
        public $unit_id;
        public $section_name;
        public $section_id;
        public $roles;
        public $email;
        public $isOnLeave;
        public $username;
        public $password;
        public $status;
        public $OTP;
        public $OTP_created_at;
        public $deactivated_by;
        public $deactivated_at;
        public $forgot_password_date;
        public $registeredBy;
        public $registerDate;
        public $editedBy;
        public $editedDate;

        //Pagination properties
        public $draw;
        public $start;
        public $rowperpage; // Rows display per page
        public $columnIndex; // Column index
        public $columnName; // Column name
        public $columnSortOrder; // asc or desc
        public $searchValue; // Search value


        //condtructor
        public function __construct($db) {
            $this->conn = $db;
        }

        public function login($country,$username){
            $query =  'SELECT * FROM '.$this->table.' WHERE country = :country AND username = :username';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);  
            } else {
                return false;
            }
        }

        public function user_auth_details($userId){
            $query =  'SELECT * FROM '.$this->table.' WHERE userId = :userId';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);  
            } else {
                return false;
            }
        }

        public function login_by_email($country,$email){
            $query =  'SELECT * FROM '.$this->table.' WHERE country = :country AND email = :email';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);  
            } else {
                return false;
            }
        }

        public function update_OTP($OTP,$userId) {
            $query =  'UPDATE ' . $this->table . ' SET OTP=:OTP, OTP_created_at = Now() WHERE userId = :userId';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':OTP', $OTP);
            $stmt->bindParam(':userId', $userId);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function user_login_details($userId) {
            $query =  'INSERT INTO user_login_details SET userId=:userId, last_login = Now(), last_activity = Now()';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId);
            if($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function get_user($userId, $OTP) {
            $query = 'SELECT u.userId, u.name, u.surname, u.email, 
                            c.id AS country_id, c.country AS country_name, d.id AS division_id, d.division_name, 
                            dep.id AS department_id, dep.department_name, un.id AS unit_id, un.unit_name, 
                            sec.id AS section_id, sec.section_name,
                            GROUP_CONCAT(DISTINCT r.role_name SEPARATOR ", ") AS roles,
                            GROUP_CONCAT(DISTINCT p.permission_name SEPARATOR ", ") AS permissions,
                            GROUP_CONCAT(DISTINCT m.module_name SEPARATOR ", ") AS modules,
                            a.approval_name AS approval,
                            h.manager_id,
                            CONCAT(um.name," ",um.surname) AS manager_name,
                            a.approval_level
                        FROM Users u
                        LEFT JOIN User_Roles ur ON u.userId = ur.user_id
                        LEFT JOIN Roles r ON ur.role_id = r.id
                        LEFT JOIN User_Permissions up ON u.userId = up.user_id
                        LEFT JOIN Permissions p ON up.permission_id = p.id
                        LEFT JOIN Module_Permissions mp ON up.permission_id = mp.permission_id
                        LEFT JOIN Modules m ON mp.module_id = m.id
                        LEFT JOIN Countries c ON u.country = c.id
                        LEFT JOIN Divisions d ON u.division_id = d.id
                        LEFT JOIN Departments dep ON u.department_id = dep.id
                        LEFT JOIN Units un ON u.unit_id = un.id
                        LEFT JOIN Sections sec ON u.section_id = sec.id              
                        LEFT JOIN approval_hierarchy h ON u.userId = h.user_id
                        LEFT JOIN Approvals a ON h.approval_id = a.id
                        LEFT JOIN Users um ON h.manager_id = um.userId
                      WHERE u.userId = :userId AND u.OTP = :OTP AND u.status = "active"
                    ';
                      
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $userId);
            $stmt->bindParam(':OTP', $OTP);
            $stmt->execute();
            
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($rows) {
                $userDetails = [];
                foreach ($rows as $row) {
                    
                    $userDetails = [
                        'userId' => $row['userId'],
                        'name' => $row['name'],
                        'surname' => $row['surname'],
                        'email' => $row['email'],
                        'country' => $row['country_name'],
                        'country_id' => $row['country_id'],
                        'division_id' => $row['division_id'],
                        'division' => $row['division_name'],
                        'department_id' => $row['department_id'],
                        'department' => $row['department_name'],
                        'unit_id' => $row['unit_id'],
                        'unit' => $row['unit_name'],
                        'section_id' => $row['section_id'],
                        'section' => $row['section_name'],
                        'roles' => $row['roles'],
                        'permissions' => $row['permissions'],
                        'modules' => $row['modules'],
                        'approval' => $row['approval'],
                        'managerId' => $row['manager_id'],
                        'managerName' => $row['manager_name'],
                        'approvalLevel' => $row['approval_level']
                    ];

                    // Query to fetch the leader of the user's division
                    $divisionLeaderQuery = 'SELECT u.userId, CONCAT(u.name," ",u.surname) AS leader_name
                                FROM Users u
                                LEFT JOIN User_Roles ur ON u.userId = ur.user_id
                                LEFT JOIN Roles r ON ur.role_id = r.id
                                WHERE r.role_name = "MAIN_FUNCTION_LEADER_ROLE"
                                AND u.country = :countryId
                                AND u.division_id = :divisionId';

                    // Prepare and execute the division leader query
                    $stmt = $this->conn->prepare($divisionLeaderQuery);
                    $stmt->bindParam(':countryId', $row['country_id']);
                    $stmt->bindParam(':divisionId', $row['division_id']);
                    $stmt->execute();
                    $divisionLeader = $stmt->fetch(PDO::FETCH_ASSOC);

                    $userDetails['divisionLeader'] = [];
                    if ($divisionLeader) {
                        $userDetails['divisionLeader'] = $divisionLeader;
                    }

                    // Query to fetch the leader of the user's department
                    $departmentLeaderQuery = 'SELECT u.userId, CONCAT(u.name," ",u.surname) AS leader_name
                                FROM Users u
                                LEFT JOIN User_Roles ur ON u.userId = ur.user_id
                                LEFT JOIN Roles r ON ur.role_id = r.id
                                WHERE r.role_name = "HOD_ROLE"
                                AND u.country = :countryId
                                AND u.division_id = :divisionId
                                AND u.department_id = :departmentId';

                    // Prepare and execute the department leader query
                    $stmt = $this->conn->prepare($departmentLeaderQuery);
                    $stmt->bindParam(':countryId', $row['country_id']);
                    $stmt->bindParam(':divisionId', $row['division_id']);
                    $stmt->bindParam(':departmentId', $row['department_id']);
                    $stmt->execute();
                    $departmentLeader = $stmt->fetch(PDO::FETCH_ASSOC);

                    $userDetails['departmentLeader'] = [];
                    if ($departmentLeader) {
                        $userDetails['departmentLeader'] = $departmentLeader;
                    }

                    // Query to fetch the leader of the user's unit
                    $unitLeaderQuery = 'SELECT u.userId, CONCAT(u.name," ",u.surname) AS leader_name
                            FROM Users u
                            LEFT JOIN User_Roles ur ON u.userId = ur.user_id
                            LEFT JOIN Roles r ON ur.role_id = r.id
                            WHERE r.role_name = "UNIT_LEADER_ROLE"
                            AND u.country = :countryId
                            AND u.division_id = :divisionId
                            AND u.department_id = :departmentId
                            AND u.unit_id = :unitId';

                    // Prepare and execute the unit leader query
                    $stmt = $this->conn->prepare($unitLeaderQuery);
                    $stmt->bindParam(':countryId', $row['country_id']);
                    $stmt->bindParam(':divisionId', $row['division_id']);
                    $stmt->bindParam(':departmentId', $row['department_id']);
                    $stmt->bindParam(':unitId', $row['unit_id']);
                    $stmt->execute();
                    $unitLeader = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $userDetails['unitLeader'] = [];
                    if ($unitLeader) {
                        $userDetails['unitLeader'] = $unitLeader;
                    }

                    // Query to fetch the leader of the user's section
                    $sectionLeaderQuery = 'SELECT u.userId, CONCAT(u.name," ",u.surname) AS leader_name
                            FROM Users u
                            LEFT JOIN User_Roles ur ON u.userId = ur.user_id
                            LEFT JOIN Roles r ON ur.role_id = r.id
                            WHERE r.role_name = "SECTION_LEADER_ROLE"
                            AND u.country = :countryId
                            AND u.division_id = :divisionId
                            AND u.department_id = :departmentId
                            AND u.unit_id = :unitId
                            AND u.section_id = :sectionId';

                    // Prepare and execute the section leader query
                    $stmt = $this->conn->prepare($sectionLeaderQuery);
                    $stmt->bindParam(':countryId', $row['country_id']);
                    $stmt->bindParam(':divisionId', $row['division_id']);
                    $stmt->bindParam(':departmentId', $row['department_id']);
                    $stmt->bindParam(':unitId', $row['unit_id']);
                    $stmt->bindParam(':sectionId', $row['section_id']);
                    $stmt->execute();
                    $sectionLeader = $stmt->fetch(PDO::FETCH_ASSOC);

                    $userDetails['sectionLeader'] = [];
                    if ($sectionLeader) {
                        $userDetails['sectionLeader'] = $sectionLeader;
                    }
                }
                return $userDetails;
                
            } else {
                return false;
            }
        }
        

        public function validate ($jwt) {
            // (G1) "UNPACK" ENCODED JWT
            // require "../../../vendor/autoload.php";
            require_once(dirname(__FILE__) . "../../../../vendor/autoload.php");
            try {
                $jwt = Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key(JWT_SECRET_KEY, JWT_ALGO));
                $valid = is_object($jwt);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
         
            // (G2) GET USER
            $now = strtotime("now");
            $remaining_time = $jwt->exp - $now;
            if ( ($valid) && ($remaining_time > 0) ) {
                $result = $this->get_user($jwt->data->userId,$jwt->data->pin);
                $valid = is_array($result);
            }
         
            // (G3) RETURN RESULT
            if ($valid) {

                return $result;

            } else {
              $this->error = "Invalid Identity";
              return false;
            }
        }

        // Function to perform permission check
        function hasPermission($userDetails, $requiredRoles = [], $requiredPermissions = [], $requiredModules = '') {
            // Check if user has SUPER_USER_ROLE
            if (in_array('SUPER_USER_ROLE', explode(', ', $userDetails['roles']))) {
                return true;
            }

            // Role check
            $userRoles = explode(', ', $userDetails['roles']);
            $roleCheck = array_intersect($userRoles, $requiredRoles);
            if (empty($roleCheck)) {
                return false;
            }

            // Permission check
            if (!empty($requiredPermissions)) {
                $userPermissions = explode(', ', $userDetails['permissions']);
                $permissionCheck = array_intersect($userPermissions, $requiredPermissions);
                if (empty($permissionCheck)) {
                    return false;
                }
            }

            // Module check
            if (!empty($requiredModules)) {
                $userModules = explode(', ', $userDetails['modules']);
                if (!in_array($requiredModules, $userModules)) {
                    return false;
                }
            }

            // All checks pass, user is authorized
            return true;
        }

        function check_expiration ($jwt) {
            // (G1) "UNPACK" ENCODED JWT
            require "../../../../vendor/autoload.php";
            try {
                $jwt = Firebase\JWT\JWT::decode($jwt, new Firebase\JWT\Key(JWT_SECRET_KEY, JWT_ALGO));
                $valid = is_object($jwt);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }         
            // (G2) GET USER
            if ($valid) {
                $now = strtotime("now");
                $expiration = $jwt->exp;
                $remaining_time = $expiration - $now ;

                if ( !$this->get_user($jwt->data->userId,$jwt->data->pin) ) {
                    return -1;
                }
                return $remaining_time;

            } else {
                $this->error = "Invalid Identity";
                return false;
            }
        }

        function refresh_token ($jwt_r) {
            // (G1) "UNPACK" ENCODED JWT
            require "../../../../vendor/autoload.php";
            try {
                $jwt2 = Firebase\JWT\JWT::decode($jwt_r, new Firebase\JWT\Key(JWT_SECRET_KEY, JWT_ALGO));
                $valid2 = is_object($jwt2);
            } catch (Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }         
            // (G2) GET USER
            $now = strtotime("now");
            $remaining_time = $jwt2->exp - $now;
            if ( ($valid2) && ($remaining_time > 0) ) {
                
                $jwt = $jwt_r;
                setcookie("jwt", $jwt, null, "/","",false,true);

                return true;
                
            } else {
                $this->error = "Invalid Identity";
                return false;
            }
        }

        public function get_settings_salis($country)
        {
            $statement = $this->conn->prepare('SELECT * FROM settings_salis WHERE country = :country ');
            $statement->bindParam(':country', $country);
            $statement->execute();
            $result = $statement->fetchAll();
            foreach($result as $row)
            {
                return array(
                    'pettycash_finance_limit'	=> $row["pettycash_finance_limit"],
                    'pettycash_coo_limit'		=> $row["pettycash_coo_limit"],
                    'is_on_budget'				=> $row["is_on_budget"]
                );
            }
        }


        public function get_country($country)
        {
            $statement = $this->conn->prepare('SELECT * FROM countries WHERE id = :country ');
            $statement->bindParam(':country', $country);
            $statement->execute();
            $result = $statement->fetchAll();
            foreach($result as $row)
            {
                return array(
                    'country_name'		=> $row["country"],
                    'country_code'		=> $row["country_code"],			
                    'country_currency'	=> $row["currency"]
                );
            }
        }


        //Get Ideas
        public function read_users(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" OR 
                    u.email LIKE "%'.$this->searchValue.'%" OR 
                    u.staffNumber LIKE "%'.$this->searchValue.'%" OR 
                    c.country LIKE "%'.$this->searchValue.'%" OR 
                    u.status LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'u.userId DESC';
            if(isset($this->columnIndex) && !empty($this->columnIndex)){
                $order = ' '.$this->columnIndex.' '.$this->columnSortOrder.' ';
            }

            //limit
            $limit = '';
            if(isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)){
                $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
            }

            //Select Count
            $sql = 'SELECT count(*) 
                FROM 
                    '.$this->table.' u 
                LEFT JOIN 
                    users u1 ON u.registeredBy = u1.userId
                LEFT JOIN 
                    users u2 ON u.editedBy = u2.userId
                LEFT JOIN countries c ON u.country = c.id 
                LEFT JOIN divisions dv ON u.division_id = dv.id 
                LEFT JOIN departments dp ON u.department_id = dp.id 
                LEFT JOIN units un ON u.unit_id = un.id
                LEFT JOIN sections sc ON u.section_id = sc.id
                WHERE u.status NOT IN ("deleted") AND u.userId != 0 
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        u.*,
                        u1.name AS created_by_name, 
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name, 
                        u2.surname AS updated_by_surname,
                        c.country AS country_name, dv.division_name,
                        dp.department_name, un.unit_name, sc.section_name
                    FROM
                        '.$this->table.' u
                    LEFT JOIN 
                        users u1 ON u.registeredBy = u1.userId
                    LEFT JOIN 
                        users u2 ON u.editedBy = u2.userId
                    LEFT JOIN countries c ON u.country = c.id 
                    LEFT JOIN divisions dv ON u.division_id = dv.id 
                    LEFT JOIN departments dp ON u.department_id = dp.id 
                    LEFT JOIN units un ON u.unit_id = un.id
                    LEFT JOIN sections sc ON u.section_id = sc.id
                    WHERE u.status NOT IN ("deleted") AND u.userId != 0 
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'
            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            //Execute Query
            $stmt->execute();


            //RowCount
            $num = $stmt->rowCount();

            $output = array();
            $data = array();

            //Check if any Idea
            if($num < 1) {

                $output = array(
                    "success" => false,
                    "message" => "Users Not Found",
                    "draw"				=>	intval($this->draw),
                    "recordsTotal"		=> 	$num,
                    "recordsFiltered"	=>	$number_of_rows,
                    "data"			    =>	[]
                );
                return $output;

            }

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $view = $create = $rights = $activate  = $reset = $delete = $onleave = ''; 

                   
                $view = '<li id="'.$userId.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                $create = '<li id="'.$userId.'" class="update"><a href="#" class="dropdown-item"><i class="mdi mdi-pencil font-size-16 text-info me-1 "></i> Edit</a></li>';
                $rights = '<li id="'.$userId.'" class="rights"><a href="#" class="dropdown-item"><i class="fas fa-user-cog font-size-16 text-info me-1 "></i> Rights</a></li>';
                $reset = '<li id="'.$userId.'" class="reset_password"><a href="#" class="dropdown-item"><i class="fas fa-user-lock font-size-16 text-info me-1 "></i> Reset Password</a></li>';

                $activate = '<li id="'.$userId.'" class="deactivate"><a href="#" class="dropdown-item"><i class="fas fa-power-off font-size-16 text-warning me-1 "></i> Deactivate</a></li>';
                if($status == "inactive" ){
                    $activate = '<li id="'.$userId.'" class="activate"><a href="#" class="dropdown-item"><i class="fas fa-check font-size-16 text-success me-1 "></i> Activate</a></li>';
                }
                $delete = '<li id="'.$userId.'" class="delete"><a href="#" class="dropdown-item"><i class="mdi mdi-trash-can font-size-16 text-danger me-1"></i> Delete</a></li>';
                // $onleave = '<li id="'.$userId.'" class="onleave"><a href="#" class="dropdown-item"><i class="mdi mdi-account-off-outline font-size-16 text-info me-1"></i> On Leave</a></li>';

                if($status == 'active') {
                    $status = '<span class="badge bg-success">'.$status.'</span>';
                } elseif($status == 'inactive') {
                    $status = '<span class="badge bg-danger">'.$status.'</span>';
                } elseif($status == 'deleted') {
                    $status = '<span class="badge bg-primary">'.$status.'</span>';
                }

                $data[] = array(
                    'userId' => $userId,
                    'staffNumber' => $staffNumber ? $staffNumber : 'N/A',
                    'names' => $name.' '.$surname,
                    'country_name' => $country_name,
                    'division_name' => $division_name ? $division_name : 'N/A',
                    'department_name' => $department_name ? $department_name : 'N/A',
                    'unit_name' => $unit_name ? $unit_name : 'N/A',
                    'section_name' => $section_name ? $section_name : 'N/A',
                    'status' => $status,
                    'registeredBy' => $registeredBy ? $created_by_surname . ' ' . $created_by_name : 'N/A' ,
                    'registerDate' => date("F j, Y g:i a", strtotime($registerDate)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'                                
                                '.$create.'                             
                                '.$rights.'                       
                                '.$reset.'
                                '.$activate .'
                                '.$onleave .'
                                '.$delete.'
                            </ul>
                        </div>
                   '
                );
            }


            $output = array(
                "success"      => true,
                "message"       => "Users Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        
        public function is_email_exists($and = ''){
            $query =  'SELECT email FROM ' . $this->table . ' WHERE email = :email '.$and.' ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $this->email);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }            
        }

        public function is_username_exists($and = '') {
            $query =  'SELECT username FROM ' . $this->table . ' WHERE username = :username '.$and.' ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $this->username);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function is_user_exists() {
            $query =  'SELECT userId FROM ' . $this->table . ' WHERE userId = :userId';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $this->userId);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function is_privilege_exists(){
            $query =  'SELECT id FROM privileges WHERE userId = :userId';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $this->userId);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }


        //Get Single Idea
        public function read_single() {
            //create query
            $query =  'SELECT 
                            u.*,
                            u1.name AS created_by_name, 
                            u1.surname AS created_by_surname,
                            u2.name AS updated_by_name, 
                            u2.surname AS updated_by_surname,
                            u3.name AS deactivated_by_name, 
                            u3.surname AS deactivated_by_surname,
                            c.country AS country_name, dv.division_name,
                            dp.department_name, un.unit_name, sc.section_name
                        FROM
                            ' . $this->table . ' u
                        LEFT JOIN 
                            users u1 ON u.registeredBy = u1.userId
                        LEFT JOIN 
                            users u2 ON u.editedBy = u2.userId
                        LEFT JOIN 
                            users u3 ON u.deactivated_by = u2.userId
                        LEFT JOIN 
                            countries c ON u.country = c.id                         
                        LEFT JOIN 
                            divisions dv ON u.division_id = dv.id 
                        LEFT JOIN 
                            departments dp ON u.department_id = dp.id 
                        LEFT JOIN 
                            units un ON u.unit_id = un.id
                        LEFT JOIN 
                            sections sc ON u.section_id = sc.id
                        LEFT JOIN 
                            department_categories d ON u.department = d.id
                        WHERE
                            u.userId = :userId
                        LIMIT 0,1
                    ';
            // Second query to retrieve user roles
            $queryUserRoles = 'SELECT 
                                    r.id,
                                    r.role_name
                                FROM
                                    user_roles ur
                                LEFT JOIN 
                                    roles r ON ur.role_id = r.id
                                WHERE
                                    ur.user_id = :userId
                            ';

            // Prepare Statement for user details
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':userId', $this->userId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Prepare Statement for user roles
            $stmtUserRoles = $this->conn->prepare($queryUserRoles);
            $stmtUserRoles->bindParam(':userId', $this->userId);
            $stmtUserRoles->execute();
            $userRoles = $stmtUserRoles->fetchAll(PDO::FETCH_ASSOC);

            //Set Properties
            $this->userId = $row['userId'];
            $this->staffNumber = $row['staffNumber'] ? $row['staffNumber'] : 'N/A';
            $this->name = $row['name'];
            $this->surname = $row['surname'];
            $this->country_name = $row['country_name'];
            $this->country_id = $row['country'];
            $this->division_name = $row['division_name'] ? $row['division_name'] : 'N/A';
            $this->division_id = $row['division_id'];
            $this->department_name = $row['department_name'] ? $row['department_name'] : 'N/A';
            $this->department_id = $row['department_id'];
            $this->unit_name = $row['unit_name'] ? $row['unit_name'] : 'N/A';
            $this->unit_id = $row['unit_id'];
            $this->section_name = $row['section_name'] ? $row['section_name'] : 'N/A';
            $this->section_id = $row['section_id'];
            $this->isOnLeave = $row['isOnLeave'];
            $this->email = $row['email'];
            $this->deactivated_by = $row['deactivated_by_name'] ? $row['deactivated_by_name'] . ' ' . $row['deactivated_by_surname'] : 'N/A';
            $this->deactivated_at = $row['deactivated_at'] ? $row['deactivated_at'] : 'N/A';
            $this->forgot_password_date = $row['forgot_password_date'] ? $row['forgot_password_date'] : 'N/A';
            $this->username = $row['username'];
            $this->status = $row['status'];
            $this->registeredBy = $row['registeredBy'] ? $row['created_by_name'] . ' ' . $row['created_by_surname'] : 'N/A';
            $this->registerDate = date("F j, Y g:i a", strtotime($row['registerDate']));
            $this->editedBy = $row['updated_by_name'] ? $row['updated_by_name'] . ' ' . $row['updated_by_surname'] : 'N/A';
            $this->editedDate = $row['editedDate'] ? date("F j, Y g:i a", strtotime($row['editedDate'])) : 'N/A';

            // Set user roles
            $this->roles = $userRoles;

            return $stmt;
        }


        public function create() {
            // Start transaction
            $this->conn->beginTransaction();
        
            // Insert basic user data
            if ($this->insertUserData()) {
                // Get the user ID of the inserted user
                $userId = $this->conn->lastInsertId();
        
                // Insert user roles
                $this->insertUserRoles($userId, $this->roles);
        
                // Retrieve role permissions and insert user permissions
                $this->insertUserPermissions($userId, $this->roles);
        
                // Commit transaction
                $this->conn->commit();
        
                return true; // User created successfully with roles and permissions
            } else {
                // Rollback transaction on error
                $this->conn->rollback();
                return false; // Error occurred while creating user
            }
        }

        // Insert basic user data
        private function insertUserData() {
            $query = ' INSERT INTO '.$this->table.'
                SET
                    name = :name,
                    surname = :surname,
                    staffNumber = :staffNumber,
                    country = :country,
                    division_id = :division_id,
                    department_id = :department_id,
                    unit_id = :unit_id,
                    section_id = :section_id,                    
                    email = :email,
                    username = :username,
                    password = :password,
                    status = :status,
                    registeredBy = :registeredBy,
                    registerDate = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->registeredBy = $this->this_user;

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':staffNumber', $this->staffNumber);
            $stmt->bindParam(':country', $this->country_id);
            $stmt->bindParam(':division_id', $this->division_id);
            $stmt->bindParam(':department_id', $this->department_id);
            $stmt->bindParam(':unit_id', $this->unit_id);
            $stmt->bindParam(':section_id', $this->section_id);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':registeredBy', $this->registeredBy);

            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        // Insert user roles
        private function insertUserRoles($user_id, $rolesData) {
            if (!empty($rolesData)) {
                foreach ($rolesData as $roleId) {
                    $query = 'INSERT INTO user_roles (user_id, role_id, created_by, created_at) VALUES (:user_id, :roleId, :created_by, Now())';
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':roleId', $roleId);
                    $stmt->bindParam(':created_by', $this->this_user);
                    $stmt->execute();
                }
            }
        }

        // Insert user permissions
        private function insertUserPermissions($user_id, $rolesData) {
            if (!empty($rolesData)) {
                foreach ($rolesData as $roleId) {
                    // Check if the permission already exists for the user
                    $query = 'INSERT INTO user_permissions (user_id, permission_id, module_id, created_by, created_at) 
                              SELECT :user_id, mp.permission_id, mp.module_id, :created_by, Now()
                              FROM role_permissions rp 
                              JOIN module_permissions mp ON rp.permission_id = mp.permission_id
                              WHERE rp.role_id = :roleId
                              AND NOT EXISTS (
                                  SELECT 1 FROM user_permissions up 
                                  WHERE up.user_id = :user_id 
                                  AND up.permission_id = mp.permission_id 
                                  AND up.module_id = mp.module_id
                              )';
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':created_by', $this->this_user);
                    $stmt->bindParam(':roleId', $roleId);
                    $stmt->execute();
                }
            }
        }

        // Function to delete existing user roles
        private function deleteUserRoles($user_id) {
            // Create query
            $query = 'DELETE FROM user_roles WHERE user_id = :user_id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        }

        // Function to delete existing user permissions
        private function deleteUserPermissions($user_id) {
            $query = 'DELETE FROM user_permissions WHERE user_id = :user_id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            return $stmt->execute();
        }


        public function auto_create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    name = :name,
                    surname = :surname,
                    country = :country_id,
                    email = :email,
                    username = :username,
                    password = :password,
                    status = :status,
                    registerDate = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->registeredBy = $this->this_user;

            //bind data
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':country', $this->country_id);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':status', $this->status);

            //execute query
            if($stmt->execute()) {
                $last_id = $this->conn->lastInsertId();
                return $last_id;
            }

            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function update() {
            try {
                // Start transaction
                $this->conn->beginTransaction();
        
                // Update basic user data
                if ($this->updateUserData()) {
                    // Delete existing user roles, permissions, and modules
                    $this->deleteUserRoles($this->userId);
                    $this->deleteUserPermissions($this->userId);
        
                    // Insert new user roles, permissions, and modules
                    $this->insertUserRoles($this->userId, $this->roles);
                    $this->insertUserPermissions($this->userId, $this->roles);
        
                    // Commit transaction
                    $this->conn->commit();
        
                    return true; // User updated successfully with roles, permissions, and modules
                } else {
                    // Rollback transaction on error
                    $this->conn->rollback();
                    return false; // Error occurred while updating user
                }
            } catch (PDOException $e) {
                // If any exception occurs, rollback transaction and return false
                $this->conn->rollback();
                
                // return $e->getMessage();
                return false;
            }
        }
         //Update Idea
         public function updateUserData() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    name = :name,
                    surname = :surname,
                    staffNumber = :staffNumber,
                    country = :country,
                    division_id = :division_id,
                    department_id = :department_id,
                    unit_id = :unit_id,
                    section_id = :section_id,
                    email = :email,
                    username = :username,
                    editedBy = :editedBy,
                    editedDate = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->editedBy = $this->this_user;

            //bind data
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':surname', $this->surname);
            $stmt->bindParam(':staffNumber', $this->staffNumber);
            $stmt->bindParam(':country', $this->country_id);
            $stmt->bindParam(':division_id', $this->division_id);
            $stmt->bindParam(':department_id', $this->department_id);
            $stmt->bindParam(':unit_id', $this->unit_id);
            $stmt->bindParam(':section_id', $this->section_id);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':editedBy', $this->editedBy);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            return $stmt->execute();
        }

        
        //Update Save Idea Changes priority
        public function activate() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    registeredBy = :registeredBy,
                    registerDate = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //clean data

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':registeredBy', $this->this_user);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function deactivate() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    deactivated_by = :deactivated_by,
                    deactivated_at = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //clean data

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':deactivated_by', $this->this_user);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function reset_password() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    password = :password,
                    editedBy = :editedBy,
                    editedDate = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':editedBy', $this->this_user);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function forgot_password($password, $userId) {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    password = :password,
                    forgot_password_date = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':userId', $userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function change_password($password, $userId) {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    password = :password,
                    last_change_password_at = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //bind data
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':userId', $userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }


        //Update Save Idea Changes Status
        public function delete() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    editedBy = :editedBy,
                    editedDate = Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            //clean data

            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':editedBy', $this->this_user);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }


        public function create_privileges() {
            //query
            $query = ' INSERT INTO privileges
                SET
                    userId                 = :userId,
                    can_add_user 			= :can_add_user, 
					can_view_user 			= :can_view_user, 
					can_edit_user 			= :can_edit_user, 
					can_deactivate_user 	= :can_deactivate_user, 
					can_reset_user_password = :can_reset_user_password, 
					can_delete_user 		= :can_delete_user, 
					can_give_privileges 	= :can_give_privileges, 
					can_see_settings 		= :can_see_settings, 
					can_update_notifications = :can_update_notifications, 
					can_be_country_manager  = :can_be_country_manager, 
					can_be_exco             = :can_be_exco, 
					can_be_gmd              = :can_be_gmd, 
					can_be_coo              = :can_be_coo, 
					can_add_activity 		= :can_add_activity, 
					can_be_activity_hod 	= :can_be_activity_hod, 
					can_be_activity_coo 	= :can_be_activity_coo, 
					can_be_activity_country_manager = :can_be_activity_country_manager, 
					can_be_activity_md 		= :can_be_activity_md, 							
					can_view_activities_reports = :can_view_activities_reports,
					can_be_incident_pro_manager = :can_be_incident_pro_manager, 	
					can_view_incident_reports 	= :can_view_incident_reports, 	
					can_add_ideas 			= :can_add_ideas, 	
					can_do_ideas_funneling 	= :can_do_ideas_funneling, 	
					can_do_ideas_sharktank 	= :can_do_ideas_sharktank, 	
					can_view_ideas_reports 	= :can_view_ideas_reports, 
					can_add_booking 		= :can_add_booking, 	
					can_be_approver 		= :can_be_approver, 							
					can_view_book_reports 	= :can_view_book_reports, 
					can_add_cash_requests 	= :can_add_cash_requests, 
					can_be_cash_hod 		= :can_be_cash_hod, 
					can_be_cash_coo 		= :can_be_cash_coo, 
					can_be_cash_manager 	= :can_be_cash_manager, 
					can_prosess_flight 		= :can_prosess_flight, 
					can_be_cash_finance 	= :can_be_cash_finance, 						
					can_view_cash_reports 	= :can_view_cash_reports, 											
					can_add_equip_requests 	= :can_add_equip_requests, 					
					can_be_equip_hod 		= :can_be_equip_hod, 					
					can_be_equip_inn 		= :can_be_equip_inn, 					
					can_be_equip_country_manager = :can_be_equip_country_manager, 					
					can_be_equip_coo 		= :can_be_equip_coo, 					
					can_be_equip_operations = :can_be_equip_operations, 					
					can_be_equip_gmd 		= :can_be_equip_gmd, 					
					can_view_equip_reports 	= :can_view_equip_reports, 
					can_view_monitoring_tasks 	= :can_view_monitoring_tasks, 					
					can_be_monitoring_pro_manager 	= :can_be_monitoring_pro_manager, 					
					can_view_monitoring_reports 	= :can_view_monitoring_reports, 
					can_view_my_minutes 	= :can_view_my_minutes, 
					can_add_all_minutes 	= :can_add_all_minutes, 
					can_view_minute_reports = :can_view_minute_reports, 	
					can_view_copay 			= :can_view_copay, 	
					can_add_copay 			= :can_add_copay, 
					can_activate_copay 		= :can_activate_copay, 
					can_view_copay_reports 	= :can_view_copay_reports, 	
					can_add_strategy_bsc 	= :can_add_strategy_bsc, 	
					can_be_strategy_hod 	= :can_be_strategy_hod, 
					can_be_strategy_division = :can_be_strategy_division, 
					can_be_strategy_hr 	    = :can_be_strategy_hr, 	
					updated_by 				= :updated_by,
					last_update 			= Now()
            ';
            

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->editedBy = $this->this_user;

            //bind data
            $stmt->bindParam(':userId', $this->userId);
            $stmt->bindParam(':can_add_user', $this->can_add_user);
            $stmt->bindParam(':can_view_user', $this->can_view_user);
            $stmt->bindParam(':can_edit_user', $this->can_edit_user);
            $stmt->bindParam(':can_deactivate_user', $this->can_deactivate_user);
            $stmt->bindParam(':can_reset_user_password', $this->can_reset_user_password);
            $stmt->bindParam(':can_delete_user', $this->can_delete_user);
            $stmt->bindParam(':can_give_privileges', $this->can_give_privileges);
            $stmt->bindParam(':can_see_settings', $this->can_see_settings);
            $stmt->bindParam(':can_update_notifications', $this->can_update_notifications);
            $stmt->bindParam(':can_be_country_manager', $this->can_be_country_manager);
            $stmt->bindParam(':can_be_exco', $this->can_be_exco);
            $stmt->bindParam(':can_be_gmd', $this->can_be_gmd);
            $stmt->bindParam(':can_be_coo', $this->can_be_coo);
            $stmt->bindParam(':can_add_activity', $this->can_add_activity);
            $stmt->bindParam(':can_be_activity_hod', $this->can_be_activity_hod);
            $stmt->bindParam(':can_be_activity_coo', $this->can_be_activity_coo);
            $stmt->bindParam(':can_be_activity_country_manager', $this->can_be_activity_country_manager);
            $stmt->bindParam(':can_be_activity_md', $this->can_be_activity_md);
            $stmt->bindParam(':can_view_activities_reports', $this->can_view_activities_reports);
            $stmt->bindParam(':can_be_incident_pro_manager', $this->can_be_incident_pro_manager);
            $stmt->bindParam(':can_view_incident_reports', $this->can_view_incident_reports);
            $stmt->bindParam(':can_add_ideas', $this->can_add_ideas);
            $stmt->bindParam(':can_do_ideas_funneling', $this->can_do_ideas_funneling);
            $stmt->bindParam(':can_do_ideas_sharktank', $this->can_do_ideas_sharktank);
            $stmt->bindParam(':can_view_ideas_reports', $this->can_view_ideas_reports);
            $stmt->bindParam(':can_add_booking', $this->can_add_booking);
            $stmt->bindParam(':can_be_approver', $this->can_be_approver);
            $stmt->bindParam(':can_view_book_reports', $this->can_view_book_reports);
            $stmt->bindParam(':can_add_cash_requests', $this->can_add_cash_requests);
            $stmt->bindParam(':can_be_cash_hod', $this->can_be_cash_hod);
            $stmt->bindParam(':can_be_cash_coo', $this->can_be_cash_coo);
            $stmt->bindParam(':can_be_cash_manager', $this->can_be_cash_manager);
            $stmt->bindParam(':can_prosess_flight', $this->can_prosess_flight);
            $stmt->bindParam(':can_be_cash_finance', $this->can_be_cash_finance);
            $stmt->bindParam(':can_view_cash_reports', $this->can_view_cash_reports);
            $stmt->bindParam(':can_add_equip_requests', $this->can_add_equip_requests);
            $stmt->bindParam(':can_be_equip_hod', $this->can_be_equip_hod);
            $stmt->bindParam(':can_be_equip_inn', $this->can_be_equip_inn);
            $stmt->bindParam(':can_be_equip_country_manager', $this->can_be_equip_country_manager);
            $stmt->bindParam(':can_be_equip_coo', $this->can_be_equip_coo);
            $stmt->bindParam(':can_be_equip_operations', $this->can_be_equip_operations);
            $stmt->bindParam(':can_be_equip_gmd', $this->can_be_equip_gmd);
            $stmt->bindParam(':can_view_equip_reports', $this->can_view_equip_reports);
            $stmt->bindParam(':can_view_monitoring_tasks', $this->can_view_monitoring_tasks);
            $stmt->bindParam(':can_be_monitoring_pro_manager', $this->can_be_monitoring_pro_manager);
            $stmt->bindParam(':can_view_monitoring_reports', $this->can_view_monitoring_reports);
            $stmt->bindParam(':can_view_my_minutes', $this->can_view_my_minutes);
            $stmt->bindParam(':can_add_all_minutes', $this->can_add_all_minutes);
            $stmt->bindParam(':can_view_minute_reports', $this->can_view_minute_reports);
            $stmt->bindParam(':can_view_copay', $this->can_view_copay);
            $stmt->bindParam(':can_add_copay', $this->can_add_copay);
            $stmt->bindParam(':can_activate_copay', $this->can_activate_copay);
            $stmt->bindParam(':can_view_copay_reports', $this->can_view_copay_reports);
            $stmt->bindParam(':can_add_strategy_bsc', $this->can_add_strategy_bsc);
            $stmt->bindParam(':can_be_strategy_hod', $this->can_be_strategy_hod);
            $stmt->bindParam(':can_be_strategy_division', $this->can_be_strategy_division);
            $stmt->bindParam(':can_be_strategy_hr', $this->can_be_strategy_hr);
            $stmt->bindParam(':updated_by', $this->editedBy);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }

        public function update_privileges() {
            //query
            $query = ' UPDATE privileges
                SET
                    can_add_user 			= :can_add_user, 
					can_view_user 			= :can_view_user, 
					can_edit_user 			= :can_edit_user, 
					can_deactivate_user 	= :can_deactivate_user, 
					can_reset_user_password = :can_reset_user_password, 
					can_delete_user 		= :can_delete_user, 
					can_give_privileges 	= :can_give_privileges, 
					can_see_settings 		= :can_see_settings, 
					can_update_notifications = :can_update_notifications, 
					can_be_country_manager  = :can_be_country_manager, 
					can_be_exco             = :can_be_exco, 
					can_be_gmd              = :can_be_gmd, 
					can_be_coo              = :can_be_coo, 
					can_add_activity 		= :can_add_activity, 
					can_be_activity_hod 	= :can_be_activity_hod, 
					can_be_activity_coo 	= :can_be_activity_coo, 
					can_be_activity_country_manager = :can_be_activity_country_manager, 
					can_be_activity_md 		= :can_be_activity_md, 							
					can_view_activities_reports = :can_view_activities_reports,
					can_be_incident_pro_manager = :can_be_incident_pro_manager, 	
					can_view_incident_reports 	= :can_view_incident_reports, 	
					can_add_ideas 			= :can_add_ideas, 	
					can_do_ideas_funneling 	= :can_do_ideas_funneling, 	
					can_do_ideas_sharktank 	= :can_do_ideas_sharktank, 	
					can_view_ideas_reports 	= :can_view_ideas_reports, 
					can_add_booking 		= :can_add_booking, 	
					can_be_approver 		= :can_be_approver, 							
					can_view_book_reports 	= :can_view_book_reports, 
					can_add_cash_requests 	= :can_add_cash_requests, 
					can_be_cash_hod 		= :can_be_cash_hod, 
					can_be_cash_coo 		= :can_be_cash_coo, 
					can_be_cash_manager 	= :can_be_cash_manager, 
					can_prosess_flight 		= :can_prosess_flight, 
					can_be_cash_finance 	= :can_be_cash_finance, 						
					can_view_cash_reports 	= :can_view_cash_reports, 											
					can_add_equip_requests 	= :can_add_equip_requests, 					
					can_be_equip_hod 		= :can_be_equip_hod, 					
					can_be_equip_inn 		= :can_be_equip_inn, 					
					can_be_equip_country_manager = :can_be_equip_country_manager, 					
					can_be_equip_coo 		= :can_be_equip_coo, 					
					can_be_equip_operations = :can_be_equip_operations, 					
					can_be_equip_gmd 		= :can_be_equip_gmd, 					
					can_view_equip_reports 	= :can_view_equip_reports, 
					can_view_monitoring_tasks 	= :can_view_monitoring_tasks, 					
					can_be_monitoring_pro_manager 	= :can_be_monitoring_pro_manager, 					
					can_view_monitoring_reports 	= :can_view_monitoring_reports, 
					can_view_my_minutes 	= :can_view_my_minutes, 
					can_add_all_minutes 	= :can_add_all_minutes, 
					can_view_minute_reports = :can_view_minute_reports, 	
					can_view_copay 			= :can_view_copay, 	
					can_add_copay 			= :can_add_copay, 
					can_activate_copay 		= :can_activate_copay, 
					can_view_copay_reports 	= :can_view_copay_reports, 	
					can_add_strategy_bsc 	= :can_add_strategy_bsc, 	
					can_be_strategy_hod 	= :can_be_strategy_hod, 
					can_be_strategy_division = :can_be_strategy_division, 
					can_be_strategy_hr 	    = :can_be_strategy_hr, 	
					updated_by 				= :updated_by,
					last_update 			= Now()
                WHERE 
                    userId = :userId
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->editedBy = $this->this_user;

            //bind data
            $stmt->bindParam(':can_add_user', $this->can_add_user);
            $stmt->bindParam(':can_view_user', $this->can_view_user);
            $stmt->bindParam(':can_edit_user', $this->can_edit_user);
            $stmt->bindParam(':can_deactivate_user', $this->can_deactivate_user);
            $stmt->bindParam(':can_reset_user_password', $this->can_reset_user_password);
            $stmt->bindParam(':can_delete_user', $this->can_delete_user);
            $stmt->bindParam(':can_give_privileges', $this->can_give_privileges);
            $stmt->bindParam(':can_see_settings', $this->can_see_settings);
            $stmt->bindParam(':can_update_notifications', $this->can_update_notifications);
            $stmt->bindParam(':can_be_country_manager', $this->can_be_country_manager);
            $stmt->bindParam(':can_be_exco', $this->can_be_exco);
            $stmt->bindParam(':can_be_gmd', $this->can_be_gmd);
            $stmt->bindParam(':can_be_coo', $this->can_be_coo);
            $stmt->bindParam(':can_add_activity', $this->can_add_activity);
            $stmt->bindParam(':can_be_activity_hod', $this->can_be_activity_hod);
            $stmt->bindParam(':can_be_activity_coo', $this->can_be_activity_coo);
            $stmt->bindParam(':can_be_activity_country_manager', $this->can_be_activity_country_manager);
            $stmt->bindParam(':can_be_activity_md', $this->can_be_activity_md);
            $stmt->bindParam(':can_view_activities_reports', $this->can_view_activities_reports);
            $stmt->bindParam(':can_be_incident_pro_manager', $this->can_be_incident_pro_manager);
            $stmt->bindParam(':can_view_incident_reports', $this->can_view_incident_reports);
            $stmt->bindParam(':can_add_ideas', $this->can_add_ideas);
            $stmt->bindParam(':can_do_ideas_funneling', $this->can_do_ideas_funneling);
            $stmt->bindParam(':can_do_ideas_sharktank', $this->can_do_ideas_sharktank);
            $stmt->bindParam(':can_view_ideas_reports', $this->can_view_ideas_reports);
            $stmt->bindParam(':can_add_booking', $this->can_add_booking);
            $stmt->bindParam(':can_be_approver', $this->can_be_approver);
            $stmt->bindParam(':can_view_book_reports', $this->can_view_book_reports);
            $stmt->bindParam(':can_add_cash_requests', $this->can_add_cash_requests);
            $stmt->bindParam(':can_be_cash_hod', $this->can_be_cash_hod);
            $stmt->bindParam(':can_be_cash_coo', $this->can_be_cash_coo);
            $stmt->bindParam(':can_be_cash_manager', $this->can_be_cash_manager);
            $stmt->bindParam(':can_prosess_flight', $this->can_prosess_flight);
            $stmt->bindParam(':can_be_cash_finance', $this->can_be_cash_finance);
            $stmt->bindParam(':can_view_cash_reports', $this->can_view_cash_reports);
            $stmt->bindParam(':can_add_equip_requests', $this->can_add_equip_requests);
            $stmt->bindParam(':can_be_equip_hod', $this->can_be_equip_hod);
            $stmt->bindParam(':can_be_equip_inn', $this->can_be_equip_inn);
            $stmt->bindParam(':can_be_equip_country_manager', $this->can_be_equip_country_manager);
            $stmt->bindParam(':can_be_equip_coo', $this->can_be_equip_coo);
            $stmt->bindParam(':can_be_equip_operations', $this->can_be_equip_operations);
            $stmt->bindParam(':can_be_equip_gmd', $this->can_be_equip_gmd);
            $stmt->bindParam(':can_view_equip_reports', $this->can_view_equip_reports);
            $stmt->bindParam(':can_view_monitoring_tasks', $this->can_view_monitoring_tasks);
            $stmt->bindParam(':can_be_monitoring_pro_manager', $this->can_be_monitoring_pro_manager);
            $stmt->bindParam(':can_view_monitoring_reports', $this->can_view_monitoring_reports);
            $stmt->bindParam(':can_view_my_minutes', $this->can_view_my_minutes);
            $stmt->bindParam(':can_add_all_minutes', $this->can_add_all_minutes);
            $stmt->bindParam(':can_view_minute_reports', $this->can_view_minute_reports);
            $stmt->bindParam(':can_view_copay', $this->can_view_copay);
            $stmt->bindParam(':can_add_copay', $this->can_add_copay);
            $stmt->bindParam(':can_activate_copay', $this->can_activate_copay);
            $stmt->bindParam(':can_view_copay_reports', $this->can_view_copay_reports);
            $stmt->bindParam(':can_add_strategy_bsc', $this->can_add_strategy_bsc);
            $stmt->bindParam(':can_be_strategy_hod', $this->can_be_strategy_hod);
            $stmt->bindParam(':can_be_strategy_division', $this->can_be_strategy_division);
            $stmt->bindParam(':can_be_strategy_hr', $this->can_be_strategy_hr);
            $stmt->bindParam(':updated_by', $this->editedBy);
            $stmt->bindParam(':userId', $this->userId);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }


        public function read_user_privileges() {
            //create query
            $query =  'SELECT 
                        p.*,
                        u1.name, 
                        u1.surname,
                        u1.email
                    FROM
                        ' . $this->table . ' u1
                    LEFT JOIN 
                        privileges p ON u1.userId = p.userId
                    WHERE
                        u1.userId = :userId
                    LIMIT 0,1
            ';

            //Prepare Statement
            $stmt = $this->conn->prepare($query);

            //Bind ID
            $stmt->bindParam(':userId', $this->userId);

            //Execute query
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //Set Properties
            $this->name = $row['name'];
            $this->surname = $row['surname'];
            $this->email = $row['email'];
            $this->can_be_super_user = $row['can_be_super_user'];
            $this->can_add_user = $row['can_add_user'];
            $this->can_view_user = $row['can_view_user'];
            $this->can_edit_user = $row['can_edit_user'];
            $this->can_deactivate_user = $row['can_deactivate_user'];
            $this->can_reset_user_password = $row['can_reset_user_password'];
            $this->can_delete_user = $row['can_delete_user'];
            $this->can_give_privileges = $row['can_give_privileges'];
            $this->can_see_settings = $row['can_see_settings'];
            $this->can_update_notifications = $row['can_update_notifications'];
            
            $this->can_be_country_manager = $row['can_be_country_manager'];
            $this->can_be_exco = $row['can_be_exco'];
            $this->can_be_gmd = $row['can_be_gmd'];
            $this->can_be_coo = $row['can_be_coo'];

            $this->can_add_activity = $row['can_add_activity'];
            $this->can_be_activity_hod = $row['can_be_activity_hod'];
            $this->can_be_activity_coo = $row['can_be_activity_coo'];
            $this->can_be_activity_country_manager = $row['can_be_activity_country_manager'];
            $this->can_be_activity_md = $row['can_be_activity_md'];
            $this->can_view_activities_reports = $row['can_view_activities_reports'];
            
            $this->can_be_incident_pro_manager = $row['can_be_incident_pro_manager'];
            $this->can_view_incident_reports = $row['can_view_incident_reports'];
            
            $this->can_add_ideas = $row['can_add_ideas'];
            $this->can_do_ideas_funneling = $row['can_do_ideas_funneling'];
            $this->can_do_ideas_sharktank = $row['can_do_ideas_sharktank'];
            $this->can_view_ideas_reports = $row['can_view_ideas_reports'];
            
            $this->can_add_booking = $row['can_add_booking'];
            $this->can_be_approver = $row['can_be_approver'];
            $this->can_view_book_reports = $row['can_view_book_reports'];
            
            $this->can_add_cash_requests = $row['can_add_cash_requests'];
            $this->can_be_cash_hod = $row['can_be_cash_hod'];
            $this->can_be_cash_coo = $row['can_be_cash_coo'];
            $this->can_be_cash_manager = $row['can_be_cash_manager'];
            $this->can_prosess_flight = $row['can_prosess_flight'];
            $this->can_be_cash_finance = $row['can_be_cash_finance'];
            $this->can_view_cash_reports = $row['can_view_cash_reports'];
            
            $this->can_add_equip_requests = $row['can_add_equip_requests'];
            $this->can_be_equip_hod = $row['can_be_equip_hod'];
            $this->can_be_equip_inn = $row['can_be_equip_inn'];
            $this->can_be_equip_country_manager = $row['can_be_equip_country_manager'];
            $this->can_be_equip_coo = $row['can_be_equip_coo'];
            $this->can_be_equip_operations = $row['can_be_equip_operations'];
            $this->can_be_equip_gmd = $row['can_be_equip_gmd'];
            $this->can_view_equip_reports = $row['can_view_equip_reports'];
            
            $this->can_view_monitoring_tasks = $row['can_view_monitoring_tasks'];
            $this->can_be_monitoring_pro_manager = $row['can_be_monitoring_pro_manager'];
            $this->can_view_monitoring_reports = $row['can_view_monitoring_reports'];
            $this->can_view_my_minutes = $row['can_view_my_minutes'];
            $this->can_add_all_minutes = $row['can_add_all_minutes'];
            $this->can_view_minute_reports = $row['can_view_minute_reports'];
            $this->can_view_copay = $row['can_view_copay'];
            $this->can_add_copay = $row['can_add_copay'];
            $this->can_activate_copay = $row['can_activate_copay'];
            $this->can_view_copay_reports = $row['can_view_copay_reports'];
            
            $this->can_add_strategy_bsc = $row['can_add_strategy_bsc'];
            $this->can_be_strategy_hod = $row['can_be_strategy_hod'];
            $this->can_be_strategy_division = $row['can_be_strategy_division'];
            $this->can_be_strategy_hr = $row['can_be_strategy_hr'];

            return $stmt;
        }

        //DROP DOWN LISTS
        public function users_list($and = ''){

            //Select Query
            $query = ' SELECT
                        userId, name, surname
                    FROM
                        '.$this->table.'
                    WHERE status IN("active") AND userId <> 0
                    '.$and.'     
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            //Execute Query
            $stmt->execute();

            $data = array();

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'userId' => $userId,
                    'name' => $name.' '.$surname,
                );
            }

            return $data;
        }


        public function save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender )
        {

            $body = '<html><head>';
            $body .=	'<title>'.$title.'</title>';
            $body .=	'</head>';
            $body .=	' <body style="font-size:14px;">';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">'.$message.' </p><br/>';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/seris/login" target="_blank">Click here</a> to login to SERIS.</p><br/>';		
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
            $body .= "</body></html>";
            
            $status_unsent = "unsent";
            $statement = $this->conn->prepare("INSERT INTO emails_to_send 
                                                (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                        VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
            $statement->bindParam(':email_to', $email_to);
            $statement->bindParam(':to_name', $to_name);
            $statement->bindParam(':reply_email', $reply_email_to);
            $statement->bindParam(':reply_name', $reply_name);
            $statement->bindParam(':email_subject', $title);
            $statement->bindParam(':body', $body);
            $statement->bindParam(':status', $status_unsent);
            $statement->bindParam(':user', $sender);

            if ($statement->execute()) 
            {
                return true;
            }
        }

    }
    

    define("JWT_SECRET_KEY", "Kh6Ya7JYUuHYScodAiRY7EulERx/NjJMAUi/GhyIPGyBp+bZ+9N3FqANdtrjcbq3wedscx/23edsxzae2JBbdH");
    define("JWT_ALGO", "HS256");