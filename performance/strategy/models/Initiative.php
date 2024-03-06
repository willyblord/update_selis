<?php

    class Initiative {
        //DB Stuff
        private $conn;
        private $table = "strategy_group_initiatives";
        public $user_details;

        public $this_user;
        public $error;

        //Table properties
        public $id;
        public $group_initiative;
        public $business_category;
        public $business_category_name;
        public $pillar_id;
        public $strategy_pillar;
        public $target;
        public $measure;
        public $timeline;
        public $type;
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

        
        public function read(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    sp.pillar_name LIKE "%'.$this->searchValue.'%"
                ) ';
            }
            //Order
            $order = 'i.id DESC ';
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
                    '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        strategy_pillars_group_level p ON i.pillar_id = p.id
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN
                        strategy_business_category b ON i.business_category = b.id
                    LEFT JOIN
                        strategies st ON p.strategy_id = st.id
                    WHERE p.strategy_id = :strategy_id AND i.type = 1
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':strategy_id', $this->group_strategy_id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        i.*, b.business_category AS business_category_name, sp.pillar_name,                
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        st.status
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN
                        strategy_pillars_group_level p ON i.pillar_id = p.id
                    LEFT JOIN 
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN
                        strategy_business_category b ON i.business_category = b.id
                    LEFT JOIN
                        strategies st ON p.strategy_id = st.id
                    WHERE p.strategy_id = :strategy_id AND i.type = 1
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':strategy_id', $this->group_strategy_id);

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

                // if( ($status === "pending") ){
                    $update = '<li id="'.$id.'" class="update_initiative"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> View/Edit</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                // }

                $data[] = array(
                    'id' => $id,
                    'group_initiative' => htmlspecialchars_decode($group_initiative),
                    'strategy_pillar' => htmlspecialchars_decode($pillar_name),
                    'business_category' => htmlspecialchars_decode($business_category_name),
                    'target' => htmlspecialchars_decode($target),
                    'measure' => htmlspecialchars_decode($measure),
                    'timeline' => htmlspecialchars_decode($timeline),
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
                        i.*, 
                        b.business_category AS business_category_name, p.strategy_pillar,
                        u1.name AS created_by_name,
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name,
                        u2.surname AS updated_by_surname
                    FROM 
                        ' . $this->table . ' i
                    INNER JOIN 
                        users u1 ON i.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON i.updated_by = u2.userId
                    LEFT JOIN
                        strategy_business_category b ON i.business_category = b.id
                    LEFT JOIN
                        strategy_pillars_group_level p ON i.pillar_id = p.id
                    WHERE
                        i.id = :id
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
            $this->group_initiative = $row["group_initiative"];
            $this->business_category = $row["business_category"];
            $this->business_category_name = htmlspecialchars_decode($row["business_category_name"]);
            $this->pillar_id = $row["pillar_id"];
            $this->strategy_pillar = $row["strategy_pillar"];
			$this->target = htmlspecialchars_decode($row["target"]);
			$this->measure = htmlspecialchars_decode($row["measure"]);
			$this->timeline = htmlspecialchars_decode($row["timeline"]);
			$this->type = $row["type"];
			$this->created_by	= $row["created_by_name"]." ".$row["created_by_surname"];
			$this->created_at	= date("F j, Y g:i a", strtotime($row['created_at']));
			$this->updated_by = $row['updated_by_name'] ? $row['updated_by_name'] . ' ' . $row['updated_by_surname'] : 'N/A';
            $this->updated_at = $row['updated_at'] ? date("F j, Y g:i a", strtotime($row['updated_at'])) : 'N/A';


            return $stmt;
        }

        //Create Idea
        public function create() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                SET
                    business_category = :business_category,
                    group_initiative = :group_initiative,
                    pillar_id = :pillar_id,
                    target = :target,
                    measure = :measure,
                    timeline = :timeline,
                    type = :type,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':business_category', $this->business_category);
            $stmt->bindParam(':group_initiative', $this->group_initiative);
            $stmt->bindParam(':pillar_id', $this->pillar_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':measure', $this->measure);
            $stmt->bindParam(':timeline', $this->timeline);
            $stmt->bindParam(':type', $this->type);
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
                    business_category = :business_category,
                    group_initiative = :group_initiative,
                    pillar_id = :pillar_id,
                    target = :target,
                    measure = :measure,
                    timeline = :timeline,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':business_category', $this->business_category);
            $stmt->bindParam(':group_initiative', $this->group_initiative);
            $stmt->bindParam(':pillar_id', $this->pillar_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':measure', $this->measure);
            $stmt->bindParam(':timeline', $this->timeline);
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
        
        public function is_initiative_exists() {
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
    