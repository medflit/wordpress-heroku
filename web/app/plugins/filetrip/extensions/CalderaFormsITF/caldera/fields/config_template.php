<div class="caldera-config-group">
	<label for="{{_id}}_shortcode"><?php echo __('Filetrip Shortcode ID', 'filetrip-plugin'); ?></label>
	<div class="caldera-config-field">
		<select id="{{_id}}_shortcode" class="field-config" name="{{_name}}[shortcode]">
          
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
	</div>
</div>

{{#script}}
jQuery(function($){
    var selectedArfaly = jQuery('#{{_id}}_shortcode').val('{{shortcode}}');
    selectedArfaly.attr('selected','selected');
});
{{/script}}
