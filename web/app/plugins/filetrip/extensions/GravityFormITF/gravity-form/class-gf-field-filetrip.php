<?php

class GF_Field_Filetrip extends GF_Field {

	public $type = 'filetrip-uploader';

	public function get_form_editor_field_title() {
		return esc_attr__( 'Filetrip Uploader', 'gravityforms' );
	}

	function get_form_editor_field_settings() {
		return array(
			'label_setting',
			'filetrip_setting',
			'rules_setting'
		);
	}

	// Validate user input
	public function validate( $value, $form ) {

		if(!isset($_POST["image-id"]) && $this->isRequired){
			$this->failed_validation = true;
			$this->validation_message = esc_html__( 'No files have been uploaded', 'gravityforms' );
			return;
		}

	}

	public function get_first_input_id( $form ) {

		return $this->multipleFiles ? '' : 'input_' . $form['id'] . '_' . $this->id;
	}


	public function get_field_input( $form, $value = '', $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		$lead_id = absint( rgar( $entry, 'id' ) );
		// error_log($this->filetrip_shortcode);
		
		if($is_form_editor){
			$input = '<img src="'.ITECH_FILETRIP_PLUGIN_URL.'/assets/img/logo.png" width="50" height="50" >'.
        		'<input type="file" class="preview-field-config" disabled="disabled" >';
		}else{
			if($this->filetrip_shortcode > 0)
			{
				$input = "<br>"; 
				$input = $input. Filetrip_Uploader::building_arfaly_container($this->filetrip_shortcode, $this->isRequired); 
			}else{
				$input = __("Please update Filetrip shortcode ID in your form to be able to render the uploader", 'filetrip-plugin');
			}
		}

		return sprintf( "<div class='ginput_container ginput_container_text'>%s</div>", $input );
	}

	public function get_value_save_entry( $value, $form, $input_name, $lead_id, $lead ) {
		global $_gf_uploaded_files;

		$uploads = array();

		if ( empty( $_gf_uploaded_files ) ) {
			$_gf_uploaded_files = array();
		}

		if ( ! isset( $_gf_uploaded_files[ $input_name ] ) ) {

			if(isset($_POST["image-id"]))
			{
				$att_ids = $_POST["image-id"];
				
				foreach( $att_ids as $att ){
					$url = wp_get_attachment_url( intval($att) );
					if($url)
						$uploads[] = $url;
				}
			}
			
			// If multi-file were returned
			if( count( $uploads ) > 1 ){
				if ( ! empty( $value ) ) { // merge with existing files (admin edit entry)
					$value = json_decode( $value, true );
					$value = array_merge( $value, $uploads );
					$value = json_encode( $value );
				} else {
					$value = json_encode( $uploads );
				}

				$_gf_uploaded_files[ $input_name ] = $value;

				return $value;
			}
			
			// If single file was returned
			if(!empty($uploads)){
				$_gf_uploaded_files[ $input_name ] = $uploads[0];

				return rgget( $input_name, $_gf_uploaded_files );
			}else{
				return '';
			}

		}
	}

	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {

		if ( $this->multipleFiles ) {
			$uploaded_files_arr = empty( $value ) ? array() : json_decode( $value, true );
			$file_count         = count( $uploaded_files_arr );
			if ( $file_count > 1 ) {
				$value = empty( $uploaded_files_arr ) ? '' : sprintf( esc_html__( '%d files', 'gravityforms' ), count( $uploaded_files_arr ) );
				return $value;
			} elseif ( $file_count == 1 ) {
				$value = current( $uploaded_files_arr );
			} elseif ( $file_count == 0 ) {
				return;
			}
		}

		$file_path = $value;
		if ( ! empty( $file_path ) ) {
			//displaying thumbnail (if file is an image) or an icon based on the extension
			$thumb     = GFEntryList::get_icon_url( $file_path );
			$file_path = esc_attr( $file_path );
			$value     = "<a href='$file_path' target='_blank' title='" . esc_attr__( 'Click to view', 'gravityforms' ) . "'><img src='$thumb'/></a>";
		}
		return $value;
	}

	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		$output = '';
		if ( ! empty( $value ) ) {
			$output_arr = array();
			$file_paths = strpos($value, ',') === false ?  array( $value ) : json_decode( $value );

			if ( is_array( $file_paths ) ) {
				foreach ( $file_paths as $file_path ) {
					$info = pathinfo( $file_path );
					if ( GFCommon::is_ssl() && strpos( $file_path, 'http:' ) !== false ) {
						$file_path = str_replace( 'http:', 'https:', $file_path );
					}
					$file_path          = esc_attr( str_replace( ' ', '%20', $file_path ) );
					$base_name          = $info['basename'];
					$click_to_view_text = esc_attr__( 'Click to view', 'gravityforms' );
					$output_arr[]       = $format == 'text' ? $file_path . PHP_EOL : "<li><a href='{$file_path}' target='_blank' title='{$click_to_view_text}'>{$base_name}</a></li>";
				}
				$output = join( PHP_EOL, $output_arr );
			}
		}
		$output = empty( $output ) || $format == 'text' ? $output : sprintf( '<ul>%s</ul>', $output );

		return $output;
	}

	public function is_value_submission_empty( $form_id ) {
		
		if(!isset($_POST["image-id"]) && $this->isRequired){
			return true;
		}
	}

	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {

		if ( $this->multipleFiles ) {

			$files = empty( $raw_value ) ? array() : json_decode( $raw_value, true );
			foreach ( $files as &$file ) {

				$file = str_replace( ' ', '%20', $file );

				if ( $esc_html ) {
					$value = esc_html( $value );
				}
			}
			$value = $format == 'html' ? join( '<br />', $files ) : join( ', ', $files );

		} else {
			$value = str_replace( ' ', '%20', $value );
		}

		if ( $url_encode ) {
			$value = urlencode( $value );
		}


		return $value;
	}

}

GF_Fields::register( new GF_Field_Filetrip() );