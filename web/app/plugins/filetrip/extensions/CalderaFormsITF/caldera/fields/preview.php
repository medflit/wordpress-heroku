<div class="preview-caldera-config-group">
	{{#unless hide_label}}<lable class="control-label">{{label}}{{#if required}} <span style="color:#ff0000;">*</span>{{/if}}</lable>{{/unless}}
    <div class="preview-caldera-config-field">
		<img src="<?php echo ITECH_FILETRIP_PLUGIN_URL.'/assets/img/logo.png'; ?>" width="50" height="50" >
        <input type="file" class="preview-field-config" disabled="disabled" {{#if hide_label}}placeholder="{{label}}"{{/if}}>
		<span class="help-block">{{caption}}</span>
	</div>
</div>
