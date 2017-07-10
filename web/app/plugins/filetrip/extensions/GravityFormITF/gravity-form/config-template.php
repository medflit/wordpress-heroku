<?php

$type = 'filetrip-uploader';

add_action( 'gform_field_standard_settings', 'filetrip_standard_settings', 10, 2 );

/**
  * Add setting field for Filetirp shortcode.
  *
  * A filetrip_settings has to be enabled from GF_Filetrip_Field Class to unhide the setting.
  *
  * @param int $position defines where the setting will be located
  * @param int $form_id will hold the current form_id.
  *
  * @return void
  */
function filetrip_standard_settings( $position, $form_id ) {

    //create settings on position 25 (right after Field Label)
    if ( $position == 10 ) {
        ?>
        <li class="filetrip_setting field_setting">
            <label for="field_admin_label">
                <?php esc_html_e( 'Select Filetrip Uploader', 'gravityforms' ); ?>
                <?php gform_tooltip( 'Select Filetrip uploader that has already been configured and created' ) ?>
            </label>
			<select id="filetrip_shortcode" class="field-config" onchange="SetFieldProperty('filetrip_shortcode', jQuery(this).val());">
				
				<?php
				$wp_query = new WP_Query(); 
				$wp_query->query('showposts=-1&post_type='.Filetrip_Constants::POST_TYPE); 

				while ($wp_query->have_posts()) : $wp_query->the_post();
				?>

						<option value="<?php echo get_the_ID();?>" ><?php echo get_the_title(); ?></option>
							
				<?php
				endwhile;
				?>

  			</select>
        </li>
        <?php
    }
}

add_action( 'gform_editor_js', 'filetrip_editor_script' );

/**
  * Initialize the Filetrip Shortcode setting field
  *
  * This function contains a javascript function that will be called whenever the Dropdown menu changed and it should 
  *     update the {field.filetrip_shortcode} object with shorcode value 
  *
  * @return void
  */
function filetrip_editor_script(){
    ?>
    <script type='text/javascript'>

        //binding to the load field settings event to initialize the checkbox
        jQuery(document).bind('gform_load_field_settings', function(event, field, form){
			var selectedFiletrip = jQuery('#filetrip_shortcode').val(field.filetrip_shortcode);
			selectedFiletrip.attr('selected','selected');
        });
    </script>
    <?php
}

?>