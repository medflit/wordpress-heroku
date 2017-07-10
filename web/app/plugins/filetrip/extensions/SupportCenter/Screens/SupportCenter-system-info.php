<?php
if (!defined('ABSPATH')) {
    return;
}
?>
<h3 class="screen-reader-text"><?php _e( 'Server Environment', 'filetrip-plugin' ); ?></h3>
<div id="itf_wp-status-theme-status">
    <div class="changelog point-releases">
        <?php do_action('itf_wp_extension_server_environment_description');?>
    </div>
    <!-- .nav-tab-wrapper -->
    <table class="widefat" cellspacing="0">
        <thead>
            <tr>
                <th colspan="3"><?php _e( 'Server Environment', 'filetrip-plugin' ); ?></th>
            </tr>
        </thead>
        <!-- START ENVIRONMENT DETAIL -->

        <tr>
            <td><?php _e( 'Server Address', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Server IP Address', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Information about your server IP Address.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php echo $_SERVER['SERVER_ADDR']; ?>
                <?php
                if (strpos($_SERVER['SERVER_ADDR'], '192.169') === 0 || $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
                    echo '<br /><small>'.__('You are running on local development.', 'filetrip-plugin').'</small>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Web Server', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Server Software', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Web server that used by your site.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                global $is_apache, $is_IIS, $is_iis7, $is_nginx;
                if ($is_apache) {
                    echo '<strong>'. __('Apache Web Server', 'filetrip-plugin') . '</strong> <br /> <small>see on <a href="https://httpd.apache.org" target="_blank">https://httpd.apache.org</a></small>';
                } elseif ($is_iis7) {
                    echo '<strong>'. __('IIS 7 Web Server', 'filetrip-plugin') . '</strong> <br /> <small>see on <a href="http://www.iis.net/learn/get-started/whats-new-in-iis-7" target="_blank">http://www.iis.net/learn/get-started/whats-new-in-iis-7</a></small>';
                } elseif ($is_IIS) {
                    echo '<strong>'. __('IIS Web Server', 'filetrip-plugin') . '</strong> <br /> <small>see on <a href="http://www.iis.net" target="_blank">http://www.iis.net</a></small>';
                } elseif ($is_nginx) {
                    echo '<strong>'. __('NGINX Web Server', 'filetrip-plugin') . '</strong> <br /> <small>see on <a href="https://www.nginx.com" target="_blank">https://www.nginx.com</a></small>';
                } else {
                    echo '<strong>'. __('Unknown Web Server', 'filetrip-plugin') . '</strong>';
                }
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'Server Info', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Server Info', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Information about your server base where your site hosted.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                $os_ver = array(
                    'sysname' => PHP_OS,
                    'nodename' => php_uname('n'),
                    'release' => php_uname('r'),
                    'machine' => php_uname('m'),
                );
                if (function_exists('posix_uname')) {
                    $os_ver = array(
                        'sysname' => PHP_OS,
                        'nodename' => php_uname('n'),
                        'release' => php_uname('r'),
                        'machine' => php_uname('m'),
                    );
                }
                echo '<span style="width: 120px;display: inline-block;">' .__('Operating System', 'filetrip-plugin') . '</span> : ';
                $os = strtolower($os_ver['sysname']);
                switch ($os) :
                    case 'darwin':
                        echo 'OSX (Darwin Kernel)';
                        break;
                    case 'netware':
                        echo 'NetWare';
                        break;
                    case 'linux':
                        echo 'Linux';
                        break;
                    default:
                        echo esc_html($os_ver['sysname']);
                        break;
                endswitch;
                echo '<br /><span style="width: 120px;display: inline-block;">' . __('Architecture', 'filetrip-plugin') . '</span> : '. esc_html($os_ver['machine'])
                        . '<br /><span style="width: 120px;display: inline-block;">' . __('Kernel / Version', 'filetrip-plugin') . '</span> : '. esc_html($os_ver['release'])
                        . '<br /><span style="width: 120px;display: inline-block;">' . __('Node Name', 'filetrip-plugin') . '</span> : '. esc_html($os_ver['nodename'])
                        . '<br /><span style="width: 120px;display: inline-block;">' . __('Server Software', 'filetrip-plugin') . '</span> : '. esc_html( $_SERVER['SERVER_SOFTWARE'] );
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'Connection Type', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Connection Type', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current connection protocol.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                echo (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')
                    || isset( $_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
                )
                ? __('SSL Connection', 'filetrip-plugin')
                : __('Standard HTTP Connection', 'filetrip-plugin'); ?></td>
        </tr>
        <tr>
            <td><?php _e( 'Document Root', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Document Root', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Document Root of your site placed.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php esc_html_e(isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : (isset($_SERVER['CONTEXT_DOCUMENT_ROOT']) ? $_SERVER['CONTEXT_DOCUMENT_ROOT'] : __('Unknown', 'filetrip-plugin'))); ?></td>
        </tr>
        <tr>
            <td><?php _e( 'PHP Version', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'PHP Version', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'The version of PHP installed on your hosting server.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                if (defined('HHVM_VERSION')) {
                    echo 'HHVM ' . __('Version','filetrip-plugin'). HHVM_VERSION;
                    echo '<br /><small>' . __('Using HHVM') . '<a href="http://hhvm.com" target="_blank">http://hhvm.com</a></small>';
                } elseif ( function_exists( 'phpversion' ) ) {
                    echo esc_html( phpversion() );
                    if (version_compare(phpversion() , '5.7')) {
                        echo '<br /><small>' . __('Recommended PHP Version is 5.7 or later.') . '</small>';
                    }
                } else {
                    _e('Unknown', 'filetrip-plugin');
                }
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'PHP Time Limit', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'PHP Time Limit', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'The amount of time (in seconds) that your site will spend on a single operation before timing out (to avoid server lockups)', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                $time_limit = @ini_get('max_execution_time');
                if ( $time_limit < 180 && $time_limit != 0 ) {
                    echo '<span class="status-warning">'.$time_limit . ' ' . _n('second', 'seconds', $time_limit, 'filetrip-plugin').'</span>';
                    echo '<br/> <small>' . @sprintf( __( 'Recommended setting max execution time to at least 180. <br />See: <a href="%1$s" target="_blank">Increasing max execution to PHP</a>', 'filetrip-plugin' ), 'http://codex.wordpress.org/Common_WordPress_Errors#Maximum_execution_time_exceeded' ) . '</small>';
                } else {
                    echo '<span class="status-ok">'.$time_limit . ' ' . _n('second', 'seconds', $time_limit, 'filetrip-plugin').'</span>';
                }
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'MySQL Version', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'MySQL Version', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'The version of MySQL installed on your hosting server.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                /** @global wpdb $wpdb */
                global $wpdb;
                echo $wpdb->db_version();
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Max Upload Size', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'. esc_attr__( 'Max Upload Size', 'filetrip-plugin') . '" data-tip="' . esc_attr__( 'The largest file size that can be uploaded to your WordPress installation.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php

                if (wp_max_upload_size() > (8*1024*1024)) {
                    echo '<span class="status-ok">'.size_format( wp_max_upload_size() ).'</span>';
                } else {
                    echo '<span class="status-warning">'.size_format( wp_max_upload_size() ).'</span>';
                    echo '<br /><small>' . @sprintf(__('Recommended Minimum Upload Size is %1$s'), '8MB') . '</small>';
                }
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'Memory Usage', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'. esc_attr__( 'Memory Usage', 'filetrip-plugin') . '" data-tip="' . esc_attr__( 'The memory usage that used by your WordPress site.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php
                $usage = memory_get_usage(true);
                if ($usage < (64*1024*1024)) {
                    echo '<span class="status-ok">' . size_format( $usage ) .'</span>';
                } else {
                    if ($usage > (100*1024*1024)) {
                        echo '<span class="status-error">' . size_format( $usage ) . '</span>';
                        echo '<br /><small>' . __( 'Your WordPress site consume too much memory.', 'filetrip-plugin' ) . '</small>';
                    } else {
                        echo '<span class="status-warning">' . size_format( $usage ) . '</span>';
                        echo '<br /><small>' . @sprintf(__( 'Memory used by your WordPress site is above average of %1$s', 'filetrip-plugin' ), '64MB') . '</small>';
                    }
                }
                ?></td>
        </tr>
        <tr>
            <td><?php _e( 'ZipArchive', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'ZipArchive', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'ZipArchive is required for unzip uploaded themes or plugins.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php echo class_exists( 'ZipArchive' ) ?  '<span class="status-ok">'. __('Installed', 'filetrip-plugin') .'</span>' : '<span class="status-error">' .__('No', 'filetrip-plugin') . '</span><br /> <small>'.__('ZipArchive is not installed on your server, but is required for some resource.', 'filetrip-plugin') . '</small>'; ?></td>
        </tr>
        <tr>
            <td><?php _e( 'DOMDocument', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'DOMDocument', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'DOMDocument is required for some plugins make sure running properly.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php echo class_exists( 'DOMDocument' ) ?  '<span class="status-ok">'.__('Installed', 'filetrip-plugin').'</span>' : '<span class="status-error">'.__('Not Installed', 'filetrip-plugin') . '</span><br /> <small>'.__('DOMDocument is not installed on your server, but is required for some resource.', 'filetrip-plugin') . '</small>'; ?></td>
        </tr>
        <tr>
            <td><?php _e( 'IonCube Loader', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'IonCube Loader', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'IonCube Loader is required for some plugins that encoded by ioncube encryption.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td><?php echo extension_loaded( 'ionCube Loader' ) ? '<span class="status-ok">'. __('Installed', 'filetrip-plugin') .'</span>' : '<span class="status-error">'.__('Not Installed', 'filetrip-plugin') . '</span><br /> <small>'.__('IonCube Loader is required for some plugins that encoded by ioncube encryption.', 'filetrip-plugin') . '</small>'; ?></td>
        </tr>
        <tr>
            <td><?php _e( 'GD Library', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'GD Library', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'Some of plugins uses this library to resize images', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                $info = '<span class="status-error">'.esc_attr__( 'Not Installed', 'filetrip-plugin' ) . '</span>';
                if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
                    $info = '<span class="status-ok">'. esc_attr__( 'Installed', 'filetrip-plugin' ) .'</span>';
                    $gd_info = gd_info();
                    if ( isset( $gd_info['GD Version'] ) ) {
                        $info .= ' <small>' . __('Version', 'filetrip-plugin') . ' ' . $gd_info['GD Version'].'</small>';
                    }
                }
                echo $info;
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'SSL Connection Test', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'GitHub Test', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'Test SSL connection support. ', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td id="ssl-test"><span class="spinner"></span></td>
        </tr>
        <tr>
            <td><?php _e( 'GitHub Test', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'GitHub Test', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'Test Connection Via GitHub API', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td id="github-test"><span class="spinner"></span></td>
        </tr>
        <tr>
            <td><?php _e( 'WordPress Test', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="'.esc_attr__( 'WordPress Test', 'filetrip-plugin').'" data-tip="' . esc_attr__( 'Test Connection Via wordpress.org API', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td id="wordpress-test"><span class="spinner"></span></td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tfoot>
            <tr>
                <th colspan="3">
                    <?php if (is_super_admin()) { ?>
                    <button class="button button-primary" id="itf_wp_get_phpinfo"><?php _e('Show PHP Info');?></button>
                    <?php } ?>&nbsp;
                </th>
            </tr>
        </tfoot>
    </table>
</div>
<!-- #itf_wp-status-theme-status -->
<?php if (is_super_admin()) { ?>
<div id="itf_wp_php_info"></div>
<?php } ?>