<?php
/*************
 * File for admin functions for CPJ Schedule Plugin
 * Author: Paul Jarvis
 * ver: 0.1.0
 * License: gpl. gnu
 */

 if(! defined ('WPINC')){
    die;
}

function cpj_appt_schedule_admin_enqueue($hook){
	
	wp_enqueue_script('cpj-calendar-schedule-admin-js', plugin_dir_url(__FILE__).'cpj-calendar-schedule-admin.js',array( 'jquery' ),'1.0.0',true);

	wp_enqueue_style('cpj-calendar-schedule-admin-css',  plugin_dir_url(__FILE__).'cpj-calendar-schedule-admin.css');

    $cpj_calendar_admin_nonce = wp_create_nonce( 'cpj_calendar_admin_nonce_cpj' );

    $localizeYesOrNo2 = wp_localize_script(
        'cpj-calendar-schedule-admin-js',
        'cpj_calendar_admin_ajax_obj',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    =>  $cpj_calendar_admin_nonce
        )
        );
}//end fx

add_action('admin_enqueue_scripts','cpj_appt_schedule_admin_enqueue');


add_action('admin_menu','cpj_appt_schedule_admin_menu');

function cpj_appt_schedule_admin_menu(){
    
    add_menu_page(
        'CPJ Appt Schedule Admin',
        'CPJ Appt Schedule Admin',
        'manage_options',
        'cpj-appt-schedule-admin-menu',
        'cpj_appt_schedule_admin_menu_page');


}


function cpj_appt_schedule_admin_menu_page(){

    global $wpdb;
    $emailContent = $wpdb->get_row("select * from wp_cpj_appt_email_content limit 1");

?>
<input type="hidden" name="cpj-schedule-email-admin-id" id="cpj-schedule-email-admin-id" value="<?php echo esc_attr($emailContent->id);?>">
<div class="schedule-email-optoins-outer-box">
    <h3>Schedule E-mail Options</h3>

    <div class="email-box-left">
        <p>Enter the e-mail you would like users to receive after scheduling an appointment. The keywords *cpj-first-name*, *cpj-date*, *cpj-time* are automatically populated with client's name and appointment time.</p>
        <div class="subject-line-box">
            <div>Subject:</div>
            <div>
        <input type="text" style="width:350px;" name="cpj-confirm-appt-subject" id="cpj-confirm-appt-subject" value="<?php echo esc_attr($emailContent->subject);?>">
        </div>
    </div> 

        <div class="editor-box">
<?php
 

                wp_editor($emailContent->email_content,'cpj-confirm-appt-email');

        ?>
        </div>
        <div class="email-content-submit-box">
            <input type="button" name="email-content-submit" id="email-content-submit" value="Submit">
        </div>
    </div>
    <div class="email-box-right" id="from-field-box">
    <p>E-mail From Address Display Name:</p>
        <div>
            <input type="text" name="appt-email-from-field-display-name" id="appt-email-from-field-display-name" value="<?php echo esc_attr($emailContent->from_field_display_name);?>">
        </div>
        <p>E-mail From Address:</p>
        <div>
            <input type="text" name="appt-email-from-field" id="appt-email-from-field" value="<?php echo esc_attr($emailContent->from_field);?>">
        </div>
        <div>
            <input type="button" name="appt-from-email-submit" id="appt-from-email-submit" value="submit">
        </div>

    </div> 
    <div class="email-box-right" id="notify-box">
        <p>E-mail where scheduling appointments notifications should be sent:</p>
        <div>
            <input type="text" name="notify-email" id="notify-email" value="<?php echo esc_attr($emailContent->notify_email);?>">
        </div>
        <div>
            <input type="button" name="notify-email-submit" id="notify-email-submit" value="submit">
        </div>
    </div>
</div>

<?php

}

add_action('wp_ajax_cpj_calendar_e-mail_admin', 'cpj_calendar_email_admin_handler');

function cpj_calendar_email_admin_handler(){

    global $wpdb;

      $doUpdate = $wpdb->update('wp_cpj_appt_email_content',
                                array(
                                    'email_content'=>sanitize_textarea_field($_POST['email-content']),
                                    'subject'=>sanitize_text_field($_POST['subject'])
                                ),
                                array(
                                    'id' => intval($_POST['id'])
                                ),
                                array('%s','%s'),
                                array('%d')
                            );
        $msg = ($doUpdate)?"Success, E-mail Content has been updated":"Error E-mail content was not updated";



        echo esc_html($msg);

 wp_die();

}

add_action('wp_ajax_cpj_calendar_e-mail_notify_admin', 'cpj_calendar_email_notify_admin_handler');

function cpj_calendar_email_notify_admin_handler(){

    global $wpdb;
   
    $doUpdate2 = $wpdb->update('wp_cpj_appt_email_content',
    array(
        'notify_email'=>sanitize_email($_POST['notify-email'])
    ),
    array(
        'id' => intval($_POST['id'])
    ),
    array('%s'),
    array('%d')
);
$msg = ($doUpdate2)?"Success, E-mail has been updated":"Error E-mail was not updated";

echo esc_html($msg);

wp_die();

}

add_action('wp_ajax_cpj_calendar_e-mail_from_admin', 'cpj_calendar_email_from_admin_handler');

function cpj_calendar_email_from_admin_handler(){

    global $wpdb;
  
    $doUpdate2 = $wpdb->update('wp_cpj_appt_email_content',
    array(
        'from_field'=>sanitize_email($_POST['from-email']),
        'from_field_display_name'=>sanitize_text_field($_POST['display-name'])
    ),
    array(
        'id' => intval($_POST['id'])
    ),
    array('%s','%s'),
    array('%d')
);

$msg = ($doUpdate2)?"Success, E-mail has been updated":"Error E-mail was not updated";

echo esc_html($msg);

wp_die();

}
