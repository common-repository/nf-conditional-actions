<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Class for conditional action message type.
 *
 * @package     NF Conditional Action Message
 * @subpackage  Classes/Actions
 * @copyright   Copyright (c) 2016, macnetic-labs
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class NF_Action_Conditional_Action_Message extends NF_Notification_Base_Type
{
    /**
     * Get things rolling
     */
    function __construct() {
        $this->name = __( 'Conditional Message' , 'nf-conditional-actions');
    }
    /**
     * Output our edit screen
     *
     * @access public
     * @since 2.8
     * @return void
     */
    public function edit_screen( $id = '' )
    {
        if ( $id == '' ) {
            $default_conditional_message = '';
            $conditional_conditions = array();
            $conditional_messages = array();
        } else {
            $notification = Ninja_Forms()->notification( $id );

            $default_conditional_message = $notification->get_setting( 'default_conditional_message' );
            $conditional_conditions = unserialize($notification->get_setting( 'conditional_conditions' ));
            $conditional_messages = unserialize($notification->get_setting( 'conditional_messages' ));

        }

        $length = 0;
        if(is_array($conditional_conditions)) {
            $length = count($conditional_conditions);
        }


        ?>
        <tr>
            <th scope="row"><label for="settings-default_conditional_message"><?php _e( 'Standard', 'nf-conditional-actions' ); ?></label></th>
            <td>
                <?php wp_editor($default_conditional_message, 'settings-default_conditional_message', array('textarea_name' => 'settings[default_conditional_message]') ); ?>
                <input type="hidden" name="settings-conditional_message_length" value="<?php echo $length ?>" id="settings-conditional_message_length" />
            </td>
        </tr>
        <?php
            if($length > 0) {
                foreach ($conditional_conditions as $i => $conditional_condition) {
                    self::nf_get_condition_fields($i, $conditional_conditions[$i], $conditional_messages[$i]);
                }
            }
        ?>
        <tr>
            <th scope="row">&nbsp;</th>
            <td>
                <input type="button" id="nf_addConditionButton" class="nf_addConditionButton" value="<?php _e('Add', 'nf-conditional-actions'); ?>" data-prefix="message" />
            </td>
        </tr>
        <?php

    }

    private static function nf_get_condition_fields($i, $condition = "", $message = "") {
        ?>
        <tr>
            <th scope="row">
                <label for="settings-conditional_condition_<?php echo $i; ?>">
                    <?php _e('Condition', 'nf-conditional-actions'); ?>

                    <input type="button" id="nf_removeConditionButton_<?php echo $i; ?>"
                           class="nf_removeConditionButton" value="<?php _e('Remove', 'nf-conditional-actions'); ?>"/>
                </label>
            </th>
            <td>
                <?php wp_editor(htmlspecialchars_decode($condition), 'settings-conditional_condition_' . $i, array('textarea_name' => 'settings[conditional_conditions][]', "textarea_rows" => 3, "wpautop" => false, "tinymce" => false, "quicktags" => true, "editor_class" => "no-quicktags")); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label
                    for="settings-conditional_message_<?php echo $i; ?>"><?php _e('Message', 'nf-conditional-actions'); ?></label></th>
            <td>
                <?php wp_editor($message, 'settings-conditional_message_' . $i, array('textarea_name' => 'settings[conditional_messages][]')); ?>
            </td>
        </tr>
        <?php
    }

    public static function nf_add_condition_action_message_callback() {

        Ninja_Forms()->notifications->add_js();
        Ninja_Forms()->notifications->add_css();
        Ninja_Forms()->notifications->bulk_actions();
        Ninja_Forms()->notifications->duplicate_notification();
        add_filter( 'media_buttons_context', array(Ninja_Forms()->notifications, 'tinymce_buttons') );

        self::nf_get_condition_fields(intval(filter_input(INPUT_POST, "index")));

        wp_die();
    }


    /**
     * Process our Redirect notification
     *
     * @access public
     * @since 2.8
     * @return void
     */
    public function process( $id ) {
        /*
        We declare our $ninja_forms_processing global so that we can access submitted values.
        */
        global $ninja_forms_processing;

        $notification = Ninja_Forms()->notification($id);
        $success_msg = $notification->get_setting('default_conditional_message');

        $conditional_conditions = unserialize($notification->get_setting( 'conditional_conditions' ));

        if(is_array($conditional_conditions)) {
            foreach ($conditional_conditions as $i => $conditional_condition) {
                //echo 'return (' . htmlspecialchars_decode(preg_replace('/(\d+),(\d+)/', '$1.$2', preg_replace('/(\d+).(\d+)/', '$1$2', do_shortcode($conditional_condition)))) . ');';
                if (eval('return (' . htmlspecialchars_decode(preg_replace('/(\d+),(\d+)/', '$1.$2', preg_replace('/(\d+).(\d+)/', '$1$2', do_shortcode($conditional_condition)))) . ');')) {
                    $success_msg = unserialize($notification->get_setting('conditional_messages'))[$i];
                    break;
                }
            }
        }

        $name = Ninja_Forms()->notification( $id )->get_setting( 'name' );
        // If our name is empty, we need to generate a random string.
        if ( empty ( $name ) ) {
            $name = ninja_forms_random_string( 4 );
        }

        $success_msg = do_shortcode( wpautop( $success_msg ) );
        $success_msg = nf_parse_fields_shortcode( $success_msg );

        $ninja_forms_processing->add_success_msg( 'success_msg-' . $name, $success_msg );

    }
}
return new NF_Action_Conditional_Action_Message();