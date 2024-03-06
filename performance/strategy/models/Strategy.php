<?php

    class Strategy {
        //DB Stuff
        private $conn;
        private $table = "strategies";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $strategy_name;
        public $year_range;
        public $vision;
        public $mission;
        public $status;
        public $created_at;
        public $created_by;
        public $activated_at;
        public $activated_by;
        public $ended_at;
        public $ended_by;
        public $updated_at;
        public $updated_by;


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

        //Get Items
        public function read_all_strategies(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    s.strategy_name LIKE "%'.$this->searchValue.'%" OR 
                    s.year_range LIKE "%'.$this->searchValue.'%" OR 
                    s.status LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 's.id DESC ';
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
                    '.$this->table.' s 
                    INNER JOIN 
                        users u ON s.created_by = u.userId 
                    WHERE s.status NOT IN ("ended")
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        s.*, u.name AS created_by_name, 
                        u.surname AS created_by_surname
                    FROM
                        '.$this->table.' s
                    INNER JOIN 
                        users u ON s.created_by = u.userId 
                    WHERE s.status NOT IN ("ended")
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

                $view = $update = $activate = $end = $delete = ''; 

                $view = '<li id="'.$id.'" class="view"><a href="view-strategy-3-year-'.$id.'" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "pending") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $activate = '<li id="'.$id.'" class="activate"><a href="#" class="dropdown-item"><i class="fas fa-check font-size-16 text-info me-1 "></i> Activate</a></li>';
                    $delete = '<li id="'.$id.'" class="delete"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }
                if( ($status === "active") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $end = '<li id="'.$id.'" class="end"><a href="#" class="dropdown-item"><i class="fas fa-check font-size-16 text-info me-1 "></i> End</a></li>';
                }


                if($status == "pending") {
                    $status = '<span class="badge" style="background:#b70000; color:#ffdfdf;">'.$status.'</span>';
                } elseif($status == "active"){
                    $status = '<span class="badge" style="background:#00b03a; color:#ffffff; ">'.$status.'</span>';
                } elseif($status == "ended") {
                    $status = '<span class="badge" style="background:#c6c6c6; color:#4c4c4c;">'.$status.'</span>';
                } 
                

                $data[] = array(
                    'id' => $id,
                    'strategy_name' => $strategy_name,
                    'year_range' => $year_range,
                    'status' => $status,
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'                                
                                '.$update.'                                
                                '.$activate.'                              
                                '.$end.'                         
                                '.$delete.'  
                            </ul>
                        </div>
                   '
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

        //Get Single Idea
        public function read_single() {
            //create query
            $query =  'SELECT 
                        s.*, 
                        u1.name AS name1,
                        u1.surname AS surname1,
                        u2.name AS name2,
                        u2.surname AS surname2
                    FROM 
                        ' . $this->table . ' s
                    INNER JOIN 
                        users u1 ON s.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON s.updated_by = u2.userId
                    WHERE
                        s.status NOT IN ("ended") AND 
                        s.id = :id
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
            $this->strategy_name = $row["strategy_name"];
			$this->year_range = $row["year_range"];
			$this->vision	= $row["vision"];
			$this->mission  = $row["mission"];
			$this->status 	= $row["status"];
			$this->created_by	= $row["name1"]." ".$row["surname1"];
			$this->created_at	= $row["created_at"];
			$this->updated_by	= $row["name2"]." ".$row["surname2"];
			$this->updated_at 	= $row["updated_at"];

            return $stmt;
        }


        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    strategy_name = :strategy_name,
                    year_range = :year_range,
                    vision = :vision,
                    mission = :mission,
                    status = :status,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_name', $this->strategy_name);
            $stmt->bindParam(':year_range', $this->year_range);
            $stmt->bindParam(':vision', $this->vision);
            $stmt->bindParam(':mission', $this->mission);
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }


        //  //Update Idea
         public function update() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    strategy_name = :strategy_name,
                    year_range = :year_range,
                    vision = :vision,
                    mission = :mission,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_name', $this->strategy_name);
            $stmt->bindParam(':year_range', $this->year_range);
            $stmt->bindParam(':vision', $this->vision);
            $stmt->bindParam(':mission', $this->mission);
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
        
        public function activate_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    activated_by = :activated_by,                   
                    activated_at = Now()
                WHERE 
                    id = :id
            ';
            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->activated_by = $this->this_user;
            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':activated_by', $this->activated_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function end_strategy() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    status = :status,
                    ended_by = :ended_by,                   
                    ended_at = Now()
                WHERE 
                    id = :id
            ';
            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->ended_by = $this->this_user;
            //bind data
            $stmt->bindParam(':status', $this->status);
            $stmt->bindParam(':ended_by', $this->ended_by);
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        public function delete() {
            //query
            $query = ' DELETE FROM '.$this->table.' WHERE id = :id ';
            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':id', $this->id);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            //print if something goes wrong
            printf("Error: %s.\n", $stmt->error);

            return false;
        }

        // SOME FUNCTIONS     
        
        public function is_strategy_exists() {
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

        public function check_strategy_status() {
            $query =  'SELECT `status` FROM ' . $this->table . ' WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['status'];
            } else {
                return false;
            }
        }


        public function is_there_an_active_strategy() {
            $query =  'SELECT * FROM ' . $this->table . ' WHERE status IN ("active") AND id <> :id';
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


        public function save_email( $email_to, $to_name, $reply_email_to, $reply_name, $title, $message, $sender )
        {

            $body = '<html><head>';
            $body .=	'<title>'.$title.'</title>';
            $body .=	'</head>';
            $body .=	' <body style="font-size:14px;">';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">'.$message.' </p><br/>';
            $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/seris/login" target="_blank">Login</a> to SERIS to view it.</p><br/>';		
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
    