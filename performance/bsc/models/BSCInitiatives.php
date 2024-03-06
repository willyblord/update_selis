<?php

    class BSCInitiatives {
        //DB Stuff
        private $conn;
        private $table = "strategy_individual_initiatives_bsc";
        public $user_details;

        public $this_user;
        public $error;

        //Table properties
        public $id;
        public $country;
        public $department;
        public $division;
        public $unit;
        public $section;
        public $individual_bsc_id;
        public $initiative_id;
        public $group_initiative;
        public $personal_initiative;
        public $country_strategy_id;
        public $pillar_id;
        public $pillar_name;
        public $bsc_parameter_name;
        public $bsc_parameter_id;
        public $bsc_parameter;
        public $strategy_pillar;
        public $initiative;
        public $value_impact;
        public $target;
        public $timeline;
        public $timeline_update;
        public $measure;
        public $figure;
        public $weight;
        public $raw_score;
        public $target_score;
        public $computed_score;
        public $evidences_url;
        public $supervisor_comments;
        public $evaluated_by;
        public $evaluated_at;
        public $assigned_to;
        public $assigned_at;
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
        public function read_all_division_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended") AND sv.location IN ("@MainFunction") 
                        AND sv.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND iv.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            // $counted->bindParam(':country', $this->country);
            // $counted->bindParam(':division', $this->division);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                            iv.*, i.group_initiative,
                            bp.bsc_parameter_name,               
                            u.name AS created_by_name, 
                            u.surname AS created_by_surname,
                            sv.status
                        FROM
                            '.$this->table.' iv
                        LEFT JOIN 
                            strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                        LEFT JOIN 
                            strategy_group_initiatives i ON iv.initiative_id = i.id 
                        LEFT JOIN 
                            strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                        LEFT JOIN 
                            strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                        LEFT JOIN
                            strategies st ON sv.group_strategy_id = st.id
                        INNER JOIN 
                            users u ON iv.created_by = u.userId                         
                        WHERE st.status NOT IN ("ended") AND sv.location IN ("@MainFunction") 
                            AND sv.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                            AND iv.individual_bsc_id = :individual_bsc_id
                            '.$searchQuery.'
                        ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            // $stmt->bindParam(':country', $this->country);
            // $stmt->bindParam(':division', $this->division);

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

                $view = $update = $delete = $progress = '';

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                    $progress = '<li id="'.$id.'" class="evaluate"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Evaluate</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'        
                                '.$progress.'    
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
        
        public function read_all_unit_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                 
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                        WHERE st.status NOT IN ("ended") 
                            AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@Unit","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                            AND iv.individual_bsc_id = :individual_bsc_id AND sv.country = :country AND sv.division = :division AND sv.department = :department AND sv.unit = :unit
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':user', $this->this_user);
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->bindParam(':department', $this->department);
            $counted->bindParam(':unit', $this->unit);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        iv.*, i.group_initiative,              
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        sv.status
                    FROM
                        '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended")                         
                        AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@Unit","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                        AND iv.individual_bsc_id = :individual_bsc_id AND sv.country = :country AND sv.division = :division AND sv.department = :department AND sv.unit = :unit
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user', $this->this_user);
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':division', $this->division);
            $stmt->bindParam(':department', $this->department);
            $stmt->bindParam(':unit', $this->unit);

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

                $view = $update = $delete = $progress = '';
          
                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }

                if( $status === "Q1_evaluation_at_hod" || $status === "Q2_evaluation_at_hod" || $status === "Q3_evaluation_at_hod" || $status === "Q4_evaluation_at_hod"){
                    $progress = '<li id="'.$id.'" class="evaluate"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Evaluate</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
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
                                '.$progress.'  
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


        public function read_all_section_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                 
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                        WHERE st.status NOT IN ("ended") 
                            AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@Section","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                            AND iv.individual_bsc_id = :individual_bsc_id 
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':user', $this->this_user);
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            // $counted->bindParam(':country', $this->country);
            // $counted->bindParam(':division', $this->division);
            // $counted->bindParam(':department', $this->department);
            // $counted->bindParam(':unit', $this->unit);
            // $counted->bindParam(':section', $this->section);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        iv.*, i.group_initiative,              
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        sv.status
                    FROM
                        '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended")                         
                        AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@Section","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                        AND iv.individual_bsc_id = :individual_bsc_id 
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user', $this->this_user);
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            // $stmt->bindParam(':country', $this->country);
            // $stmt->bindParam(':division', $this->division);
            // $stmt->bindParam(':department', $this->department);
            // $stmt->bindParam(':unit', $this->unit);
            // $stmt->bindParam(':section', $this->section);

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

                $view = $update = $delete = $progress = '';
          
                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }

                if( $status === "Q1_evaluation_at_hod" || $status === "Q2_evaluation_at_hod" || $status === "Q3_evaluation_at_hod" || $status === "Q4_evaluation_at_hod"){
                    $progress = '<li id="'.$id.'" class="evaluate"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Evaluate</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
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
                                '.$progress.'  
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

        public function read_all_department_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                 
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                        WHERE st.status NOT IN ("ended") 
                            AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@HOD","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                            AND iv.individual_bsc_id = :individual_bsc_id AND sv.country = :country AND sv.division = :division AND sv.department = :department
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':user', $this->this_user);
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $counted->bindParam(':country', $this->country);
            $counted->bindParam(':division', $this->division);
            $counted->bindParam(':department', $this->department);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        iv.*, i.group_initiative,              
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        sv.status
                    FROM
                        '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended")                         
                        AND ( (sv.status ="saved_as_draft" AND sv.created_by = :user ) OR (sv.status IN("@HOD","Q1_evaluation_at_hod","Q2_evaluation_at_hod","Q3_evaluation_at_hod","Q4_evaluation_at_hod")) )
                        AND iv.individual_bsc_id = :individual_bsc_id AND sv.country = :country AND sv.division = :division AND sv.department = :department
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':user', $this->this_user);
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $stmt->bindParam(':country', $this->country);
            $stmt->bindParam(':division', $this->division);
            $stmt->bindParam(':department', $this->department);

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

                $view = $update = $delete = $progress = '';
          
                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }

                if( $status === "Q1_evaluation_at_hod" || $status === "Q2_evaluation_at_hod" || $status === "Q3_evaluation_at_hod" || $status === "Q4_evaluation_at_hod"){
                    $progress = '<li id="'.$id.'" class="evaluate"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Evaluate</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
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
                                '.$progress.'  
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

        public function read_all_individual_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                        WHERE st.status NOT IN ("ended") AND sv.bsc_owner = :bsc_owner AND iv.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':bsc_owner', $this->this_user);
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                        iv.*, i.group_initiative,
                        bp.bsc_parameter_name,               
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        sv.status
                    FROM
                        '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended") AND sv.bsc_owner = :bsc_owner AND iv.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':bsc_owner', $this->this_user);
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
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

                $view = $update = $delete = $progress = '';

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( ($status === "saved_as_draft" || $status == "returned") ){
                    $update = '<li id="'.$id.'" class="update"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update</a></li>';
                    $delete = '<li id="'.$id.'" class="delete_initiative"><a href="#" class="dropdown-item"><i class="fas fa-trash font-size-16 text-info me-1 "></i> Delete</a></li>';
                }
                 
                if( $status === "approved" || $status === "Q1_evaluated" || $status === "Q2_evaluated" || $status === "Q3_evaluated" ){
                    $progress = '<li id="'.$id.'" class="update_progress"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Update Progress</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
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
                                '.$progress.'                             
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

        public function read_all_group_initiatives(){
            // Search 
            $searchQuery = '';
            if(isset($this->searchValue)){ 
                $searchQuery = ' AND (
                    bp.bsc_parameter_name LIKE "%'.$this->searchValue.'%" OR 
                    i.group_initiative LIKE "%'.$this->searchValue.'%" OR 
                    u.name LIKE "%'.$this->searchValue.'%" OR 
                    u.surname LIKE "%'.$this->searchValue.'%" 
                ) ';
            }
            //Order
            $order = 'iv.target_score DESC ';
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
                    '.$this->table.' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id                
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u ON iv.created_by = u.userId 
                    WHERE st.status NOT IN ("ended") AND sv.location IN ("@GMD") 
                        AND sv.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                        AND iv.individual_bsc_id = :individual_bsc_id
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            $counted->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query
            $query = ' SELECT
                            iv.*, i.group_initiative,
                            bp.bsc_parameter_name,               
                            u.name AS created_by_name, 
                            u.surname AS created_by_surname,
                            sv.status
                        FROM
                            '.$this->table.' iv
                        LEFT JOIN 
                            strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                        LEFT JOIN 
                            strategy_group_initiatives i ON iv.initiative_id = i.id 
                        LEFT JOIN 
                            strategy_individual_bsc_param p ON iv.bsc_parameter = p.id    
                        LEFT JOIN 
                            strategy_bsc_parameters bp ON p.bsc_parameter_id = bp.id               
                        LEFT JOIN
                            strategies st ON sv.group_strategy_id = st.id
                        INNER JOIN 
                            users u ON iv.created_by = u.userId                         
                        WHERE st.status NOT IN ("ended") AND sv.location IN ("@GMD") 
                            AND sv.status IN("pending","Q1_evaluation","Q2_evaluation","Q3_evaluation","Q4_evaluation")
                            AND iv.individual_bsc_id = :individual_bsc_id
                            '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);

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

                $view = $update = $delete = $progress = '';

                $view = '<li id="'.$id.'" class="view"><a href="#" class="dropdown-item"><i class="fas fa-eye font-size-16 text-info me-1 "></i> View</a></li>';
                
                if( $status === "Q1_evaluation" || $status === "Q2_evaluation" || $status === "Q3_evaluation" || $status === "Q4_evaluation"){
                    $progress = '<li id="'.$id.'" class="evaluate"><a href="#" class="dropdown-item"><i class="fas fas fa-pen font-size-16 text-info me-1 "></i> Evaluate</a></li>';
                }

                if($measure == "Quantitative Parcentage" || $measure == "Qualitative") {
                    $figure = $figure.'%';
                    $raw_score = $raw_score.'%';
                } elseif($measure == "Quantitative Financial") {
                    $figure = number_format($figure,2);
                    $raw_score = number_format($raw_score,2);
                }

                $data[] = array(
                    'id' => $id,
                    'bsc_parameter_name' => $bsc_parameter_name,
                    'initiative' => $group_initiative,
                    'value_impact' => $value_impact,
                    'target' => $target,
                    'timeline' => $timeline ? $timeline : '-',
                    'timeline_update' => $timeline_update,
                    'measure' => $measure ? $measure : '-',
                    'figure' => $figure ? $figure : '-',
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
                    'created_by' => $created_by_surname . ' ' . $created_by_name ,
                    'created_at' => date("F j, Y g:i a", strtotime($created_at)),
                    'actions' => '
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle card-drop" data-bs-toggle="dropdown" aria-expanded="false">
                                <button class="btn btn-sm btn-outline-info waves-effect waves-light">Actions</button>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                '.$view.'        
                                '.$progress.'    
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
                        iv.*, i.group_initiative,
                        sp.pillar_name, bp.bsc_parameter_name,
                        u1.name AS created_by_name,
                        u1.surname AS created_by_surname,
                        u2.name AS updated_by_name,
                        u2.surname AS updated_by_surname
                    FROM
                        ' . $this->table . ' iv
                    LEFT JOIN 
                        strategy_individual_bsc sv ON iv.individual_bsc_id = sv.id 
                    LEFT JOIN 
                        strategy_group_initiatives i ON iv.initiative_id = i.id 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON iv.pillar_id = p.id     
                    LEFT JOIN
                        strategy_pillars sp ON p.strategy_pillar = sp.id
                    LEFT JOIN 
                        strategy_individual_bsc_param pp ON iv.bsc_parameter = pp.id    
                    LEFT JOIN 
                        strategy_bsc_parameters bp ON pp.bsc_parameter_id = bp.id               
                    LEFT JOIN
                        strategies st ON sv.group_strategy_id = st.id
                    INNER JOIN 
                        users u1 ON iv.created_by = u1.userId
                    LEFT JOIN 
                        users u2 ON iv.updated_by = u2.userId             
                    WHERE
                        st.status NOT IN ("ended") AND 
                        iv.id = :id
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
            $this->individual_bsc_id = $row["individual_bsc_id"];
            $this->pillar_id = $row["pillar_id"];
            $this->pillar_name = $row["pillar_name"];
            $this->bsc_parameter_name = $row["bsc_parameter_name"];
            $this->bsc_parameter_id = $row["bsc_parameter"];
            $this->initiative_id = $row["initiative_id"];
            $this->group_initiative = $row["group_initiative"];
			$this->target  = $row["target"];
			$this->value_impact  = $row["value_impact"];
			$this->timeline  = $row["timeline"];
			$this->measure  = $row["measure"];
			$this->figure  = $row["figure"];
			$this->weight  = $row["weight"];
			$this->raw_score  = $row["raw_score"];
			$this->target_score  = $row["target_score"];
			$this->computed_score  = $row["computed_score"];
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
                    individual_bsc_id = :individual_bsc_id,
                    pillar_id = :pillar_id,
                    bsc_parameter = :bsc_parameter,
                    initiative_id = :initiative_id,
                    target = :target,
                    value_impact = :value_impact,
                    timeline = :timeline,
                    measure = :measure,
                    figure = :figure,
                    weight = :weight,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $stmt->bindParam(':pillar_id', $this->pillar_id);
            $stmt->bindParam(':bsc_parameter', $this->bsc_parameter);
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
            $stmt->bindParam(':timeline', $this->timeline);
            $stmt->bindParam(':measure', $this->measure);
            $stmt->bindParam(':figure', $this->figure);
            $stmt->bindParam(':weight', $this->weight);
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
                    pillar_id = :pillar_id,
                    bsc_parameter = :bsc_parameter,
                    initiative_id = :initiative_id,
                    target = :target,
                    value_impact = :value_impact,
                    timeline = :timeline,
                    measure = :measure,
                    figure = :figure,
                    weight = :weight,
                    updated_by = :updated_by,                   
                    updated_at = Now()
                WHERE 
                    id = :id
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->updated_by = $this->this_user;

            //bind data
            $stmt->bindParam(':pillar_id', $this->pillar_id);
            $stmt->bindParam(':bsc_parameter', $this->bsc_parameter);
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
            $stmt->bindParam(':timeline', $this->timeline);
            $stmt->bindParam(':measure', $this->measure);
            $stmt->bindParam(':figure', $this->figure);
            $stmt->bindParam(':weight', $this->weight);
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

        public function upload() {
            //query
            $query = ' INSERT INTO '.$this->table.'
                    SET
                        individual_bsc_id = :individual_bsc_id,
                        target = :target,
                        value_impact = :value_impact,
                        timeline = :timeline,
                        measure = :measure,
                        figure = :figure,
                        weight = :weight,
                        created_by = :created_by,                   
                        created_at = Now()
                ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);
            $stmt->bindParam(':target', $this->target);
            $stmt->bindParam(':value_impact', $this->value_impact);
            $stmt->bindParam(':timeline', $this->timeline);
            $stmt->bindParam(':measure', $this->measure);
            $stmt->bindParam(':figure', $this->figure);
            $stmt->bindParam(':weight', $this->weight);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }

        public function evaluate() {
            //query
            $query = ' INSERT INTO strategy_bsc_initiative_approvals
                    SET
                        initiative_id = :initiative_id,
                        raw_score = :raw_score,
                        target_score = ROUND(:target_score, 2),
                        computed_score = ROUND(:computed_score, 2),   
                        evidences_url = :evidences_url,
                        supervisor_comments = :supervisor_comments,
                        evaluated_by = :evaluated_by,                   
                        evaluated_at = Now()
                ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':raw_score', $this->raw_score);
            $stmt->bindParam(':target_score', $this->target_score);
            $stmt->bindParam(':computed_score', $this->computed_score);
            $stmt->bindParam(':evidences_url', $this->evidences_url);
            $stmt->bindParam(':supervisor_comments', $this->supervisor_comments);
            $stmt->bindParam(':evaluated_by', $this->evaluated_by);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }


        public function delete() {

            $queryCmt = ' DELETE FROM strategy_individual_bsc_comments WHERE initiative_id = :id ';
            $stmtCmt = $this->conn->prepare($queryCmt);
            $stmtCmt->bindParam(':id', $this->id);
            $stmtCmt->execute();

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

        public function create_own_initiative($business_category, $group_initiative, $pillar_id, $type) {
            //query
            $query = ' INSERT INTO strategy_group_initiatives
                SET
                    pillar_id = :pillar_id,
                    business_category = :business_category,
                    group_initiative = :group_initiative,
                    type = :type,
                    created_by = :created_by,                   
                    created_at = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);

            $this->created_by = $this->this_user;

            //bind data
            $stmt->bindParam(':pillar_id', $pillar_id);
            $stmt->bindParam(':business_category', $business_category);
            $stmt->bindParam(':group_initiative', $group_initiative);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':created_by', $this->created_by);

            //execute query
            if($stmt->execute()) {
                $last_init_id = $this->conn->lastInsertId();
                return $last_init_id;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }
        
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

        public function is_same_initiative_exists($individual_bsc_id, $and = ''){
            $query =  'SELECT initiative_id FROM ' . $this->table . ' 
                        WHERE initiative_id = :initiative_id AND individual_bsc_id = :individual_bsc_id '.$and.' 
                    ';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':initiative_id', $this->initiative_id);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            if($num > 0) {
                return true;
            } else {
                return false;
            }
            
        }

        public function check_parameter_weight($individual_bsc_id) {
            $query =  'SELECT SUM(parameter_weight) AS total_weight FROM strategy_individual_bsc_param
                        WHERE individual_bsc_id = :individual_bsc_id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['total_weight'];
            } else {
                return false;
            }
        }

        public function check_single_parameter($bsc_parameter) {
            $query =  'SELECT SUM(parameter_weight) AS total_weight FROM strategy_individual_bsc_param
                        WHERE id = :bsc_parameter
                    ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':bsc_parameter', $bsc_parameter);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['total_weight'];
            } else {
                return false;
            }
        }

        public function check_weight($individual_bsc_id, $bsc_parameter, $and = '') {
            $query =  'SELECT SUM(weight) AS total_weight FROM ' . $this->table . ' 
                        WHERE individual_bsc_id = :individual_bsc_id 
                        AND bsc_parameter = :bsc_parameter
                        '.$and.'  
                    ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->bindParam(':bsc_parameter', $bsc_parameter);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['total_weight'];
            } else {
                return false;
            }
        }

        public function check_wait($individual_bsc_id, $and = '') {
            $query =  'SELECT SUM(weight) AS total_weight FROM ' . $this->table . ' WHERE individual_bsc_id = :individual_bsc_id '.$and.'  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['total_weight'];
            } else {
                return false;
            }
        }

        public function is_missing_some_data($individual_bsc_id) {
            $query =  'SELECT * FROM strategy_individual_initiatives_bsc 
                        WHERE individual_bsc_id = :individual_bsc_id   
                        AND (pillar_id IS NULL OR bsc_parameter IS NULL OR initiative_id IS NULL)
                    ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':individual_bsc_id', $individual_bsc_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                return true;
            } else {
                return false;
            }
        }

        public function check_year($individual_bsc_id) {
            $query =  'SELECT year FROM strategy_individual_bsc WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $individual_bsc_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['year'];
            } else {
                return false;
            }
        }

        public function get_3year_strategy_id($individual_bsc_id) {
            $query =  'SELECT group_strategy_id FROM strategy_individual_bsc WHERE id = :id  ';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $individual_bsc_id);
            $stmt->execute();
            
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['group_strategy_id'];
            } else {
                return false;
            }
        }

        public function get_department_comments($initiative_id) {
            $query =  'SELECT s.*, u.name, u.surname FROM strategy_individual_bsc_comments s
                        LEFT JOIN users u ON s.userId = u.userId
                        WHERE s.initiative_id = :id  ORDER BY s.id DESC';            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $initiative_id);
            $stmt->execute();
            $num = $stmt->rowCount();
            
            $data = array();
            if($num > 0) {

                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
    
                    $data[] = array(
                        'id' => $id,
                        'comment' => $comment,
                        'comment_date' => date("M j, Y g:i a", strtotime($comment_date)),
                        'comment_by' => $name . ' ' . $surname ,
                    );
                }

                return $data;
            } else {
                return false;
            }
        }

        public function add_initiative_comment() {
            //query
            $query = ' INSERT INTO strategy_individual_bsc_comments
                SET
                    userId = :userId,
                    initiative_id = :initiative_id,
                    comment = :comment,                
                    comment_date = Now()
            ';

            //prepare statement
            $stmt = $this->conn->prepare($query);
            //bind data
            $stmt->bindParam(':userId', $this->this_user);
            $stmt->bindParam(':initiative_id', $this->id);
            $stmt->bindParam(':comment', $this->comment);

            //execute query
            if($stmt->execute()) {
                return true;
            }
            //print if something goes wrong
            // printf("Error: %s.\n", $stmt->error);

            return $stmt->error;
        }


        public function strategy_initiatives_report($country, $department, $threeyear_strategy, $annual_year, $DateFrom, $DateTo)
        {
            // Search 
            $searchQuery = '';
            // if(isset($this->searchValue)){
            //     $searchQuery = ' AND (
            //         c.refNo LIKE "%'.$this->searchValue.'%" OR 
            //         c.category LIKE "%'.$this->searchValue.'%" OR 
            //         c.status LIKE "%'.$this->searchValue.'%" OR 
            //         u.name LIKE "%'.$this->searchValue.'%" OR 
            //         u.surname LIKE "%'.$this->searchValue.'%" 
            //     ) ';
            // }
            //Order
            $order = 'i.target_score DESC';
            if(isset($this->columnIndex) && !empty($this->columnIndex)){
                $order = ' '.$this->columnIndex.' '.$this->columnSortOrder.' ';
            }

            //limit
            $limit = '';
            if(isset($this->rowperpage) && $this->rowperpage != -1  && !empty($this->rowperpage)){
                $limit =  'LIMIT ' . $this->start . ', ' . $this->rowperpage;
            }
            //Filters Checking
            $where_qr = ''; 

            if( $country !="" ){
                $where_qr .=  ' AND (c.country = :country)';
            }
            if( $department !="" ){
                $where_qr .=  ' AND (c.department = :department)';
            }
            if( $threeyear_strategy !="" ){
                $where_qr .=  ' AND (st.id = :year_range)';
            }
            if( $annual_year !="" ){
                $where_qr .=  ' AND (c.year = :year_annual)';
            }
            if( $DateFrom !="" && $DateTo !=""){
                $where_qr .= ' AND( cast(i.timeline as date) BETWEEN :dateFrom AND :dateTo )';
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
                        strategy_country_level c ON i.country_strategy_id = c.id                     
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE 1
                        '.$where_qr.'
                        '.$searchQuery.'
                '; 
            $counted = $this->conn->prepare($sql); 
            if ($country !="") $counted->bindParam(':country',$country);
            if ($department !="") $counted->bindParam(':department',$department);
            if ($threeyear_strategy !="") $counted->bindParam(':year_range',$threeyear_strategy);
            if ($annual_year !="") $counted->bindParam(':year_annual',$annual_year);
            if ($DateFrom !="" && $DateTo !="") {
                $counted->bindParam(':dateFrom',$DateFrom);
                $counted->bindParam(':dateTo',$DateTo);
            }
            $counted->execute(); 
            $number_of_rows = $counted->fetchColumn(); 

            //Create Query

            $query = ' SELECT
                        i.*, i.id AS init_id, p.strategy_pillar,                 
                        u.name AS created_by_name, 
                        u.surname AS created_by_surname,
                        c.status, st.year_range, c.year
                    FROM
                        '.$this->table.' i
                    INNER JOIN 
                        users u ON i.created_by = u.userId 
                    LEFT JOIN 
                        strategy_pillars_group_level p ON i.pillar_id = p.id 
                    LEFT JOIN 
                        strategy_country_level c ON i.country_strategy_id = c.id                     
                    LEFT JOIN
                        strategies st ON c.group_strategy_id = st.id
                    WHERE 1
                        '.$where_qr.'
                        '.$searchQuery.'
                    ORDER BY
                        '.$order.'
                    '.$limit.'            
            ';

            //Prepare statement
            $stmt = $this->conn->prepare($query);

            if ($country !="") $stmt->bindParam(':country',$country);
            if ($department !="") $stmt->bindParam(':department',$department);
            if ($threeyear_strategy !="") $stmt->bindParam(':year_range',$threeyear_strategy);
            if ($annual_year !="") $stmt->bindParam(':year_annual',$annual_year);
            if ($DateFrom !="" && $DateTo !="") {
                $stmt->bindParam(':dateFrom',$DateFrom);
                $stmt->bindParam(':dateTo',$DateTo);
            } 

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
                    'id' => $init_id,
                    'year_range' => $year_range,
                    'year' => $year,
                    'initiative' => $initiative,
                    'target' => $target,
                    'measure' => $measure,
                    'figure' => $figure,
                    'weight' => $weight.'%',
                    'raw_score' => $raw_score,
                    'target_score' => $target_score.'%',
                    'computed_score' => $computed_score.'%',
                    'created_by' => $created_by_surname . ' ' . $created_by_name
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

        /** =====================CHARTS================================= */

        //Get individual bsc scrore counts
        public function read_individual_bsc_scores_count() {
            //create query
            $query =  'SELECT
                        ROUND((SUM(target_score) / (COUNT(target_score) * 100)) * 100, 2) AS target_score,
                        ROUND(SUM(computed_score), 2) AS computed_score,
                        
                        ROUND((SUM(hr_target_score) / (COUNT(hr_target_score) * 100)) * 100, 2) AS hr_target_score,
                        ROUND(SUM(hr_computed_score), 2) AS hr_computed_score
                        
                    FROM
                        ' . $this->table . '
                    WHERE 
                        individual_bsc_id = :individual_bsc_id
                ';

            //Prepare Statement
            $stmt = $this->conn->prepare($query);

            //Bind ID
            $stmt->bindParam(':individual_bsc_id', $this->individual_bsc_id);

            //Execute query
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //Set Properties
            $this->target_score = $row['target_score'];
            $this->computed_score = $row["computed_score"];
            $this->hr_target_score = $row["hr_target_score"];
            $this->hr_computed_score = $row["hr_computed_score"];

            return $stmt;
        }

        /** =====================END OF CHARTS============================== */


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
    