<?php
/**
 * System Status
 */
if (!isset($this) || ! $this instanceof \iTechFlare\WP\iTechFlareExtension\SupportCenter) {
	return;
}
use iTechFlare\WP\Plugin\FileTrip\iTechFlareCore;
$menu_url = menu_page_url(iTechFlareCore::getSlug().'_flare_system_status', false);
$page_request = isset($_GET['detail']) ? $_GET['detail'] : null;
?>

	<div class="wrap about-wrap itf_wp-wrap" style="float:left">
		<h1><?php printf(__( 'Welcome to %1$s', 'filetrip-plugin' ), esc_html($this->extensionGetName())); ?></h1>
		<div class="about-text">
			<?php echo $this->extensionGetDescription(); ?>
			<br />
			<small>
				<em>
				<?php echo iTechFlareCore::PLUGIN_NAME; ?> <?php _e('Version ', 'filetrip-plugin');?> <?php echo \Filetrip_Constants::VERSION;?>
				</em>
			</small>
		</div>
		<!-- about-text -->
		<div class="wp-badge" style='background-color:#86a477;background-image:url(<?php echo json_encode($this->extensionGetIcon());?>'>
			<?php echo __('Version', 'filetrip-plugin') . ' ' . $this->extensionGetVersion();?>
		</div>

		<!-- Featured Support Links -->
		<div class="itf-featured-support">
			<div class="feature-section no-heading three-col">
				<div class="col">
					<a href="https://www.itechflare.com/docs/filetrip-documentation/" target="_blank">
						<span class="dashicons dashicons-book-alt"></span>
						<h3>Plugin Documentation</h3>
						<p>Learn how to install, setup, use and customize our products from our documentation center</p>
					</a>
				</div>
				<div class="col">
					<a href="https://www.itechflare.com/support/" target="_blank">
						<span class="dashicons dashicons-sos"></span>
						<h3>Support</h3>
						<p>Envato market purchases include six month of complimentary standard support</p>
					</a>
				</div>
				<div class="col">
					<a href="mailto:development@itechflare.com" target="_blank">
						<span class="dashicons dashicons-share-alt"></span>
						<h3>Customization</h3>
						<p>Do you want to customize our products and do not have the time or skills for it, get in touch with us.</p>
					</a>
				</div>
			</ul>
		</div>

		<!-- .wp-badge -->
		<h2 class="nav-tab-wrapper wp-clearfix">
			<?php
				// Dynamically populate tab header title and links
				$i = 0;
				foreach( $this->getExtensionTabs() as $tab)
				{
					if (!file_exists($tab['include'])) {
						continue;
					}
					// First time opening the page, default to first tab to be active
					if($i == 0 && !isset($page_request) ){
						?>

						<a href="<?php echo esc_url_raw($menu_url).'&amp;detail='.$tab['slug'];?>" class="nav-tab nav-tab-active"><?php esc_attr_e( $tab['title'] ); ?></a>
						
						<?php
						$i++;
						continue;
					}

					?>

					<a href="<?php echo esc_url_raw($menu_url).'&amp;detail='.$tab['slug'];?>" class="nav-tab<?php echo ( isset($page_request) && $page_request == $tab['slug']) ? ' nav-tab-active':'';?>"><?php esc_attr_e( $tab['title'] ); ?></a>
					
					<?php
				}
			?>
		</h2>
		<?php
			$tabs = $this->getExtensionTabs();
			if(!isset($page_request) || !is_string($page_request) || !isset($tabs[$page_request])) {
				$tab = reset($tabs);
			} else {
				$tab = $tabs[$page_request];
			}
			if (!empty($tab['include']) && file_exists($tab['include'])) {
				require_once($tab['include']);
				do_action( $tab['action'] );
			}
		?>
		
	</div>

<?php

