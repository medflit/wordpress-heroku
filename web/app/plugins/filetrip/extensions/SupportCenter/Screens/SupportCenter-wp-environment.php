<?php
if (!defined('ABSPATH')) {
	return;
}
?>
<h3 class="screen-reader-text"><?php _e( 'WordPress Environment', 'filetrip-plugin' ); ?></h3>
<div id="itf_wp-status-wordpress-environment">
    <div class="changelog point-releases">
        <?php do_action('itf_wp_extension_wordpress_environment_description');?>
    </div>
    <!-- .nav-tab-wrapper -->
    <table class="widefat" cellspacing="0">
        <thead>
            <tr>
                <th colspan="3" data-export-label="Theme Status"><?php _e( 'WordPress Environment', 'filetrip-plugin' ); ?></th>
            </tr>
        </thead>
        <tr>
            <td><?php _e( 'WordPress Version', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'WordPress Version', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current WordPress site version.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                    global $wp_version;
                    echo $wp_version;
                ?>
                &nbsp;<span id="wordpress-update"><?php _e('Requesting info ...', 'filetrip-plugin');?><span class="spinner"></span></span>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Home Url', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Home Url', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current WordPress site home url.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php echo esc_url_raw(home_url()); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Site Url', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Site Url', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current WordPress site url.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php echo esc_url_raw(site_url()); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Site Directory', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Site Directory', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current WordPress root directory placed.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php esc_html_e(ABSPATH); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'wp-content Directory', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'wp-content Directory', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current WordPress wp-content directory placed.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php esc_html_e(WP_CONTENT_DIR); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Active Theme', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Active Theme', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Your current active WordPress theme.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php esc_html_e(wp_get_theme()->get('Name')); ?> <small><?php _e('Version', 'filetrip-plugin');?>&nbsp;<?php echo wp_get_theme()->get('Version');?></small>
                <?php if (is_child_theme()) {
                    $parent_theme = wp_get_theme()->parent();
                    echo '<br /> <small>'.
                        sprintf(
                            __('Your active theme currently as child theme of : <strong>%1$s</strong> Version : %2$s', 'filetrip-plugin'),
                            '<a href="'. admin_url('themes.php?theme='. $parent_theme->get_template()). '">'.$parent_theme->get('Name') .'</a>',
                            $parent_theme->get('Version')
                        ) . '</small>';
                } ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Theme Author', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Theme Author', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Author of current active theme.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <a href="<?php echo esc_url_raw(wp_get_theme()->get('AuthorURI'));?>" target="_blank"><?php esc_html_e(wp_get_theme()->get('Author')); ?></a>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Theme Uri', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Theme Author', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Author of current active theme.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <a href="<?php echo esc_url_raw(wp_get_theme()->get('ThemeURI'));?>" target="_blank"><?php esc_html_e(esc_url_raw(wp_get_theme()->get('ThemeURI'))); ?></a>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Debug Mode', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Debug Mode', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Debug mode activated or not.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    echo '<span class="status-warning">'. __('Active', 'filetrip-plugin') .'</span>';
                    echo '<br /><small>'.__('You are in debug mode, make sure you are not in production site', 'filetrip-plugin').'</small>';
                } else {
                    echo '<span class="status-ok">' . __( 'Inactive', 'filetrip-plugin' ) . '</span>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Language', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Language', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'The current language used by WordPress. Default = English.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php echo get_locale(); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Multi Site', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Multi Site', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Whether or not you have WordPress Multi Site enabled.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php echo is_multisite() ? __('Yes', 'filetrip-plugin') : __('No', 'filetrip-plugin'); ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Comments', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Comments', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Comments exists on your site.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                $comments_count = wp_count_comments();
                echo '<span style="width: 180px;display: inline-block;">' . __('Total Comments', 'filetrip-plugin') . '</span> : '. $comments_count->total_comments
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Comments in Moderation', 'filetrip-plugin') . '</span> : '. $comments_count->moderated
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Comments Approved', 'filetrip-plugin') . '</span> : '. $comments_count->approved
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Comments in Trash', 'filetrip-plugin') . '</span> : '. $comments_count->trash
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Comments in Spam', 'filetrip-plugin') . '</span> : '. $comments_count->spam
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Posts', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Posts', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Posts exists on your site.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                $count_posts = wp_count_posts();
                echo '<span style="width: 180px;display: inline-block;">' . __('Total Posts', 'filetrip-plugin') . '</span> : '. ($count_posts->draft + $count_posts->trash +$count_posts->publish)
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Published Posts', 'filetrip-plugin') . '</span> : '. $count_posts->publish
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Draft Posts', 'filetrip-plugin') . '</span> : '. $count_posts->draft
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Posts in Trash', 'filetrip-plugin') . '</span> : '. $count_posts->trash
                ?>
            </td>
        </tr>
        <tr>
            <td><?php _e( 'Filetrip Uploaders', 'filetrip-plugin' ); ?></td>
            <td class="help"><?php echo '<a href="#" class="help_tip" data-title="' . esc_attr__( 'Filetrip Uploaders', 'filetrip-plugin' ) . '" data-tip="' . esc_attr__( 'Filetrip Uploaders exists on your site.', 'filetrip-plugin' ) . '">?</a>'; ?></td>
            <td>
                <?php
                $count_filetrips = wp_count_posts('filetrip');
                echo '<span style="width: 180px;display: inline-block;">' . __('Total Filetrip Uploaders', 'filetrip-plugin') . '</span> : '. ($count_filetrips->draft + $count_filetrips->trash +$count_filetrips->publish)
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Published Filetrip Uploaders', 'filetrip-plugin') . '</span> : '. $count_filetrips->publish
                    . '<br /><span style="width: 180px;display: inline-block;">' . __('Filetrip Uploaders in Trash', 'filetrip-plugin') . '</span> : '. $count_filetrips->trash
                ?>
            </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tfoot>
        <tr>
            <th colspan="3">&nbsp;</th>
        </tr>
        </tfoot>
    </table>
</div>
<!-- #itf_wp-status-theme-status -->