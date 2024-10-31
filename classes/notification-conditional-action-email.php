<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class for conditional action email type.
 *
 * @package     NF Conditional Action Email
 * @subpackage  Classes/Actions
 * @copyright   Copyright (c) 2016, macnetic-labs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

class NF_Action_Conditional_Action_Email extends NF_Notification_Base_Type
{

	/**
	 * Get things rolling
	 */
	function __construct() {
		$this->name = __( 'Conditional Email', 'nf-conditional-actions' );
	}

	/**
	 * Output our edit screen
	 *
	 * @access public
	 * @since 2.8
	 * @return void
	 */
	public function edit_screen( $id = '' ) {
		$form_id = ( '' != $id ) ? Ninja_Forms()->notification( $id )->form_id : '';
        $prefix = 'email_';
		if ( $id == '' ) {
			$email_format = 'html';
			$from_name = get_bloginfo( 'name' );
			$from_address = get_bloginfo( 'admin_email' );
			$reply_to = '';
			$to = '';
			$cc = '';
			$bcc = '';
			$email_subject = '';
            $default_conditional_email_message = '';
            $conditional_conditions = array();
            $conditional_email_messages = array();
		} else {
			$email_format = Ninja_Forms()->notification( $id )->get_setting( 'conditional_email_format' );
			$from_name = Ninja_Forms()->notification( $id )->get_setting( 'conditional_from_name' );
			$from_address = Ninja_Forms()->notification( $id )->get_setting( 'conditional_from_address' );
			$reply_to = Ninja_Forms()->notification( $id )->get_setting( 'conditional_reply_to' );
			$to = Ninja_Forms()->notification( $id )->get_setting( 'conditional_to' );
			$cc = Ninja_Forms()->notification( $id )->get_setting( 'conditional_cc' );
			$bcc = Ninja_Forms()->notification( $id )->get_setting( 'conditional_bcc' );
			$email_subject = Ninja_Forms()->notification( $id )->get_setting( 'conditional_email_subject' );
            $default_conditional_email_message = Ninja_Forms()->notification( $id )->get_setting( 'default_conditional_email_message' );
            $conditional_conditions = unserialize(Ninja_Forms()->notification( $id )->get_setting( 'conditional_email_message_conditions' ));
            $conditional_email_messages = unserialize(Ninja_Forms()->notification( $id )->get_setting( 'conditional_email_messages' ));
		}

		$conditional_conditions;

        $length = 0;
        if(is_array($conditional_conditions)) {
            $length = count($conditional_conditions);
        }

		?>
		<tr>
			<th scope="row"><label for="settings-conditional_from_name"><?php _e( 'From Name', 'ninja-forms' ); ?></label></th>
			<td>
				<input name="settings[conditional_from_name]" type="text" id="settings-conditional_from_name" value="<?php echo $from_name; ?>" class="nf-tokenize" placeholder="<?php _e( 'Name or fields', 'ninja-forms' ); ?>" data-token-limit="0" data-key="conditional_from_name" data-type="all" />
				<span class="howto"><?php _e( 'Email will appear to be from this name.', 'ninja-forms' ) ?></span>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="settings-conditional_from_address"><?php _e( 'From Address', 'ninja-forms' ); ?></label></th>
			<td>
				<input name="settings[conditional_from_address]" type="text" id="settings-conditional_from_address" value="<?php echo $from_address; ?>" class="nf-tokenize" placeholder="<?php _e( 'One email address or field', 'ninja-forms' ); ?>" data-token-limit="1" data-key="conditional_from_address" data-type="all" />
				<span class="howto"><?php _e( 'Email will appear to be from this email address.', 'ninja-forms' ) ?></span>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="settings-to"><?php _e( 'To', 'ninja-forms' ); ?></label></th>
			<td>
				<input name="settings[conditional_to]" type="text" id="settings-conditional_to" value="<?php echo $to; ?>" class="nf-tokenize" placeholder="<?php _e( 'Email addresses or search for a field', 'ninja-forms' ); ?>" data-token-limit="0" data-key="conditional_to" data-type="all" />
				<span class="howto"><?php _e( 'Who should this email be sent to?', 'ninja-forms' ) ?></span>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="settings-conditional_email_subject"><?php _e( 'Subject', 'ninja-forms' ); ?></label></th>
			<td>
				<input name="settings[conditional_email_subject]" type="text" id="settings-conditional_email_subject" value="<?php echo $email_subject; ?>" class="nf-tokenize" placeholder="<?php _e( 'Subject Text or search for a field', 'ninja-forms' ); ?>" data-token-limit="0" data-key="conditional_email_subject" data-type="all" />
				<span class="howto"><?php _e( 'This will be the subject of the email.', 'ninja-forms' ) ?></span>
			</td>
		</tr>
        <tr>
            <th scope="row"><label for="settings-default_conditional_email_message"><?php _e( 'Standard', 'nf-conditional-actions' ); ?></label></th>
            <td>
                <?php wp_editor($default_conditional_email_message, 'settings-default_conditional_email_message', array('textarea_name' => 'settings[default_conditional_email_message]') ); ?>
                <input type="hidden" name="settings-conditional_email_message_length" value="<?php echo $length ?>" id="settings-conditional_email_message_length" />
            </td>
        </tr>
        <?php
        if($length > 0) {
            foreach ($conditional_conditions as $i => $conditional_condition) {
                self::nf_get_condition_fields($i, $conditional_conditions[$i], $conditional_email_messages[$i]);
            }
        }
        ?>
        <tr>
            <th scope="row">&nbsp;</th>
            <td>
                <input type="button" id="nf_addConditionButton" class="nf_addConditionButton" value="<?php _e('Add', 'nf-conditional-actions'); ?>" data-prefix="email_message" />
            </td>
        </tr>
		<tr>
			<th scope="row"><label for="settings-attachments"><?php _e( 'Attachments', 'ninja-forms' ); ?></label></th>
			<td>
				<ul>
					<?php
					$attachment_types = apply_filters( 'nf_email_notification_attachment_types',
						array(
							'attach_csv' => __( 'Submission CSV', 'ninja-forms' ),
						)
					);

					foreach ( $attachment_types as $slug => $nicename ) {
						if ( '' == $id ) {
							$current_setting = 0;
						} else {
							$current_setting = Ninja_Forms()->notification( $id )->get_setting( $slug );
						}

						?>
						<li>
							<input name="settings[<?php echo $slug; ?>]" type="hidden" value="0">
							<input name="settings[<?php echo $slug; ?>]" type="checkbox" id="settings-<?php echo $slug; ?>" value="1" <?php checked( $current_setting, 1 ); ?>/>
							<label for="settings-<?php echo $slug; ?>"><?php echo $nicename; ?></label>
						</li>

						<?php
					}
					?>
				</ul>
			</td>
		</tr>
		<tr>
			<th scope="row"><a href="#" id="toggle_email_advanced"><?php _e( 'Advanced Settings', 'ninja-forms' ); ?></a></th>
		</tr>
		<tbody id="email_advanced" style="display:none;">
			<tr>
				<th scope="row"><label for="settings-email_format"><?php _e( 'Format', 'ninja-forms' ); ?></label></th>
				<td>
					<select name="settings[email_format]" id="settings-email_format" data-key="email_format"/>
						<option value="html" <?php selected( $email_format, 'html' ); ?>><?php _e( 'HTML', 'ninja-forms' ); ?></option>
						<option value="plain" <?php selected( $email_format, 'plain' ); ?>><?php _e( 'Plain Text', 'ninja-forms' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="settings-conditional_reply_to"><?php _e( 'Reply To', 'ninja-forms' ); ?></label></th>
				<td>
					<input name="settings[conditional_reply_to]" type="text" id="settings-conditional_reply_to" value="<?php echo $reply_to; ?>" class="nf-tokenize" placeholder="<?php __( 'One email address or field', 'ninja-forms' ); ?>" data-token-limit="1" data-key="conditional_reply_to" data-type="all" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="settings-conditional_cc"><?php _e( 'Cc', 'ninja-forms' ); ?></label></th>
				<td>
					<input name="settings[conditional_cc]" type="text" id="settings-conditional_cc" value="<?php echo $cc; ?>" class="nf-tokenize" placeholder="<?php __( 'Email addresses or search for a field', 'ninja-forms' ); ?>" data-token-limit="0" data-key="conditional_cc" data-type="all" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="settings-conditional_bcc"><?php _e( 'Bcc', 'ninja-forms' ); ?></label></th>
				<td>
					<input name="settings[conditional_bcc]" type="text" id="settings-conditional_bcc" value="<?php echo $bcc; ?>" class="nf-tokenize" placeholder="<?php __( 'Email addresses or search for a field', 'ninja-forms' ); ?>" data-token-limit="0" data-key="conditional_bcc" data-type="all" />
				</td>
			</tr>
		</tbody>
		<?php

		do_action( 'nf_email_notification_after_settings', $id );

		if ( '' != $form_id ) {
			$from_name = $this->get_value( $id, 'conditional_from_name', $form_id );
			$from_address = $this->get_value( $id, 'conditional_from_address', $form_id );
			$reply_to = $this->get_value( $id, 'conditional_reply_to', $form_id );
			$to = $this->get_value( $id, 'conditional_to', $form_id );
			$cc = $this->get_value( $id, 'conditional_cc', $form_id );
			$bcc = $this->get_value( $id, 'conditional_bcc', $form_id );
			$email_subject = $this->get_value( $id, 'conditional_email_subject', $form_id );
		} else {
			$from_name = get_bloginfo( 'name' );
			$from_address = get_bloginfo( 'admin_email' );
			$reply_to = '';
			$to = '';
			$cc = '';
			$bcc = '';
			$email_subject = '';
		}

		?>

		<script type="text/javascript">
				nf_notifications.tokens['conditional_from_name'] = <?php echo json_encode( $from_name ); ?>;
				nf_notifications.tokens['conditional_from_address'] = <?php echo json_encode( $from_address ); ?>;
				nf_notifications.tokens['conditional_reply_to'] = <?php echo json_encode( $reply_to ); ?>;
				nf_notifications.tokens['conditional_to'] = <?php echo json_encode( $to ); ?>;
				nf_notifications.tokens['conditional_cc'] = <?php echo json_encode( $cc ); ?>;
				nf_notifications.tokens['conditional_bcc'] = <?php echo json_encode( $bcc ); ?>;
				nf_notifications.tokens['conditional_email_subject'] = <?php echo json_encode( $email_subject ); ?>;
		</script>

		<?php
	}

    private static function nf_get_condition_fields($i, $condition = "", $message = "") {
        ?>
        <tr>
            <th scope="row">
                <label for="settings-conditional_email_condition_<?php echo $i; ?>">
                    <?php _e('Condition', 'nf-conditional-actions'); ?>

                    <input type="button" id="nf_removeConditionButton_<?php echo $i; ?>"
                           class="nf_removeConditionButton" value="<?php _e('Remove', 'nf-conditional-actions'); ?>"/>
                </label>
            </th>
            <td>
                <?php wp_editor(htmlspecialchars_decode($condition), 'settings-conditional_email_message_condition_' . $i, array('textarea_name' => 'settings[conditional_email_message_conditions][]', "textarea_rows" => 3, "wpautop" => false, "tinymce" => false, "quicktags" => true, "editor_class" => "no-quicktags")); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label
                        for="settings-conditional_email_message_<?php echo $i; ?>"><?php _e('Message', 'nf-conditional-actions'); ?></label></th>
            <td>
                <?php wp_editor($message, 'settings-conditional_email_message_' . $i, array('textarea_name' => 'settings[conditional_email_messages][]')); ?>
            </td>
        </tr>
        <?php
    }

    public static function nf_add_condition_action_email_callback() {

        Ninja_Forms()->notifications->add_js();
        Ninja_Forms()->notifications->add_css();
        Ninja_Forms()->notifications->bulk_actions();
        Ninja_Forms()->notifications->duplicate_notification();
        add_filter( 'media_buttons_context', array(Ninja_Forms()->notifications, 'tinymce_buttons') );

        self::nf_get_condition_fields(intval(filter_input(INPUT_POST, "index")));

        wp_die();
    }

	/**
	 * Get our input value labels
	 *
	 * @access public
	 * @since 2.8
	 * @return string $label
	 */
	public function get_value( $id, $meta_key, $form_id ) {
		$meta_value = nf_get_object_meta_value( $id, $meta_key );
		$meta_value = explode( '`', $meta_value );

		$return = array();
		foreach( $meta_value as $val ) {
			if ( strpos( $val, 'field_' ) !== false ) {
				$val = str_replace( 'field_', '', $val );
				$label = nf_get_field_admin_label( $val, $form_id );
				if ( strlen( $label ) > 30 ) {
					$label = substr( $label, 0, 30 );
				}

				$return[] = array( 'value' => 'field_' . $val, 'label' => $label . ' - ID: ' . $val );
			} else {
				$return[] = array( 'value' => $val, 'label' => $val );
			}
		}

		return $return;
	}

	/**
	 * Process our Email notification
	 *
	 * @access public
	 * @since 2.8
	 * @return void
	 */
	public function process( $id ) {
		global $ninja_forms_processing;

		$form_id 		= $ninja_forms_processing->get_form_ID();
		$form_title 	= $ninja_forms_processing->get_form_setting( 'form_title' );

		$email_format 	= Ninja_Forms()->notification( $id )->get_setting( 'email_format' );
		$attach_csv 	= Ninja_Forms()->notification( $id )->get_setting( 'attach_csv' );

		$from_name 		= $this->process_setting( $id, 'from_name' );
		$from_name 		= implode( ' ', $from_name );

		$from_address 	= $this->process_setting( $id, 'from_address' );
		$from_address 	= $from_address[0];

		$reply_to 		= $this->process_setting( $id, 'reply_to' );
		$reply_to 		= $reply_to[0];
		$to 			= $this->process_setting( $id, 'to' );
		$cc 			= $this->process_setting( $id, 'cc' );
//		$cc             = $cc[0];
		$bcc 			= $this->process_setting( $id, 'bcc' );
//		$bcc 			= $bcc[0];

		if ( empty ( $from_name ) )
			$from_name = 'WordPress';

		if ( empty ( $from_address ) ) {
			$admin_email = get_bloginfo( 'admin_email' );
			$from_address = $admin_email;
		}

		$email_from 	= $from_name.' <'.$from_address.'>';

		$subject 		= $this->process_setting( $id, 'email_subject' );
		$subject 		= $this->flatten_array_recursive( ' ', $subject );

		if ( empty( $subject ) ) {
			$subject = $form_title;
		} elseif( is_array( $subject ) ){
            $subject = implode( ',', $subject );
        }


        $message = Ninja_Forms()->notification( $id )->get_setting('default_conditional_email_message');

        $conditional_conditions = unserialize(Ninja_Forms()->notification( $id )->get_setting( 'conditional_email_message_conditions' ));

        if(is_array($conditional_conditions)) {
            foreach ($conditional_conditions as $i => $conditional_condition) {
                //echo 'return (' . htmlspecialchars_decode(preg_replace('/(\d+),(\d+)/', '$1.$2', preg_replace('/(\d+).(\d+)/', '$1$2', do_shortcode($conditional_condition)))) . ');';
                if (eval('return (' . htmlspecialchars_decode(preg_replace('/(\d+),(\d+)/', '$1.$2', preg_replace('/(\d+).(\d+)/', '$1$2', do_shortcode($conditional_condition)))) . ');')) {
                    $message = unserialize(Ninja_Forms()->notification( $id )->get_setting('conditional_email_messages'))[$i];
                    break;
                }
            }
        }

		if ( is_array ( $message ) )
			$message 	= $message[0];

		if ( $email_format != 'plain' ) {
            $message = do_shortcode( apply_filters( 'ninja_forms_admin_email_message_wpautop', wpautop( $message ) ) );
            $message = nf_parse_fields_shortcode( $message );
        }

		if ( empty ( $message ) )
			$message 		= ' ';

		$headers = array();
		$headers[] = 'From: ' . $email_from;

		if( ! empty( $reply_to ) ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}
		$headers[] = 'Content-Type: text/' . $email_format;
		$headers[] = 'charset=utf-8';

		if ( ! empty( $cc ) ) {
			foreach ($cc as $ccemail) {
				if ( ! empty( $ccemail ) ) {
					$headers[] = 'Cc: ' . $ccemail;
				}
			}
		}

		if ( ! empty( $bcc ) ) {
			foreach ($bcc as $bccemail) {
				if ( ! empty( $bccemail ) ) {
					$headers[] = 'Bcc: ' . $bccemail;
				}
			}
		}

		$csv_attachment = '';
		$attachments = apply_filters( 'nf_email_notification_attachments', array(), $id );

		// Check to see if we need to attach a CSV
		if ( 1 == $attach_csv ) {
			// Create our attachment

			// Get our submission ID
			$sub_id = $ninja_forms_processing->get_form_setting( 'sub_id' );

			// create CSV content
			$csv_content = Ninja_Forms()->sub( $sub_id )->export( true );

			$upload_dir = wp_upload_dir();
			$path = trailingslashit( $upload_dir['path'] );

			// create temporary file
			$path = tempnam( $path, 'Sub' );
			$temp_file = fopen( $path, 'r+' );

			// write to temp file
			fwrite( $temp_file, $csv_content );
			fclose( $temp_file );

			// find the directory we will be using for the final file
			$path = pathinfo( $path );
			$dir = $path['dirname'];
			$basename = $path['basename'];

			// create name for file
			$new_name = apply_filters( 'ninja_forms_submission_csv_name', 'ninja-forms-submission' );

			// remove a file if it already exists
			if( file_exists( $dir.'/'.$new_name.'.csv' ) ) {
				unlink( $dir.'/'.$new_name.'.csv' );
			}

			// move file
			rename( $dir.'/'.$basename, $dir.'/'.$new_name.'.csv' );
			$csv_attachment = $dir.'/'.$new_name.'.csv';

			$attachments[] = $csv_attachment;
		}

		if ( is_array( $to ) AND !empty( $to ) ){

            $to = explode( ",", $this->flatten_array_recursive( ',', $to ) );

			wp_mail( $to, $subject, $message, $headers, $attachments );
		}

		// Delete our admin CSV if one is present.
		if ( file_exists( $csv_attachment ) ) {
			//unlink ( $csv_attachment );
		}
	}

    public function flatten_array_recursive( $glue = ',', array $array = array() ) {

        $return = array();

        foreach ( $array as $value ) {

            if ( is_array( $value ) ) {

                $return[] = $this->flatten_array_recursive( $glue, $value );

            } else {

                $return[] = $value;

            }

        }

        return implode( $glue, $return );

    }

	/**
	 * Explode our settings by ` and extract each value.
	 * Check to see if the setting is a field; if it is, assign the value.
	 * Run shortcodes and return the result.
	 *
	 * @access public
	 * @since 2.8
	 * @return array $setting
	 */
	public function process_setting( $id, $setting, $html = 0 ) {

		// save the setting name
		$setting_name = $setting;

		$format = Ninja_Forms()->notification( $id )->get_setting( 'email_format' );
		if ( 'html' == $format )
			$html = 1;

		// call parent process setting method
		$setting = parent::process_setting( $id, $setting, $html );

		// gotta keep the old filter in case anyone was using it.
		return apply_filters( 'nf_email_notification_after_settings', $setting, $setting_name, $id );
	}

}

return new NF_Action_Conditional_Action_Email();
