<?php

// =============================================
// CONFIGURATIONS
// =============================================

// Headers
// you can add more than one email address
$to_email_addresses       = array( 'mail@example.com' => 'Example' );
$cc_email_addresses       = array( 'mail@example.com' => 'Example' );
$bcc_email_addresses      = array( 'mail@example.com' => 'Example' );
$reply_to_email_addresses = array( 'mail@example.com' => 'Example' );

// Body
$subject_prefix = '';

// SMTP
// comment these lines if you want to use native PHP mail function instead of SMTP
// $is_smtp = true;
// $smtp_host = '';
// $smtp_host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
// $smtp_auth = true;                                   // Enable SMTP authentication
// $smtp_username = 'user@example.com';                 // SMTP username
// $smtp_password = 'secret';                           // SMTP password
// $smtp_Secure = 'tls';                                // Enable TLS encryption, `ssl` also accepted
// $smtp_port = 587;

// Validation messages
$error_messages  = array(
	'name'    => 'Please enter your name.',
	'email'   => 'Please enter your email address correctly.',
	'subject' => 'Subject is required.',
	'message' => 'Message is required.',
	'else'    => 'An error occured.',
);
$success_message  = 'Email sent successfully!';

// =============================================
// BEGIN SEND EMAIL PROCESS
// =============================================

$result['error'] = 0;

// Form's values
$name = isset( $_REQUEST['contact-name'] ) ? $_REQUEST['contact-name'] : '';
$email = isset( $_REQUEST['contact-email'] ) ? $_REQUEST['contact-email'] : '';
$subject = isset( $_REQUEST['contact-subject'] ) ? $_REQUEST['contact-subject'] : '';
$message = isset( $_REQUEST['contact-message'] ) ? $_REQUEST['contact-message'] : '';

// Validation
$errors = array();
if ( empty( $email ) || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) $errors[] = $error_messages['email'];
if ( empty( $name ) ) $errors[] = $error_messages['name'];
if ( empty( $subject ) ) $errors[] = $error_messages['subject'];
if ( empty( $message ) ) $errors[] = $error_messages['message'];

if ( count( $errors ) ) {
	$result['error'] = 1;
	$result['status'] = 'error';
	$result['message'] = implode( '<br />', $errors );

	// Send output
	if ( ! empty( $_REQUEST[ 'ajax' ] ) ) {
		// called via AJAX
		echo json_encode( $result );
	} else {
		// no AJAX
		echo 'Error: ' . '<br />' . $result['message'];
	}

	return;
}

// Initiate PHPMailer
require_once( 'class.phpmailer.php' );
$mail = new PHPMailer();

// If Using SMTP
if ( isset( $is_smtp ) && $is_smtp ) {
	$result['is_smtp'] = 1;
	$mail->isSTMP();
	$mail->Host = $smtp_host;
	$mail->SMTPAuth = $smtp_auth;
	$mail->Username = $smtp_username;
	$mail->Password = $smtp_password;
	$mail->SMTPSecure = $smtp_secure;
	$mail->Port = $smtp_port;
}

// headers
$mail->From = $email;
$mail->FromName = $name;
foreach ( $to_email_addresses as $e => $n ) $mail->addAddress( $e, $n );
foreach ( $cc_email_addresses as $e => $n ) $mail->addCC( $e, $n );
foreach ( $bcc_email_addresses as $e => $n ) $mail->addBCC( $e, $n );
foreach ( $reply_to_email_addresses as $e => $n ) $mail->addReplyTo( $e, $n );

// body
$mail->Subject = $subject_prefix . $subject;
$mail->Body    = $message;

// send
if ( ! $mail->send() ) {
	$result['status'] = 'error';
	$result['message'] = $error_messages['else'];
	$result['error'] = 1;
} else {
	$result['status'] = 'success';
	$result['message'] = $success_message;
}

// Send output
if ( ! empty( $_REQUEST[ 'ajax' ] ) ) {
	// called via AJAX
	echo json_encode( $result );
} else {
	// no AJAX
	if ( $result['status'] == 'error' ) {
		echo 'Error: ' . $mail->ErrorInfo;
	} else {
		echo 'Success';
	}
}