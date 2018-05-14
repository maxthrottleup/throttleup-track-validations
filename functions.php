<?php


/**
 * This is a shortcode that is called when the track 1 is complete. It takes the entry_id of the quiz and the score as
 * GET parameters. When the score is >= 80, the incorrect answers are shown. This shortcode also updates user statuses.
 *
 * @param GET parameters: entry_id and score
 *
 * @return string $html as a result of the shortcode execution
 */
function throttleup_track_1_complete() {

	if ( ! isset( $_GET['entry_id'] ) ) {
		ob_start(); ?>

        <p><h2>You can't access this page directly. Please take Track 1 and you will see your results</h2></p>

		<?php return ob_get_clean();
	}

	$html = '';

	if ( intval( get_user_meta( wp_get_current_user()->ID, 'w30_track1', true ) ) < 80 ) {
		ob_start(); ?>

        <p><h2>We are sorry! Your score
            was <?= get_user_meta( wp_get_current_user()->ID, 'w30_track1', true ) ?></h2></p>
        <p>Your score is not high enough to move on to Track 2. You are free to take the test again in 1 month with
            no additional fee. Please use the next month to become more involved in our community, and study our books
            and resources.</p>
        <p><em>Best, <br>Melissa and the Whole30 Coaching Team</em></p>

		<?php return ob_get_clean();
	} else {
		ob_start(); ?>

        <p><h2>You passed Track 1.
            Score: <?= intval( get_user_meta( wp_get_current_user()->ID, 'w30_track1', true ) ) ?></h2></p>
        <p>Congratulations! You can start Track 2 when you are ready.</p>
        <p><em>Best, <br>Melissa and the Whole30 Coaching Team</em></p>

		<?php $html .= ob_get_clean();
	}

	if ( intval( get_user_meta( wp_get_current_user()->ID, 'w30_track1', true ) ) != 100 ) {
		$html .= throttle_up_get_incorrect_answers( THROTTLEUP_TRACKS_TRACK_1_FORM, $_GET['entry_id'] );
	}

	return $html;

}

add_shortcode( 'throttleup-track-1-complete', 'throttleup_track_1_complete' );


/**
 * This function gets all the incorrect answers
 *
 * @param $form_id , $entry_id of the form that was submitted
 *
 * @return string with the HTML of the wrong answers or any error message
 */
function throttle_up_get_incorrect_answers( $form_id, $entry_id ) {

	$entry = GFAPI::get_entry( $entry_id );
	$form  = GFAPI::get_form( $form_id );

	if ( is_wp_error( $entry ) || is_wp_error( $form ) ) {
		return '<p><h2>There was an error. Please go back and try again.</h2></p>';
	}

	if ( intval( $entry['created_by'] ) != wp_get_current_user()->ID && ! current_user_can( 'administrator' ) ) {
		return '<p><h2>You don\'t have authorization to access the information of this entry</h2></p>';
	}

	if ( empty( $form['fields'] ) ) {
		return '<p><h2>It seems to be an error on the server. If you are seeing this please report to admin.</h2></p>';
	}

	$incorrect_answers = array();
	foreach ( $form['fields'] as $item ) {

		if ( $item->type != 'quiz' ) {
			continue;
		}

		foreach ( $item->choices as $choice ) {
			if ( isset( $choice['gquizIsCorrect'] ) &&
			     isset( $entry[ $item->id ] ) &&
			     $choice['gquizIsCorrect'] == true &&
			     $choice['value'] != $entry[ $item->id ]
			) {
				$incorrect_label = '';
				foreach ( $item->choices as $choice2 ) {
					if ( $choice2['value'] == $entry[ $item->id ] ) {
						$incorrect_label = $choice2['text'];
					}
				}

				$incorrect_answers[ $item->id ] = array(
					'q' => $item->label,
					'a' => $incorrect_label
				);
			}
		}
	}

	ob_start();
	?>

    <br>

	<?php if ( intval( get_user_meta( wp_get_current_user()->ID, 'w30_track1', true ) ) +
	           count( $incorrect_answers ) == 100 ) : ?>

        <p><h2 style="font-size:22px">These are the answers you got wrong:</h2></p>

		<?php foreach ( $incorrect_answers as $incorrect_answer ) : ?>

            <p style="font-size:16px">
				<?= $incorrect_answer['q'] ?>
                <br>
                <strong style="padding-left: 10px">Your answer was: </strong><?= $incorrect_answer['a'] ?>
            </p>

		<?php endforeach ?>

	<?php else : ?>

        <p><h2 style="font-size:22px">We are not able to show your wrong answers.</h2></p>

	<?php endif ?>

	<?php

	return ob_get_clean();
}


/**
 * Function to update the usermeta on form submission
 *
 * @param $entry Default argument of Gravity Forms
 * @param $form Default argument of Gravity Forms
 */
function throttleup_update_usermeta( $entry, $form ) {

	switch ( $entry['form_id'] ) {
		case 2:
//			if ( empty( get_user_meta( wp_get_current_user()->ID, 'w30_track1_last', true ) ) ) {
				update_user_meta( wp_get_current_user()->ID, 'w30_track1', $entry['gquiz_score'] );
				update_user_meta( wp_get_current_user()->ID, 'w30_track1_last', $entry['id'] );
//			} else {
//				GFAPI::delete_entry( $entry['id'] );
//				wp_redirect( get_site_url() . '/my-account' );
//				exit;
//			}
			break;
		case 4:
			update_user_meta( wp_get_current_user()->ID, 'w30_track2', 'Pending' );
			break;
		case 6:
			update_user_meta( wp_get_current_user()->ID, 'w30_track3', 'Pending' );
			break;
		default:
			break;
	}
}

add_action( 'gform_after_submission', 'throttleup_update_usermeta', 10, 2 );


function throttleup_redirects() {

	$current_page = get_queried_object();
	$current_user = wp_get_current_user();

	if ( $current_page->post_name == 'track-1' ) {

		/* Perform the time validation, if there is no time set, start the timer */
		$track1_timer_start = get_user_meta( $current_user->ID, 'track1_timer_start', true );

		if ( empty( $track1_timer_start ) ) {
		    $time = time();
			update_user_meta( $current_user->ID, 'track1_timer_start', $time );
			$track1_timer_start = $time;
		}

		if ( time() > intval( $track1_timer_start ) + THROTTLEUP_TRACK1_LIMIT_TIME * 60 ) {
			wp_redirect( get_site_url() . '/my-account' );
			exit;
		}

		/* Check if the I understand redirect applies */
		$search_criteria['field_filters'][] = array( 'key' => 'created_by', 'value' => wp_get_current_user()->ID );
		$entries = GFAPI::get_entries( THROTTLEUP_TRACKS_TRACK_1_FORM, $search_criteria );

		if ( ! empty( $entries ) && empty( $entries[0]['partial_entry_id'] ) ) {

			$search_criteria['field_filters'][] = array( 'key' => 'created_by', 'value' => wp_get_current_user()->ID );
			$count_entries                      = GFAPI::count_entries( THROTTLEUP_UNDERSTAND_FORM, $search_criteria );

			if ( $count_entries == 0 ) {
				wp_redirect( get_site_url() . '/my-account' );
				exit;
			}
		}
	}


	if ( $current_page->post_name == 'coach-documents-manager' ) {

		$w30_track4 = strtolower( get_user_meta( $current_user->ID, 'w30_track4', true ) );

		if ( ( $w30_track4 == '' || $w30_track4 == 'fail' || $w30_track4 == 'pending' ) && ! current_user_can( 'administrator' ) ) {
			wp_redirect( get_site_url() . '/my-account' );
			exit;
		}

	}

	return;

}

add_action( 'template_redirect', 'throttleup_redirects' );


function throttleup_debug() {
	$track1_timer_start = get_user_meta( wp_get_current_user()->ID, 'track1_timer_start', true );

	var_dump( '<pre>' );
	var_dump( $track1_timer_start );
	var_dump( '</pre>' );


	/* Check if the I understand redirect applies */
	$search_criteria['field_filters'][] = array( 'key' => 'created_by', 'value' => wp_get_current_user()->ID );
	$entries = GFAPI::get_entries( THROTTLEUP_TRACKS_TRACK_1_FORM, $search_criteria );

	var_dump( '<pre>' );
	var_dump( ! empty( $entries ) );
	var_dump( '</pre>' );

	if ( ! empty( $entries ) ) {

		$search_criteria['field_filters'][] = array( 'key' => 'created_by', 'value' => wp_get_current_user()->ID );
		$count_entries                      = GFAPI::count_entries( THROTTLEUP_UNDERSTAND_FORM, $search_criteria );

		if ( $count_entries == 0 ) {
			wp_redirect( get_site_url() . '/my-account' );
			exit;
		}
	}

	wp_send_json_success();
}

add_action( 'wp_ajax_throttleup_debug', 'throttleup_debug' );


function throttleup_check_timer() {

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'c' => - 1, 'm' => 'User is not logged in' ) );
	}

	$current_user       = wp_get_current_user();
	$track1_timer_start = get_user_meta( $current_user->ID, 'track1_timer_start', true );

	if ( empty( $track1_timer_start ) ) {
		wp_send_json_error();
	}

	wp_send_json_success( array( 'start' => $track1_timer_start, 'time' => time() ) );
}

add_action( 'wp_ajax_throttleup_check_timer', 'throttleup_check_timer' );
add_action( 'wp_ajax_nopriv_throttleup_check_timer', 'throttleup_check_timer' );

function throttleup_reset_timer() {

	$user_id = ( isset( $_POST['user_id'] ) ) ? $_POST['user_id'] : '';

	if ( empty( $user_id ) ) {
		wp_send_json_error( array( 'error' => - 1 ) );
	}

	$user_id = intval( $user_id );

	if ( delete_user_meta( $user_id, 'track1_timer_start' ) &&
	     delete_user_meta( $user_id, 'w30_track1_last' ) &&
	     delete_user_meta( $user_id, 'track1_last_partial' ) ) {
		wp_send_json_success();
	}

	wp_send_json_error( array( 'error' => 0 ) );
}

add_action( 'wp_ajax_throttleup_reset_timer', 'throttleup_reset_timer' );


function throttleup_enqueue_scripts() {
	wp_enqueue_script(
		'timer-js',
		plugins_url( 'throttleup-track-validations/timer.js' ),
		array( 'jquery' ),
		time(),
		true
	);

	wp_localize_script(
		'timer-js',
		'timer_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'track1_limit' => THROTTLEUP_TRACK1_LIMIT_TIME )
	);

	wp_enqueue_script(
		'ajax-js',
		plugins_url( 'throttleup-track-validations/ajax.js' ),
		array( 'jquery' ),
		time(),
		true
	);

	wp_localize_script(
		'ajax-js',
		'ajax_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);
}

add_action( 'wp_enqueue_scripts', 'throttleup_enqueue_scripts' );


function admin_throttleup_enqueue_scripts( $hook ) {
	if ( 'user-edit.php' != $hook ) {
		return;
	}

	wp_enqueue_script(
		'timer-js',
		plugins_url( 'throttleup-track-validations/timer.js' ),
		array( 'jquery' ),
		time(),
		true
	);

	wp_localize_script(
		'timer-js',
		'timer_object',
		array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'track1_limit' => THROTTLEUP_TRACK1_LIMIT_TIME )
	);
}

add_action( 'admin_enqueue_scripts', 'admin_throttleup_enqueue_scripts' );


/* Add the settings page */
function throttleup_settings_init() {
	// register a new setting for "general" page
	register_setting( 'general', 'throttleup_track1_time' );

	// register a new section in the "general" page
	add_settings_section(
		'throttleup_settings_section',
		'Settings for Coaches',
		'throttleup_settings_section_cb',
		'general'
	);

	// register a new field in the "throttleup_settings_section" section, inside the "general" page
	add_settings_field(
		'throttleup_settings_field',
		'Track 1 time (in minutes)',
		'throttleup_settings_field_cb',
		'general',
		'throttleup_settings_section'
	);
}

/**
 * register throttleup_settings_init to the admin_init action hook
 */
add_action( 'admin_init', 'throttleup_settings_init' );

/**
 * callback functions
 */

// section content cb
function throttleup_settings_section_cb() {
	echo '<p>Settings related to Coaches</p>';
}

// field content cb
function throttleup_settings_field_cb() {
	// get the value of the setting we've registered with register_setting()
	$setting = get_option( 'throttleup_track1_time' );
	$setting = isset( $setting ) ? intval( $setting ) : 90;
	// output the field
	?>
    <input type="text" name="throttleup_track1_time" value="<?= $setting ?>">
	<?php
}


/**
 * AJAX Callback Function to retrieve the last saved answers for the Track 1
 */
function throttleup_check_last_t1_entry() {

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'c' => - 3, 'm' => 'User is not logged in' ) );
	}

	$user = wp_get_current_user();

	/* Get the last entry of the current user */
	$entry_id = get_user_meta( $user->ID, 'track1_last_partial', true );

	if ( ! isset( $entry_id ) || empty( $entry_id ) ) {
		wp_send_json_error( array( 'c' => - 2, 'm' => 'Could not find a last entry' ) );
	}

	$entry = GFAPI::get_entry( $entry_id );

	if ( is_wp_error( $entry ) ) {
		wp_send_json_error( array( 'c' => - 2, 'm' => 'Could not find a last entry' ) );
	}

	$sanitized_results = array();

	for ( $i = 1; $i <= 100; $i ++ ) {

		if ( ! isset( $entry[ $i ] ) ) {
			continue;
		}

		$sanitized_results[] = array( 'n' => $i, 'v' => htmlspecialchars_decode( $entry[ $i ], ENT_HTML5 ) );

	}

	wp_send_json_success( $sanitized_results );
}

add_action( 'wp_ajax_throttleup_check_last_t1_entry', 'throttleup_check_last_t1_entry' );
add_action( 'wp_ajax_nopriv_throttleup_check_last_t1_entry', 'throttleup_check_last_t1_entry' );


/**
 * AJAX Callback Function to retrieve the last saved answers for the Track 1
 */
function throttleup_check_last_t2_entry() {

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'c' => - 3, 'm' => 'User is not logged in' ) );
	}

	$user = wp_get_current_user();

	/* Get the last entry of the current user */
	$entry_id = get_user_meta( $user->ID, 'track2_last_partial', true );

	if ( ! isset( $entry_id ) || empty( $entry_id ) ) {
		wp_send_json_error( array( 'c' => - 2, 'm' => 'Could not find a last entry' ) );
	}

	$entry = GFAPI::get_entry( $entry_id );

	if ( is_wp_error( $entry ) ) {
		wp_send_json_error( array( 'c' => - 2, 'm' => 'Could not find a last entry' ) );
	}

	$sanitized_results = array();

	for ( $i = 1; $i <= 15; $i ++ ) {

		if ( ! isset( $entry[ $i ] ) ) {
			continue;
		}

		$sanitized_results[] = array( 'n' => $i, 'v' => htmlspecialchars_decode( $entry[ $i ], ENT_HTML5 ) );

	}

	wp_send_json_success( $sanitized_results );
}

add_action( 'wp_ajax_throttleup_check_last_t2_entry', 'throttleup_check_last_t2_entry' );
add_action( 'wp_ajax_nopriv_throttleup_check_last_t2_entry', 'throttleup_check_last_t2_entry' );


/**
 * On partial
 */

function throttleup_partial_saved( $partial_entry, $form ) {

	if ( ! is_user_logged_in() ) {
		return;
	}

	/* Update last entry of the user meta */
	$user = wp_get_current_user();

	$form_id = intval( $partial_entry['form_id'] );

	switch ( $form_id ) {
		case THROTTLEUP_TRACKS_TRACK_1_FORM:
			update_user_meta( $user->ID, 'track1_last_partial', $partial_entry['id'] );
			break;
		case THROTTLEUP_TRACKS_TRACK_2_FORM:
			update_user_meta( $user->ID, 'track2_last_partial', $partial_entry['id'] );
			break;
		default:
			break;
	}
}

add_action( 'gform_partialentries_post_entry_saved', 'throttleup_partial_saved', 10, 2 );
add_action( 'gform_partialentries_post_entry_updated', 'throttleup_partial_saved', 10, 2 );


/**
 * The following shortcode created an I understand functionality
 */
function throttleup_shortcode_i_understand() {

	$search_criteria['field_filters'][] = array( 'key' => 'created_by', 'value' => wp_get_current_user()->ID );
//	$search_criteria['field_filters'][] = array( 'key' => 'partial_entry_percent', 'value' => '' );

	$entries = GFAPI::get_entries( THROTTLEUP_TRACKS_TRACK_1_FORM, $search_criteria );

	if ( ! empty( $entries ) && empty( $entries[0]['partial_entry_id'] ) ) {

		$last_date_created = date_create_from_format( 'Y-m-d H:i:s', $entries[0]['date_created'] );

		ob_start(); ?>

        <p>At this moment, you are eligible for a refund. By proceeding past this point, you are forfeiting your right
            to a refund. This will be your last attempt to pass Track 1 without incurring an additional fee. We
            recommend
            that you return to your studies for at least one month before re-taking the test. Your first test was taken
            on
			<?= $last_date_created->format( 'l F jS o' ) ?>.</p>

		<?php echo do_shortcode( '[gravityform id="' . THROTTLEUP_UNDERSTAND_FORM . '" title="false" description="false" ajax="true"]' ); ?>

		<?php return ob_get_clean();
	} else {
		ob_start(); ?>

        <script>
            jQuery(window).load(function () {
                jQuery('.tu-start-track-1').show();
            });
        </script>

		<?php return ob_get_clean();
	}

}

add_shortcode( 'i-understand', 'throttleup_shortcode_i_understand' );





