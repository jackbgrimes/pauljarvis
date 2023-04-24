<?php
/**
 * Plugin Name:       CPJ Calendar Appointment Scheduler
 * Description:       This plugin creates a block that can be added to any page or post. The block creates a calendar with selectable dates which then display a list of times that are selectable and then collects contact information. On successful collection of date, time and contact information, en e-mail is sent to customer confirming appointment date and an e-mail is sent to admin notifying user of new appointment. E-mail content and notify email address are customizable via admin options page.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.2
 * Author:            Paul Jarvis
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cpj-calendar-schedule
 *
 * @package           cpj-calendar-schedule
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

 if(! defined ('WPINC')){
    die;
}


include("cpj-schedule-admin.php");

function cpj_calendar_schedule_cpj_calendar_schedule_block_init() {
	register_block_type( __DIR__ . '/build' );


}
add_action( 'init', 'cpj_calendar_schedule_cpj_calendar_schedule_block_init' );

function cpj_calendar_time_enqueue($hook){
	
	$cpj_calendar_time_nonce = wp_create_nonce( 'cpj_calendar_time-nonce-cpj' );

	wp_enqueue_script('cpj_calendar_time-js', plugins_url('/cpj-calendar-time.js',__FILE__),array( 'jquery','jquery-form' ),'1.0.0',true);

	wp_enqueue_style('cpj_calendar_time-css',  plugins_url('/cpj-calendar-time.css',__FILE__));

	$localizeYesOrNo = wp_localize_script(
	'cpj_calendar_time-js',
	'cpj_calendar_ajax_obj',
	array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => $cpj_calendar_time_nonce,
		'site_url' => plugins_url()
	)
	);

	
}//end fx

add_action('wp_enqueue_scripts', 'cpj_calendar_time_enqueue');

register_activation_hook(__FILE__,'cpj_schedule_db_setup');

function cpj_schedule_db_setup(){

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    global $wpdb;

    $tblName = 'wp_cpj_appt_schedule';

    $sql = "CREATE TABLE " . $tblName . " (
            id int NOT NULL AUTO_INCREMENT,
            user_id int not null,
            appt_date DATE not null,
            appt_time TIME not null,
            type varchar(255),
            mktime varchar(255),
			PRIMARY KEY (id)
			);";
    
    maybe_create_table($tblName, $sql);

	$emailTbl = 'wp_cpj_appt_email_content';

	$emailSql = "CREATE TABLE " . $emailTbl . "(
				id int not null AUTO_INCREMENT,
				from_field varchar(255),
				email_content text,
				subject varchar(255) not null,
				notify_email varchar(255),
				from_field_display_name varchar(255),
				PRIMARY KEY (id)
			);";
	
	$isOk = maybe_create_table($emailTbl, $emailSql);

	if($isOk){
		
				$adminEmail = get_option('admin_email');
				$blogName = get_option('blogname');
				$emailPlaceHolder = "no-reply@example.com";
				$subjectPlaceHolder = "Thank you for your appointment request.";
				$contentPlaceHolder = "*cpj-first-name*,<p>Thank you for your appointment request.</p><p>Your appointment is scheduled for *cpj-date* at *cpj-time*.</p>";


		$wpdb->insert(
					$emailTbl,array(
						'notify_email'=>$adminEmail,
						'from_field'=>$emailPlaceHolder,
						'email_content'=>$contentPlaceHolder,
						'subject'=>$subjectPlaceHolder,
						'from_field_display_name'=>$blogName),
						array('%s','%s','%s','%s','%s')
			);
	
	}//end i

	$tblName = 'wp_cpj_appt_users';

    $sql = "CREATE TABLE " . $tblName . " (
            id int NOT NULL AUTO_INCREMENT,
            first_name varchar(255) not null,
			last_name varchar(255) not null,
			phone varchar(255) not null,
			email varchar(255) not null,
			pref varchar(255),
			PRIMARY KEY (id)
			);";
	maybe_create_table($tblName, $sql);

	
}


add_action('wp_ajax_cpj_calendar', 'cpj_calendar_handler');
add_action('wp_ajax_nopriv_cpj_calendar', 'cpj_calendar_handler');

function cpj_calendar_handler(){


		
	check_ajax_referer('cpj_calendar_time-nonce-cpj','cpj_calendar_time_nonce');

	$curMonNum = intval($_POST['cur-mon-num']);
	$showToday = false;
	$monDir = sanitize_text_field($_POST['mon-direction']);

	if($monDir === 'next'){

		 if($curMonNum === 12){
			$newMon = 1;
			$newYear = date("Y") + 1;
		}
		else{
			$newMon = $curMonNum + 1;
			$newYear = date('Y');
		}
		
		$today = getdate(mktime(0,0,0,$newMon,1,$newYear));
		$numOfDaysInMonth = date("t",mktime(0,0,0,$newMon,1,$newYear));
	}
	else if($monDir === 'prev'){
	
		if($curMonNum === 1){
			$newMon = 12;
			$newYear = date("Y") - 1;
		}
		else{
			$newMon = $curMonNum - 1;
			$newYear = date('Y');
		}
		
		$today = getdate(mktime(0,0,0,$newMon,1,$newYear));
		$numOfDaysInMonth = date("t",mktime(0,0,0,$newMon,1,$newYear));

	}
	else{
		$showToday = true;

		$today = getdate();
		$numOfDaysInMonth = date("t");
	}

	$firstDayOfMonth = date("w", mktime(0,0,0,$today['mon'],1,$today['year']));	

	$dayArr = ['Sun','Mon','Tues','Wed','Thu','Fri','Sat'];
?>

<input type="hidden" id="cur-mon-num" value="<?php echo esc_attr($today['mon']);?>">
<input type="hidden" id="cur-mon-name" value="<?php echo esc_attr($today['month']);?>">
<input type="hidden" id="cur-year" value="<?php echo esc_attr($today['year']);?>">

	<div class="calendar-header">
		<div class="prev-mon-btn"><<</div>
		<div class="mon-title"><?php echo esc_html($today['month']) . " " . esc_html($today['year']);?></div>
		<div class="next-mon-btn">>></div>
</div>
<table class="cal-table">
<tr class="dow-header">

<?php

	for($m=0; $m < 8;$m++){
echo '
		<td class="dow-title">'.esc_html($dayArr[$m]).'</td>
';

	}//end for loop
echo '
</tr>
<tr class="mon-days-box">
';

	
	$startNum = 1 - $firstDayOfMonth;
	$weekCounter = 0;

	for($i=$startNum; $i<=$numOfDaysInMonth; $i++){
	$weekCounter ++;

	echo '<td>';

	if($i<=0){
		echo "<div>&nbsp;</div>";
	}
	else if(($i == $today['mday'])&&($showToday)){

		echo '<div class="mon-days mon-days-today">'.esc_html($i).'</div>';

	}
	else{

		echo '<div class="mon-days">'.esc_html($i).'</div>';

	}

	echo '</td>';

	if($weekCounter === 7){
		echo '</tr><tr>';
		$weekCounter = 0;
	}	
}//end for loop

echo "</tr></table>";
/**/
	wp_die();

}

add_action('wp_ajax_cpj_time', 'cpj_time_handler');
add_action('wp_ajax_nopriv_cpj_time', 'cpj_time_handler');

function cpj_time_handler(){


	global $wpdb;


			$selMon = intval($_POST['cur-mon-num']);
			$selDay = intval($_POST['cur-day-num']);
			$selYear = intval($_POST['cur-year']);

			$nineAm = mktime(9,0,0,$selMon,$selDay,$selYear);

			$nineAmStr = date('g:i A D F j, Y',$nineAm);

			
			$sqlDate = $selYear . "-" . str_pad($selMon,2,"0",STR_PAD_LEFT) . "-" . str_pad($selDay,2,"0",STR_PAD_LEFT);
			$sqlStr = "select mktime from wp_cpj_appt_schedule where appt_date = '".$sqlDate."'";
			

			$apptTimeArr = $wpdb->get_results($sqlStr, ARRAY_N);


			$bookedTimeArr = array();

			for($p=0;$p<count($apptTimeArr);$p++){
				$bookedTimeArr[] = $apptTimeArr[$p][0];
			}//emnd for
			
			for($t = 9; $t < 17; $t++){
				 
				$timeNum = mktime($t,0,0,$selMon,$selDay,$selYear);
				$timeStr = date('g:i A',$timeNum);
				
			
				if(in_array($timeNum,$bookedTimeArr)){

					$disabledClass = "time-btn-disabled";
				
				}
					else{
				
						$disabledClass = "time-btn";
				
					}
					

				?>
				
				<button class="<?php echo esc_attr($disabledClass);?>" value="<?php echo esc_attr($timeNum);?>"><?php echo esc_html($timeStr);?></button>
				
				<?php

				$timeNum = mktime($t,30,0,$selMon,$selDay,$selYear);
				$timeStr = date('g:i A',$timeNum);

				if(in_array($timeNum,$bookedTimeArr)){

					$disabledClass = "time-btn-disabled";
				
				}
					else{
				
						$disabledClass = "time-btn";
				
					}
				
				?>
				
				<button class="<?php echo esc_attr($disabledClass);?>" value="<?php echo esc_attr($timeNum);?>"><?php echo esc_html($timeStr);?></button>
				
				<?php

			}//end for


wp_die();

}

add_action('wp_ajax_cpj_cust_form', 'cpj_cust_form_handler');
add_action('wp_ajax_nopriv_cpj_cust_form', 'cpj_cust_form_handler');

function cpj_cust_form_handler(){
	
	check_ajax_referer('cpj_calendar_time-nonce-cpj','cpj_calendar_time_nonce');

$sel_date_time = intval($_POST['sel-date_time']);

?>
	<div id="cpj-cust-form-box">
		<div class="cpj-cust-form-title">Enter Your Information:</div>
		<div id="error-msg-box"></div>
		<form id="cpj-cust-form" method="post">
			<input type="hidden" name="sel-date-time" id="sel-date-time" value="<?php echo esc_attr($sel_date_time);?>">
			<input type="hidden" name="action" value="cpj_cust_form_submit">
			<div class="first-name-row form-row">
				<label for="first-name" id="lbl-first-name">First Name:</label>
				<div class="input-box">
					<input type="text" name="first-name" id="first-name" class="cust-form-input" value="">
				</div>
			</div>
			<div class="last-name-row form-row">
				<label for="last-name" id="lbl-last-name">Last Name:</label>
				<div class="input-box">
					<input type="text" name="last-name" id="last-name" class="cust-form-input" value="">
				</div>
			</div>
			<div class="phone-row form-row">
				<label for="phone" id="lbl-phone">Phone:</label>
				<div class="input-box">
					<input type="text" name="phone" id="phone" class="cust-form-input" value="">
				</div>
			</div>
			<div class="email-row form-row">
				<label for="cpj-cust-form-email" id="lbl-email">E-mail:</label>
				<div class="input-box">
					<input type="text" name="cpj-cust-form-email" id="cpj-cust-form-email" class="cust-form-input" value="">
				</div>
			</div>
			<div class="pref-row form-row">
				<label for="pref">Preferred Contact Method:</label>
				<div class="input-box">
					<select name="pref">
						<option value="Phone">Phone</option>
						<option value="E-mail">E-Mail</option>
						<option value="Text">Text</option>
					</select>
				</div>
			</div>
			<div class="submit-row form-row">
				<input type="button" name="submit" id="cpj-cust-form-submit-btn" value="Submit">
			</div>
		</form>
	</div>


<?php

wp_die();

}//end fx

add_action('wp_ajax_cpj_cust_form_submit', 'cpj_cust_form_submit_handler');
add_action('wp_ajax_nopriv_cpj_cust_form_submit', 'cpj_cust_form_submit_handler');

function cpj_cust_form_submit_handler(){

	//do db stuff here
	global $wpdb;

	$selDateTime = intval($_POST['sel-date-time']);

	$wpdb->insert(
				'wp_cpj_appt_users',
				array(
					'email' => sanitize_email($_POST['cpj-cust-form-email']),
					'first_name' => sanitize_text_field($_POST['first-name']),
					'last_name' => sanitize_text_field($_POST['last-name']),
					'phone' => sanitize_text_field($_POST['phone']),
					'pref' => sanitize_text_field($_POST['pref'])
				),
				array(
					'%s','%s','%s','%s','%s'
				)
			);
			
			$userId = $wpdb->insert_id;

			$wpdb->insert(
				'wp_cpj_appt_schedule',
					array(
						'user_id'=>$userId,
						'appt_date'=>date('Y-m-d',$selDateTime),
						'appt_time'=>date('H:i:s',$selDateTime),
						'mktime'=>intval($_POST['sel-date-time'])
					),
					array('%d','%s','%s','%d')
				);


//do email stuff here
$rowSql = "select * from wp_cpj_appt_email_content limit 1";

$emailRow = $wpdb->get_row($rowSql);

$apptDate = date('D F j,Y',$selDateTime);
$apptTime = date('g:i A',$selDateTime);

$firstName = sanitize_text_field($_POST['first-name']);

$firstRun = str_replace('*cpj-first-name*',$firstName,$emailRow->email_content);
$secRun = str_replace('*cpj-date*',$apptDate,$firstRun);
$confirmEmailText = str_replace('*cpj-time*',$apptTime,$secRun);

$toEmail = sanitize_email($_POST['cpj-cust-form-email']);

$confirmSubject = $emailRow->subject;

$headers = "MIME-Version: 1.0" . "\r\n"; 
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
 
$headers .= 'From: '.$emailRow->from_field_display_name . '<'. $emailRow->from_field . '>' . "\r\n"; 

$didSend = mail($toEmail, $confirmSubject, $confirmEmailText, $headers);

if(!($didSend)){
	echo "problem sending e-mail";
}

$notifyTo = $emailRow->notify_email;

$notifySubject = "A new appointment request!";
$notifyText = "A new request for appointment has been submitted.\n";
$notifyText .= "Date: ". $apptDate ."\n" . "Time: " . $apptTime . "\n";
$notifyText .= "Name: " . $firstName . " " . sanitize_text_field($_POST['last-name']) . "\n" . "Phone: ".sanitize_text_field($_POST['phone'])."\n"."E-mail: ".$toEmail."\n";
$notifyText .= "Prefered contact method: ". sanitize_text_field($_POST['pref']) ."\n";

$didSend2 = mail($notifyTo, $notifySubject, $notifyText, 'From: ' . $emailRow->from_field);

if(!$didSend2){echo "problem sending e-mail-2";}

	//do date stuff here

	$displayTime = date('g:i a',$selDateTime);
	$displayDate = date('l, F j Y',$selDateTime);
	?>
	<div id="confirm-msg-box">
		<h4>Thank you <?php echo esc_html($_POST['first-name']) ." ".esc_html($_POST['last-name']);?></h4>
		<p>We look forward to meeting you at <?php echo esc_html($displayTime);?> 
		on <?php echo esc_html($displayDate);?>.</p>
		<p>You will receive an e-mail to confirm and give you instructions for our meeting</p>
</div>


<?php

wp_die();	

} //end fx
