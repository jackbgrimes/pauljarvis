/*************
 * JS File for admin JS functions for CPJ Schedule Plugin
 * Author: Paul Jarvis
 * ver: 0.1.0
 * License: gpl. gnu
 */

jQuery(document).ready(function($){

    $("#email-content-submit").click(function(){

       $('#cpj-confirm-appt-email').val(tinyMCE.activeEditor.getContent());
    
        if($("#cpj-confirm-appt-email").val().length){
   
            const myData = {
                        'cpj-calendar-admin-nonce':cpj_calendar_admin_ajax_obj.nonce,
                        'action':'cpj_calendar_e-mail_admin',
                        'email-content':$("#cpj-confirm-appt-email").val(),
                        'subject':$("#cpj-confirm-appt-subject").val(),
                        'id':$("#cpj-schedule-email-admin-id").val()
        }

            const requestAdmin = $.ajax({
                        type: 'POST',
                        url:cpj_calendar_admin_ajax_obj.ajax_url,
                        data: myData,
                        error: function(e){ console.log(e);},
                        beforeSend: function(){
                            }
                });
            requestAdmin.done(function(response){
                $("html, body").animate({ scrollTop: 0 }, "slow");

                $(".email-box-left").prepend(`<h3>${response}</h3>`);
            });

        }//end if

        });


    $("#notify-email-submit").click(function(){

        if($("#notify-email").val().length){

        const myData = {
                        'cpj-calendar-admin-nonce':cpj_calendar_admin_ajax_obj.nonce,
                        'action':'cpj_calendar_e-mail_notify_admin',
                        'notify-email':$("#notify-email").val(),
                        'id':$("#cpj-schedule-email-admin-id").val()
        }

            const requestNotifyAdmin = $.ajax({
                        type: 'POST',
                        url:cpj_calendar_admin_ajax_obj.ajax_url,
                        data: myData,
                        error: function(e){ console.log(e);},
                        beforeSend: function(){
                            }
                });
            requestNotifyAdmin.done(function(response){
                $("#notify-box").prepend(`<h3>${response}</h3>`);
            });

        }//end if

    });

    $("#appt-from-email-submit").click(function(){

        let fieldNotEmpty = $("#appt-email-from-field-display-name").val().length + $("#appt-email-from-field").val().length;

        if(fieldNotEmpty > 0){
            const myData = {
                'cpj-calendar-admin-nonce':cpj_calendar_admin_ajax_obj.nonce,
                'action':'cpj_calendar_e-mail_from_admin',
                'from-email':$("#appt-email-from-field").val(),
                'display-name':$("#appt-email-from-field-display-name").val(),
                'id':$("#cpj-schedule-email-admin-id").val()
}

    const requestFromAdmin = $.ajax({
                type: 'POST',
                url:cpj_calendar_admin_ajax_obj.ajax_url,
                data: myData,
                error: function(e){ console.log(e);},
                beforeSend: function(){
                    }
        });
    requestFromAdmin.done(function(response){
        $("#from-field-box").prepend(`<h3>${response}</h3>`);
    });
   
        }

    });


});//end document.ready