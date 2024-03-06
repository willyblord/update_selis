<?php  
 
//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../../../administration/users/models/User.php';

//Instantiate SB and Connect
$database = new Database();

if (!isset($_COOKIE["jwt"])) {
    header("Location: login");
    exit();
}


$db = $database->connect(); 
//Instantiate Project object
$user = new User($db);

//Check jwt validation
$userDetails = $user->validate($_COOKIE["jwt"]);
if ($userDetails === false) {
    header("Location: login");
    exit();
}

$requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'HR_ROLE'];
$requiredPermissions = ['view_report_bsc'];
$requiredModules = 'Performance';

if ( !$user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules) ) {

    header("Location: login");
    exit();
} 

function fetch_data($db)  
{ 
    //Instantiate SB and Connect
    $database = new Database();
    $db = $database->connect(); 

    function clean_data($data) {  
        $data = trim($data);  
        $data = strip_tags($data);  
        $data = stripslashes($data);
        $data = htmlspecialchars($data);  
        return $data;  
    }

    $country = clean_data($_POST['country']);
    $department = clean_data($_POST['department']);
    $threeyear_strategy = clean_data($_POST['threeyear_strategy']);
    $annual_year = clean_data($_POST['annual_year']);
    $DateFrom = clean_data($_POST['DateFrom']);
    $DateTo = clean_data($_POST['DateTo']);
    
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

    $query = ' SELECT
                i.*, i.id AS init_id, p.strategy_pillar,               
                u.name AS created_by_name, 
                u.surname AS created_by_surname,
                c.status, st.year_range, c.year,
                ctry.country AS countryName,
                dept.category AS depart
            FROM
                strategy_initiatives i
            INNER JOIN 
                users u ON i.created_by = u.userId 
            LEFT JOIN 
                strategy_pillars_group_level p ON i.pillar_id = p.id 
            LEFT JOIN 
                strategy_country_level c ON i.country_strategy_id = c.id                     
            LEFT JOIN
                strategies st ON c.group_strategy_id = st.id
            LEFT JOIN 
                countries ctry ON c.country = ctry.id
            LEFT JOIN 
                department_categories dept ON c.department=dept.id 
            WHERE 1
                '.$where_qr.'
            ORDER BY i.target_score DESC      
    ';

    //Prepare statement
    $stmt = $db->prepare($query);

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
    $num = $stmt->rowCount();		
        
    if( $num > 0 )
    {
        $data_array = array();
        while($data = $stmt->fetch(PDO::FETCH_ASSOC)) {  
            $data_array[] = $data;
        }
        

        $countryTitle = '<span style="color:#b01c2e;">All</span>';
        if($country != ""){
            foreach ($data_array as $row1) {
                $countryTitle = '<span style="color:#b01c2e;">'.$row1['countryName'].'</span>';
            }
        }

        $departmentTitle = '<span style="color:#b01c2e;">All</span>';
        if($department != ""){
            foreach ($data_array as $row1) {
                $departmentTitle = '<span style="color:#b01c2e;">'.$row1['depart'].'</span>';
            }
        }

        $threeyearTitle = '<span style="color:#b01c2e;">All</span>';
        if($threeyear_strategy != ""){
            $threeyearTitle = '<span style="color:#b01c2e;">'.$row1['year_range'].'</span>';
        }

        $annualtTitle = '<span style="color:#b01c2e;">All</span>';
        if($annual_year != ""){
            foreach ($data_array as $row1) {
                $annualtTitle = '<span style="color:#b01c2e;">'.$row1['year'].'</span>';
            }
        }

        $dateTitle = '<span style="color:#b01c2e;">All</span>';
        if( $DateFrom !="" && $DateTo !=""){
            $dateTitle = date("F j, Y ", strtotime($DateFrom)).' <b>To</b> '.date("F j, Y ", strtotime($DateTo));
        }
            
        $output = '
            
        <div style="text-align:left;">
            <img src="../../../assets/images/smart_logo.jpg" width="80" >
            <p><b>SMART APPLICATIONS INTERNATIONAL</b></p>
        </div>

        <h3 style="text-align:center; text-decoration:underline;">SERIS - STRATEGY INITIATIVES REPORT</h3> <br/><br/>
            
            
            <div>        
                <b>Date selected:</b> '.$dateTitle.' <br/><br/>
				<b>Country:</b> '.$countryTitle.' |
				<b>Department:</b> '.$departmentTitle.' |
				<b>3Year Strategy:</b> '.$threeyearTitle.' | 
				<b>Year:</b> '.$annualtTitle.' <br/><br/> 
                
				<b>Number of initiatives:</b> '.$num.' <br/><br/>            
            </div>
    
            <table border="1" cellspacing="0" cellpadding="5">  
                <tr style="font-weight:bold;">  
                    <th>Strategy</th>
                    <th>Year</th>
                    <th>Initiative</th>
                    <th>Target</th>
                    <th>Measure</th>
                    <th>Figure</th>
                    <th>Weight</th>
                    <th>Raw Score</th>
                    <th>Target Score</th>
                    <th>Achieved Weight</th>
                </tr>

                ';

                foreach ($data_array as $row) {

                    $figure = $row["figure"];
                    $raw_score = $row["raw_score"];
                    if($row["measure"] == "Quantitative Parcentage") {
                        $figure = $row["figure"].'%';
                        $raw_score = $row["raw_score"].'%';
                    }
                    if($row["measure"] == "Quantitative Financial") {
                        $figure = number_format($row["figure"],2);
                        $raw_score = number_format($row["raw_score"],2);
                    }

                    // $releaseD = $row["financeReleaseDate"] ? $row["disb_by_surname"] . ' ' . $row["disb_by_name"] : 'N/A';
                    // $userRel = $row["financeReleaseDate"] ? date("F j, Y g:i a", strtotime($row["financeReleaseDate"])) : 'N/A';
                    // $budget_cat = $row["budgetCategory"] ? $row["budgetCategory"] : 'N/A';

                    $output .= 
                    '                         
                        <tr nobr="true">  
                            <td>'.$row["year_range"].'</td> 
                            <td>'.$row["year"].'</td> 	
                            <td>'.$row["initiative"].'</td> 
                            <td>'.$row["target"].'</td> 					
                            <td>'.$row["measure"].'</td> 
                            <td>'.$figure.'</td>  
                            <td>'.$row["weight"].'%</td> 
                            <td>'.$raw_score.'</td> 
                            <td>'.$row["target_score"].'%</td> 
                            <td>'.$row["computed_score"].'%</td> 
                        </tr>  
                    ';
                }
                
$output .= '
            </table>
        ';
        return $output;  
    }
}  
if(isset($_POST["viewPdf"]))  
{  
       set_time_limit(0);
       ini_set('memory_limit', '640M');
       
       require_once('../../../assets/tcpdf/tcpdf.php'); 

       // Extend the TCPDF class to create custom Header and Footer
       class MYPDF extends TCPDF {

            //Page header
            public function Header() {
                // Logo
                // $image_file = '../../assets/images/logo.png';
                // $this->Image($image_file, 10, 10, 15, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
                // Set font
                $this->SetFont('helvetica', 'B', 6);
                // Title
                $this->Cell(0, 15, 'SERIS - STRATEGY INITIATIVES REPORT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            }

            // Page footer
            public function Footer() {
                // Position at 15 mm from bottom
                $this->SetY(-15);
                // Set font
                $this->SetFont('helvetica', 'I', 8);
                // Page number
                $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
            }
        }

       $obj_pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       $obj_pdf->SetCreator(PDF_CREATOR);  
       $obj_pdf->SetTitle("SERIS - STRATEGY INITIATIVES REPORT");  
       $obj_pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
       $obj_pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
       $obj_pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
       $obj_pdf->SetDefaultMonospacedFont('helvetica');  
       $obj_pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
       $obj_pdf->SetMargins(PDF_MARGIN_LEFT, '15', PDF_MARGIN_RIGHT); 
       $obj_pdf->SetHeaderMargin(8);
       $obj_pdf->setPrintHeader(TRUE);  
       $obj_pdf->setPrintFooter(TRUE);  
       $obj_pdf->SetAutoPageBreak(TRUE, 15);   
       $obj_pdf->SetFont('helvetica', '', 7);  
       $obj_pdf->AddPage();  
       $content = '';  
       $content .= fetch_data($db);  
       $obj_pdf->writeHTML($content);  
       
       ob_end_clean();
       
       $obj_pdf->Output('Strategy_initiatives_report.pdf', 'I');  
}  
?>