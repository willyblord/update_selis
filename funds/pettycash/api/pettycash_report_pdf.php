<?php  
 
//Headers
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Method:POST');
header('Content-Type:application/json');

include_once '../../../include/Database.php';
include_once '../models/Pettycash.php';
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
$user_details = $user->validate($_COOKIE["jwt"]);
if ($user_details === false) {
    header("Location: login");
    exit();
}

if ($user_details['can_be_super_user'] != 1 && $user_details['can_view_cash_reports'] != 1) {
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
    $budget_category = clean_data($_POST['budget_category']);	
    $cashCategory = clean_data($_POST['cashCategory']);	
    $statusCheck = clean_data($_POST['statusCheck']);	
    $status = clean_data($_POST['status']);	
    $cashReqBy = clean_data($_POST['cashReqBy']);	
    $cashDisbBy = clean_data($_POST['cashDisbBy']);	
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
    if( $budget_category !="" ){
        $where_qr .=  ' AND (c.budget_category = :budget_category)';
    }
    if( $cashCategory !="" ){
        $where_qr .=  ' AND (c.category = :category)';
    }
    if( $statusCheck !="" ){
        if( $statusCheck =="disbursed" ){
            $where_qr .=  ' AND ( c.status IN ("completed", "cleared", "clearing", "clearDenied") ) ';
        } 
        elseif( $statusCheck =="inprogress" ){
            $where_qr .=  ' AND ( c.status IN ("approved", "@FinanceFromHOD", "@HOD", "suspended", "returnedFromHOD", "returnedFromFinance", "returnedFromCOO", "returnedFromGMD", "@COO", "@FinanceFromCOO", "@FinanceFromGMD", "@GMDfromCOO", "@GMDfromCOO") )';
        }								 
        elseif( $statusCheck =="declined" ){
            $where_qr .=  ' AND ( c.status IN ("rejected", "cancelled") )';
        }
    }
    if( $status !="" ){
        $where_qr .=  ' AND (c.status = :status)';
    }
    if( $cashReqBy !="" ){
        $where_qr .=  ' AND (c.requestBy = :requestBy)';
    }
    if( $cashDisbBy !="" ){
        $where_qr .=  ' AND (c.financeRelease = :financeRelease)';
    }
    if( $DateFrom !="" && $DateTo !=""){
        if( ($statusCheck !="" ) && ($statusCheck =="disbursed") ){
            $where_qr .= ' AND( cast(c.financeReleaseDate as date) BETWEEN :dateFrom AND :dateTo )';
        }
        elseif( ($statusCheck !="") || ($statusCheck =="inprogress") ){
            $where_qr .= ' AND( cast(c.requestDate as date) BETWEEN :dateFrom AND :dateTo )';
        }								
        elseif( ($statusCheck !="") || ($statusCheck =="declined") ){
            $where_qr .= ' AND( cast(c.requestDate as date) BETWEEN :dateFrom AND :dateTo )';
        }
    }

    $query = ' SELECT
            c.*,
            c.id AS pty_id,
            ctry.currency,
            ctry.country AS countryName,
            dept.category AS depart,
            u.name AS req_by_name,
            u.surname AS req_by_surname,
            u1.name AS disb_by_name,
            u1.surname AS disb_by_surname,
            bc.name AS budgetCategory
        FROM
            cashrequests c
        LEFT JOIN 
            users u ON c.requestBy = u.userId
        LEFT JOIN 
            users u1 ON c.financeRelease = u1.userId
        LEFT JOIN 
            countries ctry ON c.country = ctry.id
        LEFT JOIN 
            budget_categories bc ON c.budget_category = bc.id
        LEFT JOIN 
            department_categories dept ON c.department=dept.id 
        WHERE 1
            '.$where_qr.'
        ORDER BY c.id ASC
    ';
                            
    //Prepare statement
    $stmt = $db->prepare($query);

    if ($country !="") $stmt->bindValue(':country',$country);
    if ($department !="") $stmt->bindValue(':department',$department);
    if ($budget_category !="") $stmt->bindValue(':budget_category',$budget_category);
    if ($cashCategory !="") $stmt->bindValue(':category',$cashCategory);
    if ($status !="") $stmt->bindValue(':status',$status);
    if ($cashReqBy !="") $stmt->bindValue(':requestBy',$cashReqBy);
    if ($cashDisbBy !="") $stmt->bindValue(':financeRelease',$cashDisbBy);
    if ($DateFrom !="" && $DateTo !="") {
        $stmt->bindValue(':dateFrom',$DateFrom);
        $stmt->bindValue(':dateTo',$DateTo);
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

        $categoryTitle = '<span style="color:#b01c2e;">All</span>';
        if($cashCategory != ""){
            $categoryTitle = '<span style="color:#b01c2e;">'.$cashCategory.'</span>';
        }

        $budgetTitle = '<span style="color:#b01c2e;">All</span>';
        if($budget_category != ""){
            foreach ($data_array as $row1) {
                $budgetTitle = '<span style="color:#b01c2e;">'.$row1['budgetCategory'].'</span>';
            }
        }

        $statCheckTitle = '<span style="color:#b01c2e;">All</span>';
        if($statusCheck != ""){
            $statCheckTitle = '<span style="color:#b01c2e;">'.$statusCheck.'</span>';
        }

        $statusTitle = '<span style="color:#b01c2e;">All</span>';
        if($status != ""){
            $statusTitle = '<span style="color:#b01c2e;">'.$status.'</span>';
        }

        $reqByTitle = '<span style="color:#b01c2e;">All</span>';
        if($cashReqBy != ""){
            foreach ($data_array as $row1) {
                $reqByTitle = '<span style="color:#b01c2e;">'.$row1['req_by_surname'].' '.$row1['req_by_name'].'</span>';
            }
        }

        $reqDibTitle = '<span style="color:#b01c2e;">All</span>';
        if($cashDisbBy != ""){
            foreach ($data_array as $row1) {
                $reqDibTitle = '<span style="color:#b01c2e;">'.$row1['disb_by_surname'].' '.$row1['disb_by_name'].'</span>';
            }
        }

        $dateTitle = '<span style="color:#b01c2e;">All</span>';
        if( $DateFrom !="" && $DateTo !=""){
            $dateTitle = date("F j, Y ", strtotime($DateFrom)).' <b>To</b> '.date("F j, Y ", strtotime($DateTo));
        }

        $countryReport='';
        foreach ($data_array as $row1) {
            $countryReport = $row1['countryName'];
        }
            
        $output = '
            
        <div style="text-align:left;">
            <img src="../../../assets/images/smart_logo.jpg" width="80" >
            <p><b>SMART APPLICATIONS INTERNATIONAL - '.strtoupper($countryReport).'</b></p>
        </div>

        <h3 style="text-align:center; text-decoration:underline;">SERIS - PETTYCASH REQUESTS REPORT</h3> <br/><br/>
            
            
            <div>        
                <b>Requests Type:</b> '.$statCheckTitle.' <br/>
				<b>Date selected:</b> '.$dateTitle.' <br/><br/>
				<b>Country:</b> '.$countryTitle.' |
				<b>Department:</b> '.$departmentTitle.' |
				<b>Category:</b> '.$categoryTitle.' | 
				<b>Budget Line:</b> '.$budgetTitle.' | 
				<b>Status:</b> '.$statusTitle.' | 
				<b>Requested By:</b> '.$reqByTitle.' | 
				<b>Disbursed By:</b> '.$reqDibTitle.' <br/><br/>
                
				<b>Number of requests:</b> '.$num.' <br/><br/>            
            </div>
    
            <table border="1" cellspacing="0" cellpadding="5">  
                <tr style="font-weight:bold;">  
                    <th>Ref. Number</th>  
                    <th>Category</th> 
                    <th>Budget Line</th>
                    <th>Description</th> 
                    <th>Total amount</th>  
                    <th>Status</th>  
                    <th>Request by</th>  
                    <th>Department</th>  
                    <th>Request date</th> 
                    <th>Disburse date</th>  
                    <th>Disbursed by</th> 
                </tr>

                ';

                $tot_grand_total = 0;
                $currency = '';

                foreach ($data_array as $row) {
                    
                    $currency = $row['currency'];
                    $tot_grand_total = $tot_grand_total + $row["totalAmount"];

                    $releaseD = $row["financeReleaseDate"] ? $row["disb_by_surname"] . ' ' . $row["disb_by_name"] : 'N/A';
                    $userRel = $row["financeReleaseDate"] ? date("F j, Y g:i a", strtotime($row["financeReleaseDate"])) : 'N/A';
                    $budget_cat = $row["budgetCategory"] ? $row["budgetCategory"] : 'N/A';

                    $output .= 
                    '                         
                        <tr nobr="true">  
                            <td>'.$row["refNo"].'</td> 
                            <td>'.$row["category"].'</td> 	
                            <td>'.$budget_cat.'</td> 
                            <td>'.$row["description"].'</td> 					
                            <td>'.number_format($row["totalAmount"],2).' '.$row['currency'].' </td>  
                            <td>'.$row["status"].'</td>  
                            <td>'.$row["req_by_name"] .' '.$row["req_by_surname"].'</td> 
                            <td>'.$row["depart"].'</td> 
                            <td>'.date("F j, Y g:i a", strtotime($row['requestDate'])).'</td>
                            <td>'.$releaseD.'</td>  
                            <td>'.$userRel.'</td> 
                        </tr>  
                    ';
                }
                
                $output .= '

                <tr>
                    <td colspan="11"></td>
                </tr>
                <tr style="background-color:#dff1ff;">
                    <td colspan="2"><h3>TOTAL</h3></td>
                    <td colspan="9"><b style="color:#c90000;">'.number_format($tot_grand_total,2).' '.$currency.'</b></td>
                </tr>
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
                $this->Cell(0, 15, 'SERIS - PETTY CASH REPORT', 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
       $obj_pdf->SetTitle("SERIS - PETTYCASH REPORT");  
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
       
       $obj_pdf->Output('Pettycash_report.pdf', 'I');  
}  
?>