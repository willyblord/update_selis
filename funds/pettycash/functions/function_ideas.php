<?php

function send_add_idea_email( $email_to, $to_name, $reply_email_to, $reply_name, $reply_surname, $idea_title)
{
	include( '../../../include/conn.php');
	$my=$_SESSION['userIdentification'];


	$body = '<html><head>';
    $body .=	'<title>'.$idea_title.'</title>';
    $body .=	'</head>';
    $body .=	' <body style="font-size:14px;">';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> has added a new idea to Innovation Hub. </p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/salis/login.php" target="_blank">Login</a> to SOMIS to view them.</p><br/>';		
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
    $body .= "</body></html>";
                    
    $status_unsent = "unsent";
    $statement = $db->prepare("INSERT INTO emails_to_send 
                                    (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
    $result = $statement->execute(
                array(
                    ':email_to'		=>	$email_to,
                    ':to_name'		=>	$to_name,
                    ':reply_email'	=>	$reply_email_to,
                    ':reply_name'	=>	$reply_name,
                    ':email_subject'=>	$idea_title,
                    ':body'			=>	$body,
                    ':status'		=>	$status_unsent,
                    ':user'			=>	$my
                )
            );
            if (!empty($result)) 
            {
                return true;
            }
}

function send_change_status_email( $email_to, $to_name, $reply_email_to, $reply_name, $reply_surname, $idea_id, $status, $idea_title)
{
	include( '../../../include/conn.php');
	$my=$_SESSION['userIdentification'];


	$body = '<html><head>';
    $body .=	'<title>'.$idea_title.'</title>';
    $body .=	'</head>';
    $body .=	' <body style="font-size:14px;">';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> has updated the status to <b>'.$status.'</b> for the idea (<b>IDEA_'.$idea_id.'</b>). </p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/salis/login.php" target="_blank">Login</a> to SOMIS to view it.</p><br/>';		
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
    $body .= "</body></html>";
                    
    $status_unsent = "unsent";
    $statement = $db->prepare("INSERT INTO emails_to_send 
                                    (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
    $result = $statement->execute(
                array(
                    ':email_to'		=>	$email_to,
                    ':to_name'		=>	$to_name,
                    ':reply_email'	=>	$reply_email_to,
                    ':reply_name'	=>	$reply_name,
                    ':email_subject'=>	$idea_title,
                    ':body'			=>	$body,
                    ':status'		=>	$status_unsent,
                    ':user'			=>	$my
                )
            );
            if (!empty($result)) 
            {
                return true;
            }
}

function send_change_priority_email( $email_to, $to_name, $reply_email_to, $reply_name, $reply_surname, $idea_id, $priority, $idea_title)
{
	include( '../../../include/conn.php');
	$my=$_SESSION['userIdentification'];


	$body = '<html><head>';
    $body .=	'<title>'.$idea_title.'</title>';
    $body .=	'</head>';
    $body .=	' <body style="font-size:14px;">';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Dear '.$to_name.',</p><br/>';									
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is to notify you that <b>'.$reply_name.' '.$reply_surname.'</b> has updated the priority to <b>'.$priority.'</b> for the idea (<b>IDEA_'.$idea_id.'</b>). </p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">You can <a href="https://data.smartapplicationsgroup.com/salis/login.php" target="_blank">Login</a> to SOMIS to view it.</p><br/>';		
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">This is an automated email alert.</p><br/>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Thank you,</p>';
    $body .= '<p style=" margin:auto; font-family:Trebuchet MS; color:#001919;">Smart Alerts.</p>';
    $body .= "</body></html>";
                    
    $status_unsent = "unsent";
    $statement = $db->prepare("INSERT INTO emails_to_send 
                                    (email_to, to_name, reply_email, reply_name, email_subject, body, status, user, insert_date) 
                                VALUES (:email_to, :to_name, :reply_email, :reply_name, :email_subject, :body, :status, :user, Now()) ");
    $result = $statement->execute(
                array(
                    ':email_to'		=>	$email_to,
                    ':to_name'		=>	$to_name,
                    ':reply_email'	=>	$reply_email_to,
                    ':reply_name'	=>	$reply_name,
                    ':email_subject'=>	$idea_title,
                    ':body'			=>	$body,
                    ':status'		=>	$status_unsent,
                    ':user'			=>	$my
                )
            );
            if (!empty($result)) 
            {
                return true;
            }
}



?>