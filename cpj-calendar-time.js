/**
 * Plugin Name:       CPJ Calendar Scheduler
 * Description:       This block displays calendar to pick date then displays time available for booking
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Paul Jarvis
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cpj-calendar-schedule
 *
 * @package           cpj-calendar-schedule
 */

const ajaxLoaderGif = '<img id="cpj-ajax-loader-gif" src="'+cpj_calendar_ajax_obj.site_url + '/cpj-calendar-schedule/ajax-loader/ajax-loader.gif">';

jQuery(document).ready(function($){

 
            if($(".wp-block-cpj-calendar-schedule-cpj-calendar-schedule").length){

                let pCount = 0;
                const doLoad = setInterval(function(){
                    let lStr = "Loading";
                    pCount ++;
                    for(let c=0;c<=pCount;c++){
                        lStr += ".";
                    }
                   pCount = (pCount === 4)?0:pCount;
                    $(".cpj-calendar-box").html(lStr);
                    
                },500);

                const request = $.ajax({
					type: 'POST',
					url:cpj_ajax_obj.ajax_url,
					data: {'action' : 'cpj_calendar','cpj_calendar_time_nonce':cpj_calendar_ajax_obj.nonce},
					error: function(e){ console.log(e);},
					beforeSend: function(){
						}
					});

		request.done(function(msg){
            clearInterval(doLoad);
            $(".cpj-calendar-box").html(msg);
        });//end request done

     }//end if block exists

        $("body").on("click",".next-mon-btn",function(){
                 
            const newReq = $.ajax({
                type: 'POST',
                url:cpj_calendar_ajax_obj.ajax_url,
                data: {'action' : 'cpj_calendar',
                        'cpj_calendar_time_nonce':cpj_calendar_ajax_obj.nonce,
                        'cur-mon-num':$("#cur-mon-num").val(),
                        'mon-direction':'next'
                    },
                error: function(e){ console.log(e);},
                beforeSend: function(){
                    $(".cpj-calendar-box").html(ajaxLoaderGif);
                    }
                });

         newReq.done(function(msg){
                $(".cpj-calendar-box").html(msg);
         });//end request done


        });

        $("body").on("click",".prev-mon-btn",function(){
                    
            const newReqPrev = $.ajax({
                type: 'POST',
                url:cpj_calendar_ajax_obj.ajax_url,
                data: {'action' : 'cpj_calendar',
                        'cpj_calendar_time_nonce':cpj_calendar_ajax_obj.nonce,
                        'cur-mon-num':$("#cur-mon-num").val(),
                        'mon-direction':'prev'
                    },
                error: function(e){ console.log(e);},
                beforeSend: function(){
                    $(".cpj-calendar-box").html(ajaxLoaderGif);
                    }
                });

         newReqPrev.done(function(msg){
                $(".cpj-calendar-box").html(msg);
         });//end request done


        });

        $("body").on("click",".mon-days",function(){

            let dateStr = $("#cur-mon-name").val() +" "+$(this).html()+", "+$("#cur-year").val();
            let fullStr = '<div class="sel-date-display">' + dateStr + '</div>';

            $("#pick-a-date-holder").html(dateStr);

            const timeReq = $.ajax({
                type: 'POST',
                url:cpj_calendar_ajax_obj.ajax_url,
                data: {'action' : 'cpj_time',
                        'cpj_calendar_time_nonce':cpj_calendar_ajax_obj.nonce,
                        'cur-mon-num':$("#cur-mon-num").val(),
                        'cur-year':$("#cur-year").val(),
                        'cur-day-num':$(this).html()
                    },
                error: function(e){ console.log(e);},
                beforeSend: function(){
                    $(".cpj-time-box").html(ajaxLoaderGif);
                    }
                });

         timeReq.done(function(msg){
           $("#pick-a-time-text").show();
            $(".cpj-time-box").html(msg);
         });//end request done


        });

        $("body").on('click','.time-btn', function(){

            let selTime = $(this).html();
            $("#pick-a-time-holder").html(selTime);
            $(".time-btn").removeClass("time-btn-sel");
            $(this).addClass("time-btn-sel");

            if(!($(".next-btn").length)){
                $(".outer-time-box").append('<div class="next-btn">Next</div>');
                $(".next-btn").show('slow');
            }//ednm if
        });

        $("body").on("click",".next-btn",function(){
           
            const custFormReq = $.ajax({
                type: 'POST',
                url:cpj_calendar_ajax_obj.ajax_url,
                data: {'action' : 'cpj_cust_form',
                        'cpj_calendar_time_nonce':cpj_calendar_ajax_obj.nonce,
                        'sel-date_time':$(".time-btn-sel").val()
                    },
                error: function(e){ console.log(e);},
                beforeSend: function(){
                    $(".wp-block-cpj-calendar-schedule-cpj-calendar-schedule").html(ajaxLoaderGif);
                    }
                });

                custFormReq.done(function(msg){
           
                $(".wp-block-cpj-calendar-schedule-cpj-calendar-schedule").html(msg);
         });//end request done

        });

            $("body").on('click','#cpj-cust-form-submit-btn',function(){

                const custFormSubmit = $.ajax({
                    type: 'POST',
                    url:cpj_calendar_ajax_obj.ajax_url,
                    data: $("#cpj-cust-form").formSerialize(),
                    error: function(e){ console.log(e);},
                    beforeSend: checkCpjCustForm
                        });
    
                    custFormSubmit.done(function(msg){
               
                        $(".wp-block-cpj-calendar-schedule-cpj-calendar-schedule").html(msg);

                    });//end request done
    
            });

function checkCpjCustForm(){
            


            let isValid = true;
            let errorMsg = "";

            if(!($("#first-name").val().length)){
                $("#lbl-first-name").css('color','red');
                $("#first-name").css('border-color','red');
                errorMsg += '<div>Please enter your first name</div>';
            isValid = false;
            }
            if(!($("#last-name").val().length)){
                $("#lbl-last-name").css('color','red');
                $("#last-name").css('border-color','red');
                errorMsg += '<div>Please enter your last name</div>';
            isValid = false;
               
            }
            if(!($("#phone").val().length)){
                $("#lbl-phone").css('color','red');
                $("#phone").css('border-color','red');
                errorMsg += '<div>Please enter your phone number.</div>';
            isValid = false;
               
            }
            if(!($("#cpj-cust-form-email").val().length)){
                $("#lbl-email").css('color','red');
                $("#cpj-cust-form-email").css('border-color','red');
                 errorMsg += '<div>Please enter your e-mail.</div>';
            isValid = false;
               
            }

            $("#error-msg-box").html(errorMsg);

            if(isValid){
                $(".wp-block-cpj-calendar-schedule-cpj-calendar-schedule").html(ajaxLoaderGif);
            }

            return isValid;


}//end fx



});//end document ready