<?php

    class Pillar {
        //DB Stuff
        private $conn;
        private $table = "strategy_pillars_group_level";
        public $user_details;

        public $this_user;
        public $error;

        //User properties
        public $id;
        public $strategy_id;
        public $strategy_pillar;
        public $strategic_objective;
        public $picture_of_success;
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
        public function read_all_group_pillar(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    sp.pillar_name LIKE "%'.$this->searchValue.'%" OR 
                    s.strategic_objective LIKE "%'.$this->searchValue.'%" OR 
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
                        strategy_pillars sp ON s.strategy_pillar = sp.id
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
                        u.surname AS created_by_surname,
                        sp.pillar_name
                    FROM
                        '.$this->table.' s
                    INNER JOIN 
                        users u ON s.created_by = u.userId
                    LEFT JOIN 
                        strategy_pillars sp ON s.strategy_pillar = sp.id
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

                $view = $update = $delete = ''; 

                $view = '<li id="'.$id.'" class="view_pillar"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                $update = '<li id="'.$id.'" class="update_pillar"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> View/Edit</a></li>';
                $delete = '<li id="'.$id.'" class="delete_pillar"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                
                $data[] = array(
                    'id' => $id,
                    'strategy_pillar' => htmlspecialchars_decode($pillar_name),
                    'strategic_objective' => htmlspecialchars_decode($strategic_objective),
                    'picture_of_success' => htmlspecialchars_decode($picture_of_success),
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

        //Get Single 
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
            $this->strategy_pillar = $row["strategy_pillar"];
			$this->strategic_objective = htmlspecialchars_decode($row["strategic_objective"]);
			$this->picture_of_success = htmlspecialchars_decode($row["picture_of_success"]);
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
                    strategy_pillar = :strategy_pillar,
                    strategic_objective = :strategic_objective,
                    picture_of_success = :picture_of_success,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_id', $this->strategy_id);
            $stmt->bindParam(':strategy_pillar', $this->strategy_pillar);
            $stmt->bindParam(':strategic_objective', $this->strategic_objective);
            $stmt->bindParam(':picture_of_success', $this->picture_of_success);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }

        public function upload() {
            
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    strategy_id = :strategy_id,
                    strategy_pillar = :strategy_pillar,
                    strategic_objective = :strategic_objective,
                    picture_of_success = :picture_of_success,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_id', $this->strategy_id);
            $stmt->bindParam(':strategy_pillar', $this->strategy_pillar);
            $stmt->bindParam(':strategic_objective', $this->strategic_objective);
            $stmt->bindParam(':picture_of_success', $this->picture_of_success);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }

            return $stmt->error;
        }


        //  //Update Idea
         public function update() {
            //query
            $query = ' UPDATE '.$this->table.'
                SET
                    strategy_pillar = :strategy_pillar,
                    strategic_objective = :strategic_objective,
                    picture_of_success = :picture_of_success,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':strategy_pillar', $this->strategy_pillar);
            $stmt->bindParam(':strategic_objective', $this->strategic_objective);
            $stmt->bindParam(':picture_of_success', $this->picture_of_success);
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
        
        public function is_pillar_exists() {
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

        public function is_same_pillar_exists($strategy_id, $and){
            $query =  'SELECT strategy_pillar FROM ' . $this->table . ' 
                        WHERE strategy_pillar=:strategy_pillar AND strategy_id = :strategy_id '.$and.' 
                    ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':strategy_pillar', $this->strategy_pillar);
            $stmt->bindParam(':strategy_id', $strategy_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

    }
    