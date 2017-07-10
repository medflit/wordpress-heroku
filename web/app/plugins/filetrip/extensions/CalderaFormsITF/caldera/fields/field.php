<?php
	$is_multiple = null;
	if( !empty( $field['config']['multi_upload'] ) ){
		$is_multiple = 'multiple="multiple"';
		$field_name .= '[]';
	}

?><?php echo $wrapper_before; ?>
	<?php echo $field_label; ?>
	<?php echo $field_before; ?>
		<?php 
        if(isset($field['config']['shortcode']) && intval($field['config']['shortcode'])>=1)
        {
          echo "<br>"; 
          echo Filetrip_Uploader::building_arfaly_container($field['config']['shortcode'], $field_required); 
        }else{
          echo __("Please update Filetrip shortcode ID in your form to be able to render the uploader", 'filetrip-plugin');
        }
        ?>
		<input type="hidden" name="<?php echo $field_name; ?>" value="true">
		<?php echo $field_caption; ?>
	<?php echo $field_after; ?>
<?php echo $wrapper_after; ?>
