<?php ?>

<script type="text/html" id="tmpl-upload-card-single">

    <li class="filetrip-transfer-item {{data.channel_key}}-button" id="filetrip-upload-card-{{data.card_id}}" style="display:none">
        
        <span class="filetrip-transfer-country list-only">
            {{data.channel_name}}
        </span>
        
        <span class="filetrip-transfer-name filetrip-overflow">
            {{data.file_name}}
        </span>
        
        <span class="filetrip-transfer-country grid-only">
            {{data.mime}}
            <br>
            <span class="filetrip-transfer-progress-bytes">{{data.descriptive_progress}}</span>
        </span>
        
        <div class="pull-right">
        
        <span class="filetrip-transfer-progress">
            <span class="filetrip-transfer-progress-bg">
            <span class="filetrip-transfer-progress-fg" style="width: 0%;"></span>
            </span>
            
            <span class="filetrip-transfer-progress-labels">
            <span class="filetrip-transfer-progress-label">
                <# if ( !data.active ) { #>
                    {{{data.channel_icon}}}
                <# } #>
                <# if ( data.active ) { #>
                   0%
                <# } #>
            </span>

            <span class="filetrip-transfer-completes">
                <# if ( !data.active ) { #>
                    <a href="#" class="help_tip filetrip-help-pointer--icon" data-title="{{data.channel_name}} is not active" data-tip="Please activate {{data.channel_name}} channel and try again.">?</a>
                <# } #>
                <# if ( data.active ) { #>
                    <!-- 490 / 500 MB -->
                    {{data.descriptive_progress}}
                <# } #>
            </span>
            </span>
        </span>
        
        <span class="filetrip-transfer-end-date ended" title="Dropbox/app/test/videos">
            <!-- Dropbox/app/test/videos -->
            <# if ( data.default_dest == '' ) { #>
                /ROOT
            <# } #>
            <# if ( data.default_dest != '' ) { #>
                {{{data.default_dest}}}
            <# } #>
        </span>
        <span class="filetrip-transfer-stage">
            <div class="arfaly-drive-folder-loading spinner is-active" style="float:left"></div>
            <span class="stage-channel-icon">
            {{{data.channel_icon}}}
            </span>
        </span>
        </div>
    </li>

</script>