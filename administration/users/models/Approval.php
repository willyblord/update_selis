<?php

    class Approval {
        //DB Stuff
        private $conn;
        private $table = "approvals";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $approval_name;
        public $approval_level;
        public $created_by;
        public $created_at;
        public $updated_by;
        public $updated_at;

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

        //Get Ideas
        public function read_approvals(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    approval_name LIKE "%'.$this->searchValue.'%" OR
                    approval_level LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'id DESC';
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
                    '.$this->table.' WHERE 1
                    '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT *
                    FROM
                        '.$this->table.' WHERE 1
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
                    "message" => "Data Not Found",
                    "draw"				=>	intval($this->draw),
                    "recordsTotal"		=> 	$num,
                    "recordsFiltered"	=>	$number_of_rows,
                    "data"			    =>	[]
                );
                return $output;

            }

            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $data[] = array(
                    'id' => $id,
                    'approval_name' => $approval_name,
                    'approval_level' => $approval_level
                );
            }


            $output = array(
                "success"      => true,
                "message"       => "Data Found",
                "draw"			=>	intval($this->draw),
                "recordsTotal"	=> 	$num,
                "recordsFiltered"=>	$number_of_rows,
                "data"			=>	$data
            );

            return $output;
        }

        public function is_same_approval_exists($and = '') {
            $query =  'SELECT approval_name, approval_level FROM ' . $this->table . ' WHERE (approval_name = :approval_name OR approval_level = :approval_level) '.$and.' ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':approval_name', $this->approval_name);
            $stmt->bindParam(':approval_level', $this->approval_level);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function is_approval_exists() {
            $query =  'SELECT id FROM ' . $this->table . ' WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
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
            $query =  'SELECT *
                    FROM
                        ' . $this->table . '
                    WHERE
                        id = :id
                    LIMIT 0,1
            ';

            //Prepare Statement
            $stmt = $this->conn->prepare($query);

            //Bind ID
            $stmt->bindParam(':id', $this->id);

            //Execute query
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //Set Properties
            $this->id = $row['id'];
            $this->approval_name = $row['approval_name'];
            $this->approval_level = $row['approval_level'];

            return $stmt;
        }


        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    approval_name = :approval_name,
                    approval_level = :approval_level,
                    created_by = :created_by,
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':approval_name', $this->approval_name);
            $stmt->bindParam(':approval_level', $this->approval_level);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }

        //Update Idea
        public function update() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    approval_name = :approval_name,
                    approval_level = :approval_level,
                    updated_by = :updated_by,
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':approval_name', $this->approval_name);
            $stmt->bindParam(':approval_level', $this->approval_level);
            $stmt->bindParam(':updated_by', $this->updated_by);
            $stmt->bindParam(':id', $this->id);

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

            $query = ' DELETE FROM '.$this->table.' WHERE id = :id ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':id', $this->id);            

            try {
                $stmt->execute();
                return true;
            } catch (PDOException $e) {
                if($e->getCode() == 23000){ // Cannot delete or update a parent row
                    return "You need to first delete items attached to this!";
                } else {
                    return $e->getMessage();
                }
            }
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
            
            $role_status_unsent = "unsent";
            $statement = $this->conn->prepare("INSERT INTO emails_to_send 
                                                (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                        VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
            $statement->bindParam(':email_to', $email_to);
            $statement->bindParam(':to_name', $to_name);
            $statement->bindParam(':reply_email', $reply_email_to);
            $statement->bindParam(':reply_name', $reply_name);
            $statement->bindParam(':email_subject', $title);
            $statement->bindParam(':body', $body);
            $statement->bindParam(':status', $role_status_unsent);
            $statement->bindParam(':user', $sender);

            if ($statement->execute()) 
            {
                return true;
            }
        }

    } 