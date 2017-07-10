<table class="widefat">

	<thead>

		<tr>

			<th scope="col"><?php filetrip_bkp_backups_number( $schedule ); ?></th>
			<th scope="col"><?php _e( 'Size', 'filetrip-plugin' ); ?></th>
			<th scope="col"><?php _e( 'Type', 'filetrip-plugin' ); ?></th>
			<th scope="col"><?php _e( 'Actions', 'filetrip-plugin' ); ?></th>
            <th scope="col"><?php _e( 'Send to cloud', 'filetrip-plugin' ); ?></th>
		</tr>

	</thead>

	<tbody>

		<?php if ( $schedule->get_backups() ) {

			$schedule->delete_old_backups();

			foreach ( $schedule->get_backups() as $file ) {

				if ( ! file_exists( $file ) ) {
					continue;
				}

				filetrip_bkp_get_backup_row( $file, $schedule );

			}

		} else { ?>

			<tr>
				<td class="filetrip-no-backups" colspan="4"><?php _e( 'This is where your backups will appear once they are been generated.', 'filetrip-plugin' ); ?></td>
			</tr>

		<?php } ?>

	</tbody>

</table>