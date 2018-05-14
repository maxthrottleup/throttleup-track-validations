(function ($) {
    'use strict';

    $(document).ready(function () {

        var pathname = window.location.pathname;

        if (pathname.search('/track-1/') !== -1) {

            /* Load previous entry answers */
            $.ajax({
                url: ajax_object.ajax_url,
                data: {action: 'throttleup_check_last_t1_entry'},
                method: 'POST',
                success: function (response) {

                    if (!response.success) {

                        switch (response.data.c) {
                            case -1: console.log('Response: ', response.data.m); break;
                            case -2:

                                /* Set defaults */
                                for(var i = 1; i <= 100; i++) {
                                    $('#field_2_' + i + ' li[class^="gchoice_2_' + i + '"]').last().find('input').prop('checked', true);
                                }

                                break;
                            case -3: break;
                            default: console.log('Error: ', response.data); break;
                        }

                        return;
                    }

                    response.data.forEach(function (current_item) {

                        if (parseInt(current_item.n) > 100)
                            return;

                        var options = $('#field_2_' + current_item.n + ' li[class^="gchoice_2_' + current_item.n + '"]');

                        options.each(function () {

                            if ($(this).find('input').val() == current_item.v)
                                $(this).find('input').prop('checked', true);
                        });
                    });
                },
                error: function (response) {
                    console.log('Error: ', response);
                }
            });
        }

        if (pathname.search('/track-2/') !== -1) {

            /* Load previous entry answers */
            $.ajax({
                url: ajax_object.ajax_url,
                data: {action: 'throttleup_check_last_t2_entry'},
                method: 'POST',
                success: function (response) {

                    if (!response.success) {

                        switch (response.data.c) {
                            case -1: console.log('Error 1. Response: ', response.data.m); break;
                            case -2: console.log('Error 2. Response: ', response.data.m); break;
                            case -3: console.log('Error 3. Response: ', response.data.m); break;
                            case -4: console.log('Error 4. Response: ', response.data.m); break;
                            default: console.log('Unknown error: ', response.data); break;
                        }

                        return;
                    }

                    response.data.forEach(function (current_item) {
                        $('#input_4_' + current_item.n ).text( current_item.v );
                    });
                },
                error: function (response) {
                    console.log('Error: ', response);
                }
            });
        }

        if (pathname.search('/track-1-introduction/') !== -1) {

            $( document ).bind('gform_post_render', function(event, form_id, current_page) {
                $('.tu-start-track-1').show();
            });

            $( 'input[type="submit"]' ).on( 'click', function() {
                var confirm = $( '#input_9_1' ).val();

                if ( confirm.toLowerCase() != 'i understand' ) {
                    alert('Please type "I understand" on the required field.');
                    return false;
                }
                else {
                    $('#gform_9').submit();
                }

            } );

        }

    });

    $(window).load(function () {
        /*	Nothing Yet */
    });

})(jQuery);