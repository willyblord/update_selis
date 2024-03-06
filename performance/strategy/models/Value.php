<?php

    class Value {
        //DB Stuff
        private $conn;
        private $table = "strategy_values";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $strategy_id;
        public $value_title;
        public $value_description;
        public $created_at;
        public $created_by;
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
        public function read_all_values(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    s.value_title LIKE "%'.$this->searchValue.'%" OR 
                    s.value_description LIKE "%'.$this->searchValue.'%" OR 
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
                    LEFT JOIN
                        strategies st ON s.strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND s.strategy_id = :strategy_id
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':strategy_id', $this->strategy_id);
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
                    LEFT JOIN
                        strategies st ON s.strategy_id = st.id
                    WHERE st.status NOT IN ("ended") AND s.strategy_id = :strategy_id
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':strategy_id', $this->strategy_id);

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

                $update = $delete = ''; 

                $update = '<li id="'.$id.'" class="update_value"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                $delete = '<li id="'.$id.'" class="delete_value"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                

                $data[] = array(
                    'id' => $id,
                    'value_title' => htmlspecialchars_decode($value_title),
                    'value_description' => htmlspecialchars_decode($value_description),
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$update.'                             
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
                    LEFT JOIN
                        strategies st ON s.strategy_id = st.id
                    WHERE
                        st.status NOT IN ("ended") AND
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
            $this->value_title = $row["value_title"];
			$this->value_description = $row["value_description"];
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
                    strategy_id = :strategy_id,
                    value_title = :value_title,
                    value_description = :value_description,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_id', $this->strategy_id);
            $stmt->bindParam(':value_title', $this->value_title);
            $stmt->bindParam(':value_description', $this->value_description);
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
                    value_title = :value_title,
                    value_description = :value_description,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':value_title', $this->value_title);
            $stmt->bindParam(':value_description', $this->value_description);
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
        
        public function is_value_exists() {
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

    }
    