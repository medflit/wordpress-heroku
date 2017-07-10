<?php

/*
 * This calss should provide an abstraction for handling chunked uploads
 * for both Dropbox, Google Drive, and all of the other channels.
 */


class Filetrip_Channel
{
    private $_config;
    private $_current_channel;

    public function __construct($configuration, iFiletrip_Channel $channel) 
    {
        $this->_config = $configuration;
        $this->_current_channel = $channel;

        $this->_config = array(
            'meta_field_type' => 'dropbox_folder_select',
            'meta_subfolder_id' => 'enable_user_subfolder',
            'select_folder_id' => 'dropbox_folder',
            'field_slug' => 'dropbox',
            'field_name' => 'Dropbox',
            'ajax_action_ref' => 'dropbox_send_file'
        );

        // Filters 
        add_filter( 'itf/filetrip/channel/selection_filter', array($this, 'add_meta_channel_configuration'));
        add_filter( 'itf/filetrip/settings/add/section_fields', array($this, 'add_setting_field'));

        // Register this channel to the core channel distributor [Filetrip_Distributor]
        add_filter( 'itf/filetrip/filter/register/channels', array($this, 'register_me_to_channels'));
        add_filter( 'itf/filetrip/filter/channels/media/dest', array($this, 'register_me_as_forwarder'), 10, 2);

        // Dropbox meta type to render folder selection widget
        add_action( 'wp_ajax_' . $this->_config['ajax_action_ref'] , array($this, 'send_media_to_cloud'), 10 );
        add_action( 'wp_ajax_' . $this->_config['ajax_action_ref'] . '_backup', array($this, 'send_file_to_cloud'), 10 );
        add_action( 'filetrip_cmb_render_' . $this->_config['meta_field_type'] , array($this, 'folder_select_widget'), 10, 5 );
        add_action( 'itf/main_setting/add_field/' . $this->_config['meta_field_type'] , array($this, 'add_settings_field_callback'), 10, 4);
        add_action( 'admin_init', array($this ,'init_admin_hooks') );
        
        /**
        * Hook to admin footer to inject jQuery for Media action addition
        */
        add_action('admin_footer-upload.php', array( $this, 'custom_bulk_admin_media_library_footer'));
    }

  	/**
	 * @param (array) ($channels) 
	 *      This function responsible of registering this channel to the core 
	 *		Filetrip channel distributor [Filetrip_Distributor]. Also it should pass
	 *		all the necessary information to the client js.
	 */
	function register_me_to_channels($channels)
	{
			/**
			** Channel array structure:
			array(
				'channel_key' => 'key',
				'channel_name' => 'name',
				'channel_icon' => 'path/img',
				'channel_action_url' => 'action_url',
				'active' => $this->_current_channel->is_active(),
				'security' => wp_create_nonce( $this->ajax_action_ref )
			)
			**/
			$filetrip_settings = \Filetrip_Uploader::get_filetrip_main_settings();

			$destination = isset($filetrip_settings[$this->select_folder_id])?$filetrip_settings[$this->select_folder_id]:'';
			
			$channels[$this->field_slug] = array(
				'destination' => $destination,
				'channel_key' => $this->_config['field_slug'],
				'channel_name' => $this->_config['field_name'],
				'channel_icon' => $this->get_icon('', '', '20', ''),
				'channel_action_url' => $this->ajax_action_ref,
				'active' => $this->_current_channel->is_active(),
				'security' => wp_create_nonce( $this->ajax_action_ref ),
				'is_subfolder' => false
			);

			return $channels;
	}

  	/**
	 * @param (int) ($uploader_id) 
	 *      Gets attachment ID and find if this channel is selected as forwarding channel, and retrive destination.
	 */
	function register_me_as_forwarder($forwardingChannels, $uploader_id)
	{
		$post = get_post($uploader_id);
		$selectedChannels = \Filetrip_Channel_Utility::get_channel_selected($post->post_parent);

		/**
		* Check if this channel is been selected for the designated uploader.
		*/
		foreach((array)$selectedChannels as $select)
		{
			if($select == $this->field_slug){
				$forwardingChannels[] = array(
					'destination' => $this->get_meta_destination_folder($post->post_parent),
					'channel_key' => $this->field_slug,
					'channel_name' => $this->field_name,
					'channel_icon' => $this->get_icon('', '', '20', ''),
					'channel_action_url' => $this->ajax_action_ref,
					'active' => \Filetrip_Dropbox::is_dropbox_active(),
					'security' => wp_create_nonce( $this->ajax_action_ref ),
					'is_subfolder' => $this->get_subfolder_option_value($post->post_parent)
				);
			}
		}

		return $forwardingChannels;
	}

	function get_meta_destination_folder($post_id)
    {
        $meta = get_post_meta( $post_id );
        if(isset($meta[\Filetrip_Constants::METABOX_PREFIX . $this->select_folder_id]))
        {
          $folder = $meta[\Filetrip_Constants::METABOX_PREFIX . $this->select_folder_id][0];
          return $folder;
        }
        
        return false;
    }

	function get_subfolder_option_value($post_id)
	{
		if(empty($post_id))
			return false;
		
		$meta = get_post_meta( $post_id );
		if(isset($meta[\Filetrip_Constants::METABOX_PREFIX . $this->meta_subfolder_id]))
		{
			$channel_selected = $meta[\Filetrip_Constants::METABOX_PREFIX . $this->meta_subfolder_id][0];
			return $channel_selected;
		}
		
		return false;
	}

	/**
	 * Add a trigger to inject Channel icon in Media Column
	 *
	 * @return null
	 */
	function init_admin_hooks() {
		// All hooks that need to be called at admin_init trigger should be placed hear 
		add_action( 'manage_media_custom_column', array($this, 'insert_channel_hyperlinked_icon'), 10, 2 );
	}

	/**
	 * This function will render the channel folder listing widget.
	 *
	 * @return html
	 */
	function folder_select_widget($field_args, $escaped_value, $object_id, $object_type, $field_type_object ) {
		\Filetrip_Dropbox_Utility::build_select_folder_widget($field_args, $field_type_object, true);
	}

	function add_meta_channel_configuration($meta_config_array)
	{
		// Add checkbox field
		$meta_config_array['fields']['channels']['options'][$this->field_slug] = $this->get_icon();

		// Add folder selection field
		$meta_config_array['fields'][] = array(
              'name'    => 'Dropbox folder',
              'desc'    => 'Select the destination folder for your Dropbox folder channel',
              'id'      => \Filetrip_Constants::METABOX_PREFIX . $this->select_folder_id,
              'type'    => $this->meta_field_type,
              'attributes' => array( 'readonly' => '' )
           );

		 $meta_config_array['fields'][] = array(
              'name'    => 'Automatic sub-folder creation',
              'desc'    => '(Dropbox Channel Only) Automatically create sub-folders with the uploader name under the main destination folder as a new partitioning mechanism',
              'id'      => \Filetrip_Constants::METABOX_PREFIX . $this->meta_subfolder_id,
              'type'    => 'checkbox'
            );
		

		return $meta_config_array;
	}

	function add_setting_field($setting_array)
	{
		$setting_array[\Filetrip_Constants::POST_TYPE.'_settings'][] = array(
					'name' => $this->select_folder_id,
					'label' => __( 'Select Dropbox Folder', 'filetrip-plugin' ),
					'desc' => __( 'Select default Dropbox folder as Media destination', 'filetrip-plugin' ),
					'type' => $this->setting_field_type,
					'default' => '',
				);

		return $setting_array;
	}

	function add_settings_field_callback( $args, $section, $option, $obj)
	{
		// Render my setting
		add_settings_field( $section . '[' . $option['name'] . ']', $option['label'], 
		function() use($args, $obj) {
			\Filetrip_Dropbox_Utility::build_select_folder_widget($args, $obj);
		}
		, $section, $section, $args );
	}

	function get_icon($link = '', $title = '', $size = '30', $label = 'Dropbox')
	{
		// If there is no link? Don't add href 
		$hrefStart = '<a target="_blank" href="'.$link.'">';
		$hrefEnd = '</a> ';

		if( $link == '' )
		{
			$hrefStart = '';
			$hrefEnd = '';
		}
		
		return $hrefStart.'<img title="Dropbox" src="'.$this->extensionGetDirectoryUrl().'/assets/img/dropbox.png" width="'. $size .'" height="'. $size .'">'.$hrefEnd." ".$label;
	}

	function insert_channel_hyperlinked_icon($column_name, $id)
	{
		switch($column_name)
		{
			case \Filetrip_Constants::MEDIA_COLUMN_SLUG;
				$query_s = admin_url(\Filetrip_Constants::FILETRIP_DISTRIBUTOR_PAGE);
				$query_s = $query_s.'&media='.$id.'&source='.\Filetrip_Constants::Transfer_Type('media').'&';

				echo $this->get_icon($query_s.'channel='.$this->field_slug, 'Send file to Dropbox', '20', '');

				break;
			default:
				break;
		}
	}

}