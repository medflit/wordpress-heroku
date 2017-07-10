<?php
/**
 * @internal iTechFlare\WP\Plugin\FileTrip\Core\AdminMenu
 */
if (!isset($this) || ! class_exists('\\iTechFlare\\WP\\Plugin\\FileTrip\\Core\\AdminMenu')
	|| ! $this instanceof iTechFlare\WP\Plugin\FileTrip\Core\AdminMenu
) {
	return;
}

/**
 * @var \iTechFlare\WP\Plugin\FileTrip\Core\ExtensionLoader
 */
$loader         = $this->getExtensionLoader();
$isCoreShown    = $this->isCoreExtensionShown();
$libUrl         = $this->libraryUrl();
$core_extension = $this->getCoreExtension();
?>
<div class="wrap">
	<h1><?php echo $this->getMenuTitle() . ' ' .  __('Extensions', 'filetrip-plugin');?></h1>
	<br />
	<hr />
	<p>
		<span class="button button-selector button-primary" data-target=".plugin-card"><?php _e('All', 'filetrip-plugin');?></span>
		<?php if ($isCoreShown && ! empty($core_extension)) : ?>
			<span class="button button-selector" data-target=".core-extension"><?php _e('Core Extension', 'filetrip-plugin');?></span>
			<span class="button button-selector" data-target=".non-core"><?php _e('Non Core Extension', 'filetrip-plugin');?></span>
		<?php endif;?>
		<span class="button button-selector" data-target=".active-extension"><?php _e('Active Extension', 'filetrip-plugin');?></span>
		<span class="button button-selector" data-target=".inactive-extension"><?php _e('Inactive Extension', 'filetrip-plugin');?></span>
	</p>
	<hr /><br />
	<form id="itf_wp_extensions" method="post">
		<div class="wp-list-table widefat itf_wp-module-install">
			<h2 class="screen-reader-text"><?php _e(sprintf('%s Module List', $this->getMenuTitle()), 'filetrip-plugin');?></h2>
			<div id="itf_wp-module-list">
				<?php
				$count = 1;
				foreach ((array) $this->getAllAvailableExtensions() as $extensionName) {
					$extension = $loader->getExtension($this->getName(), $extensionName);
					if (! $extension || ! $isCoreShown && ! empty($core_extension) && in_array($extensionName, $core_extension)) {
						continue;
					}

					/**
					 * @var \iTechFlare\WP\Plugin\FileTrip\Core\Abstracts\FlareExtension
					 */
					$icon = $extension->extensionGetIcon();
					$icon = trim($icon) != '' ? $icon : dirname($libUrl) .'/assets/images/icon-unavailable.png';
					$author = $extension->extensionGetAuthor();
					$author_uri = $extension->extensionGetAuthorUri();
					$description = $extension->extensionGetDescription();
					if (trim($description) == '') {
						$desc =__('Description unavailable', 'filetrip-plugin');
						$desc .= str_repeat(' &nbsp;', (123 - strlen($desc))/2);
						$description = '<span style="color: #999;"><em>'.$desc.'</em></span>';
					} else {
						$description  = substr($description, 0, 120).(strlen($description) < 120 ? '': ' ...');
						$description .= strlen($description) < 120 ? str_repeat(' &nbsp;', (123 - strlen($description))/2) :'';
					}

					$extension_uri = $extension->extensionGetUri();
					$extension_uri = $extension_uri != ''
						? "<strong><a target=\"_blank\" title=\"".esc_attr__('Visit Module URL', 'filetrip-plugin')."\" href=\"{$extension_uri}\">".__('Visit Extension URL', 'filetrip-plugin')."</a></strong>"
						: '<span style="color:#999;"><em>'.__('Module URL Unavailable', 'filetrip-plugin').'</em></span>';
					// classes
					$class = in_array($extensionName, $core_extension) ? 'core-extension' : 'non-core';
					$class .= $extension->extensionHasLoaded() ? ' active-extension' : ' inactive-extension';
					$class .= ($count % 2) === 0  ? ' the-even' : ' the-odd';
					$count++;
					?>
					<div class="plugin-card <?php echo $class;?>">
						<div class="plugin-card-top">
							<div class="name column-name">
								<h3>
									<?php echo $extension->extensionGetName();?>
									<img src="<?php echo $icon;?>" class="extension-icon plugin-icon" alt="<?php esc_attr_e($extension->extensionGetName());?>">
								</h3>
							</div>
							<div class="action-links">
								<ul class="plugin-action-buttons">
									<?php if (in_array($extensionName, $core_extension)) { ?>
										<li><span class="install-now button disabled" disabled="disabled" data-slug="<?php echo esc_attr($extensionName);?>" aria-label="<?php esc_attr_e(__('Activate', 'filetrip-plugin') . ' ' . $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>" data-name="<?php esc_attr_e( $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>"><?php _e('Core Activated', 'filetrip-plugin');?></span></li>
									<?php } else { ?>
										<?php if ($extension->extensionHasLoaded()) { ?>
											<li><a class="install-now button" data-slug="<?php echo esc_attr($extensionName);?>" href="<?php menu_page_url($this->getSlugExtension());?>&amp;extension_action=deactivate&amp;extension=<?php echo esc_attr(str_replace('\\', '-', $extensionName));?>&amp;_wpnonce=<?php echo $this->getNonce();?>" aria-label="<?php esc_attr_e(__('Activate', 'filetrip-plugin') . ' ' . $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>" data-name="<?php esc_attr_e( $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>"><?php _e('Deactivate', 'filetrip-plugin');?></a></li>
										<?php } else { ?>
											<li><a class="install-now button button-primary" data-slug="<?php echo esc_attr($extensionName);?>" href="<?php menu_page_url($this->getSlugExtension());?>&amp;extension_action=activate&amp;extension=<?php echo  esc_attr(str_replace('\\', '-', $extensionName));?>&amp;_wpnonce=<?php echo $this->getNonce();?>" aria-label="<?php esc_attr_e(__('Deactivate', 'filetrip-plugin') . ' ' . $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>" data-name="<?php esc_attr_e( $extension->extensionGetName() . ' ' . $extension->extensionGetVersion());?>"><?php _e('Activate', 'filetrip-plugin');?></a></li>
										<?php } ?>
									<?php } ?>
								</ul>
							</div>
							<div class="desc column-description">
								<p><?php echo $description;?></p>
								<p class="authors"><cite>By
										<?php if (!empty($author)) { ?>
										<a href="<?php echo $author_uri;?>" target="_blank"><?php echo $author;?></a>
										<?php } else { ?>
											<span style="color:#999"><em><?php _e('Unknown Author', 'filetrip-plugin');?></em></span>
										<?php } ?>
									</cite>
								</p>
							</div>
						</div>
						<div class="plugin-card-bottom">
							<div class="vers column-rating">
								<div class="star-rating">
									<strong><?php _e('Version :', 'filetrip-plugin');?></strong> <?php echo $extension->extensionGetVersion();?>
								</div>
							</div>
							<div class="column-updated">
								<?php echo $extension_uri;?>
							</div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</form>
</div>
<?php
