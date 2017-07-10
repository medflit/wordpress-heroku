<?php

require dirname( __FILE__ ) . '/server.php';
require dirname( __FILE__ ) . '/task.php';

if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require dirname( __FILE__ ) . '/namespace.php';
	add_action( 'wp_ajax_nopriv_filetrip_backdrop_run', 'FILETRIP\Backdrop\Server::spawn' );
}
else {
	add_action( 'wp_ajax_nopriv_filetrip_backdrop_run', 'FILETRIP_Backdrop_Server::spawn' );
}
