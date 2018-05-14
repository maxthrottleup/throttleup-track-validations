(function ($) {
    'use strict';

    $(document).ready(function () {

        var pathname = window.location.pathname;

        if (pathname.search('/track-1/') !== -1) {

            check_server_timer();

            setInterval(
                check_server_timer,
                60000
            );
        }

        if (pathname.search('/user-edit.php')) {

            var reset_timer_btn = $('#track1_reset_timer');

            reset_timer_btn.on('click', function () {
                $.ajax({
                    url: timer_object.ajax_url,
                    data: {
                        action: 'throttleup_reset_timer',
                        user_id: reset_timer_btn.attr('user_id')
                    },
                    method: 'POST',
                    success: function (response) {

                        if (!response.success) {
                            switch (response.data.error) {
                                case -1:
                                    alert('An error occurred on the information sent to the server. Please try again.');
                                    break;
                                case 0:
                                    alert('Timer was already reset for this user.');
                                    reset_score_track1();
                                    break;
                                default:
                                    break;
                            }
                            return;
                        }

                        alert('Timer has been reset for this user.');
                        reset_score_track1();
                    },
                    error: function (response) {
                        alert('There was an error on the server. Please try again later.');
                    }
                });
            });

            $( '#w30_track1' ).on( 'blur', function() {
                if ( $( this ).val() == '' ) {
                    alert( 'If you are resetting user data for Track 1, make sure you click the button "Reset Timer 1"' );
                }
            } );

        }

        function reset_score_track1() {
            $('#w30_track1').val('');
            $('#submit').click();
        }

    });

    $(window).load(function () {
        /*	Nothing Yet */
    });

    /* Additional functions */
    function check_server_timer() {
        $.ajax({
            url: timer_object.ajax_url,
            data: {
                action: 'throttleup_check_timer'
            },
            method: 'POST',
            success: function (response) {

                if (response.success) {
                    var start = parseInt(response.data.start);
                    var time = parseInt(response.data.time);
                    var finish = start + timer_object.track1_limit * 60;

                    if (time >= finish) {
                        jQuery('input[type="submit"]').click();
                        return;
                    }

                    var remaining = Math.floor((finish - time) / 60) + 1;

                    if (remaining == 45 || remaining == 5)
                        alert(remaining.toString() + ' minutes remaining.');

                    if (remaining <= 45)
                        $('#quizend').css('background-color', 'hsla(60, 100%, 70%, 1)');

                    if (remaining <= 5)
                        $('#quizend').css('background-color', 'hsla(0, 100%, 70%, 1)');

                    $('#quizend #time').text(remaining);
                }
                else {
                    switch (response.data.c) {
                        case -1:
                            console.log('Response: ', response.data.m);
                            alert('You are logged out. You will be redirected to log in again. Don\' worry, we have saved your answers.');
                            window.location = '/my-account';
                            break;
                        default:
                            break;
                    }
                }
            },
            error: function (response) {
                console.log(response);
            }
        });
    }

})(jQuery);