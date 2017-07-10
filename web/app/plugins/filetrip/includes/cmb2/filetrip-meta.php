<?php
/**
 * Include and setup custom metaboxes and fields. (make sure you copy this file to outside the FILETRIP_CMB directory)
 *
 * @category Filetrip
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/webdevstudios/Custom-Metaboxes-and-Fields-for-WordPress
 */

/**
 * Get the bootstrap! If using the plugin from wordpress.org, REMOVE THIS!
 */
if ( file_exists( dirname( __FILE__ ) . '/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/init.php';
}

/**
 * Conditionally displays a field when used as a callback in the 'show_on_cb' field parameter
 *
 * @param  FILETRIP_CMB2_Field object $field Field object
 *
 * @return bool                     True if metabox should show
 */
function filetrip_cmb_hide_if_no_cats( $field ) {
	// Don't show this field if not in the cats category
	if ( ! has_tag( 'cats', $field->object_id ) ) {
		return false;
	}
	return true;
}

add_filter( 'filetrip_cmb_meta_boxes', 'filetrip_sample_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */

function arfaly_get_icon($imgName, $name='', $size='30', $link = '', $title = '')
{
  // If there is no link? Don't add href 
  $hrefStart = '<a target="_blank" href="'.$link.'">';
  $hrefEnd = '</a> ';

  if( $link == '' )
  {
    $hrefStart = '';
    $hrefEnd = '';
  }
  
  return $hrefStart.'<img title="'.$title.'" src="'.ITECH_FILETRIP_PLUGIN_URL.'/assets/img/'.$imgName.'.png" width="'.$size.'" height="'.$size.'">'.$hrefEnd.$name;
}

function filetrip_sample_metaboxes( array $meta_boxes ) {
    
    $prefix = Filetrip_Constants::METABOX_PREFIX;
    
    // Start with an underscore to hide fields from custom fields list

    $meta_boxes['arfaly_channel_selection'] = array(
        'id'         => 'arfaly_forward_selection',
		'title'      => __( ucfirst(Filetrip_Constants::POST_TYPE).' channel selection', 'cmb' ),
		'object_types'      => array( Filetrip_Constants::POST_TYPE ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true,
		'fields'     => array( "channels" =>
            array(
              'name'    => 'Upload channel',
              'desc'    => 'Select a channel where you want your files to be uploaded after they get approved ?',
              'id'      => $prefix . 'channel_select',
              'type'    => 'multicheck',
              'options' => array(
                  'wordpress' => arfaly_get_icon('wordpress', 'Wordpress')
              )
           )
        )
    );
    
    $meta_boxes['new_arfaly_metabox'] = array(
        'id'         => 'arfaly_metabox',
		'title'      => __( ucfirst(Filetrip_Constants::POST_TYPE).' multi-file uploader options', 'filetrip-plugin' ),
		'object_types'      => array( Filetrip_Constants::POST_TYPE ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true,
		'fields'     => array(
            array(
				'name' => __( 'Allow guests', 'filetrip-plugin' ),
				'desc' => __( 'Allow guests to upload files', 'filetrip-plugin' ),
				'id'   => $prefix . 'allow_guests',
				'type' => 'checkbox',
			),
            array(
				'name' => __( 'Enforce information submission', 'filetrip-plugin' ),
				'desc' => __( 'Enforce users to submit Title and Description for each upload.', 'filetrip-plugin' ),
				'id'   => $prefix . 'enforce_info',
				'type' => 'checkbox',
			),
            array(
				'name' => __( 'Information submission is required', 'filetrip-plugin' ),
				'desc' => __( 'Users must submit information for the upload to execute.', 'filetrip-plugin' ),
				'id'   => $prefix . 'required',
				'type' => 'checkbox',
			),
            array(
				'name' => __( 'Disable Drag & Drop effect', 'filetrip-plugin' ),
				'desc' => __( 'Use native browse button for file upload.', 'filetrip-plugin' ),
				'id'   => $prefix . 'disable_drag_drop',
				'type' => 'checkbox',
			),
            array(
				'name' => __( 'Disable file preview', 'filetrip-plugin' ),
				'desc' => __( 'Disable file preview feature', 'filetrip-plugin' ),
				'id'   => $prefix . 'file_preview',
				'type' => 'checkbox',
			),
            array(
              'name'       => __( 'Max file size (MB)', 'filetrip-plugin' ),
              'desc'       => __( '(MB) Max upload file size allowed.', 'filetrip-plugin' ),
              'id'         => $prefix . 'max_file_size',
              'type'       => 'text_small',
              'default'    => '10',
              'sanitization_cb' => 'arfaly_number_sanitization', // custom sanitization callback parameter
                          // 'escape_cb'       => 'number_escaping',  // custom escaping callback parameter
              'attributes'  => array(
                  'placeholder' => 10,
              ),
            ),
            array(
              'name'       => __( 'Max files limit)', 'filetrip-plugin' ),
              'desc'       => __( 'Max upload files limit.', 'filetrip-plugin' ),
              'id'         => $prefix . 'file_upload_limit',
              'type'       => 'text_small',
              'default'    => '10',
              'sanitization_cb' => 'arfaly_number_sanitization', // custom sanitization callback parameter
              'attributes'  => array(
                  'placeholder' => 10,
              ),
            ),
            array(
				'name'    => __( 'Strictly allow extensions', 'filetrip-plugin' ),
				'desc'    => __( 'Allow only these extensions (Use comma seperator). Leave it empty to allow WP default mime list. <br><a href="'.admin_url( Filetrip_Constants::OPTION_PAGE ).'">To customize mime</a>', 'filetrip-plugin' ),
				'id'      => $prefix . 'strict_extensions',
				'type'    => 'text',
                'attributes'  => array(
                  'placeholder' => 'avi, wmv, png, jpg',
                ),
			),
            array(
              'name' => __( 'Debug', 'filetrip-plugin' ),
              'desc' => __( 'Print out debug messages', 'filetrip-plugin' ),
              'id'   => $prefix . 'debug',
              'type' => 'checkbox',
			),
            array(
				'name'       => __( 'Debugging info target', 'filetrip-plugin' ),
				'desc'       => __( 'For class name add "." letter prefix. For ID targeting put "#" letter', 'filetrip-plugin' ),
				'id'         => $prefix . 'target_debug',
				'type'       => 'text',
                'attributes'  => array(
                    'placeholder' => '#output-name',
                ),
			),
            array(
				'name'       => __( 'Uploader message', 'filetrip-plugin' ),
				'desc'       => __( 'Label that will be displayed in the bottom of the uploader box', 'filetrip-plugin' ),
				'id'         => $prefix . 'label',
				'type'       => 'text',
                'attributes'  => array(
                    'placeholder' => 'Allowed file types are psd, ai, bmp, svg, tiff, gif, jpg, and png.',
                ),
			),
                        // Drop box section
            array(
				'name'       => __( 'Drop file box label', 'filetrip-plugin' ),
				'desc'       => __( 'Change drop file box label from here', 'filetrip-plugin' ),
				'id'         => $prefix . 'drop_box_title',
				'type'       => 'text',
                'attributes'  => array(
                    'placeholder' => 'Drop your files here',
                ),
			),
            array(
              'name'       => __( 'Drop box label font size', 'filetrip-plugin' ),
              'desc'       => __( 'Change the font size of the drop file box label from here.', 'filetrip-plugin' ),
              'id'         => $prefix . 'drop_box_font_size',
              'type'       => 'text_small',
              'default'    => '26',
              'sanitization_cb' => 'arfalygold_number_sanitization', // custom sanitization callback parameter
                          // 'escape_cb'       => 'number_escaping',  // custom escaping callback parameter
              'attributes'  => array(
                  'placeholder' => 26,
              ),
            ),
            array(
              'name'       => __( 'Change the height of the uploader box', 'filetrip-plugin' ),
              'desc'       => __( 'Modify the height of the uploader box.', 'filetrip-plugin' ),
              'id'         => $prefix . 'drop_box_height',
              'type'       => 'text_small',
              'default'    => '100',
              'sanitization_cb' => 'arfalygold_number_sanitization', // custom sanitization callback parameter
                          // 'escape_cb'       => 'number_escaping',  // custom escaping callback parameter
              'attributes'  => array(
                  'placeholder' => 100,
              ),
            ),
            array(
				'name'       => __( 'File Title placeholder', 'filetrip-plugin' ),
				'desc'       => __( 'Customize title placeholder', 'filetrip-plugin' ),
				'id'         => $prefix . 'title_placeholder',
				'type'       => 'text',
                'attributes'  => array(
                    'placeholder' => 'Enter file title...',
                ),
			),
            array(
				'name'       => __( 'File Description placeholder', 'filetrip-plugin' ),
				'desc'       => __( 'Customize description placeholder', 'filetrip-plugin' ),
				'id'         => $prefix . 'desc_placeholder',
				'type'       => 'text',
                'attributes'  => array(
                    'placeholder' => 'Enter file description...',
                ),
			),
            array(
              'name' => __( 'Theme options', 'filetrip-plugin' ),
              'desc' => __( 'Options related to plugin theme.', 'filetrip-plugin' ),
              'id'   => $prefix . 'test_title',
              'type' => 'title',
              ),
            array(
				'name'    => __( 'Change uploader\'s theme', 'filetrip-plugin' ),
				'desc'    => __( 'Select the suitable theme for your website.', 'filetrip-plugin' ),
				'id'      => $prefix . 'uploader_theme',
				'type'    => 'select',
                'default' => array('simplex'),
				'options' => array(
                    'simplex' => __( 'Simplex', 'filetrip-plugin' ),
					'default' => __( 'Big style', 'filetrip-plugin' ),
					'super-simplex' => __( 'Super Simplex', 'filetrip-plugin' ),
				),
				// 'inline'  => true, // Toggles display to inline
			),
            array(
              'name'    => __( 'Icon image', 'filetrip-plugin' ),
              'desc'    => __( 'Uploader icon image with dimension less than <b>(110x110)px</b> ', 'filetrip-plugin' ),
              'id'      => $prefix . 'icon_image',
              'type' => 'file',
              'allow' => array( 'url', 'attachment' ) // limit to just attachments with array( 'attachment' )
              ),
            array(
              'name'    => __( 'Upload item color', 'filetrip-plugin' ),
              'desc'    => __( 'Change the background color of an upload entries', 'filetrip-plugin' ),
              'id'      => $prefix . 'background_color',
              'type'    => 'colorpicker',
              'default' => ''
              ),
            array(
              'name'    => __( 'Logo Color', 'filetrip-plugin' ),
              'desc'    => __( 'Logo color', 'filetrip-plugin' ),
              'id'      => $prefix . 'logo_color',
              'type'    => 'colorpicker',
              'default' => '#639AFF'
              ),
            array(
              'name'    => __( 'Text color', 'filetrip-plugin' ),
              'desc'    => __( 'Text color', 'filetrip-plugin' ),
              'id'      => $prefix . 'text_color',
              'type'    => 'colorpicker',
              'default' => '#818080'
              ),
            array(
              'name'    => __( 'Upload border color', 'filetrip-plugin' ),
              'desc'    => __( 'Upload border color', 'filetrip-plugin' ),
              'id'      => $prefix . 'border_color',
              'type'    => 'colorpicker',
              'default' => '#cecece'
              ),
            array(
              'name'    => __( 'Label Color', 'filetrip-plugin' ),
              'desc'    => __( 'Upload label text color', 'filetrip-plugin' ),
              'id'      => $prefix . 'label_color',
              'type'    => 'colorpicker',
              'default' => '#818080'
              ),
            )
        );

        $meta_boxes['arfaly_channel_selection'] = apply_filters('itf/filetrip/channel/selection_filter', $meta_boxes['arfaly_channel_selection']);

        // Add other metaboxes as needed
        return $meta_boxes;
}