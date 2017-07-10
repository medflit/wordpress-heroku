<?php
/**
 * Various helper functions
 */

/**
 * Get the common MIME-types for extensions
 * @return array
 */
function itech_arfaly_get_mime_types() {
	// Generated with dyn_php class: http://www.phpclasses.org/package/2923-PHP-Generate-PHP-code-programmatically.html
	$mimes_exts = array(
        'zip'=>
		array(
			'label'=> __('Compressed File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/zip',
                'application/octet-stream'
			),
		),
        'rar'=>
		array(
			'label'=> __('Compressed File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/x-rar-compressed', 
                'application/octet-stream'
			),
		),
        'dxf'=>
		array(
			'label'=> __('Drawing Exchange Format', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/dxf', 
                'image/vnd.dwg',
				'image/x-dwg'
			),
		),
		'dwg'=>
		array(
			'label'=> __('Architecture Design Format', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/acad', 
                'image/vnd.dwg',
				'image/x-dwg'
			),
		),
        'svg'=>
		array(
			'label'=> __('Scalable Vector Graphics', 'filetrip-plugin'),
			'mimes'=>
			array(
				'image/svg+xml'
			),
		),
        'eps'=>
		array(
			'label'=> __('Encapsulated PostScript', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/postscript'
			),
		),
        'ai'=>
		array(
			'label'=> __('Adobe Illustrator', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/postscript'
			),
		),
        'psd'=>
		array(
			'label'=> __('Photoshop', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/octet-stream'
			),
		),
        'tif'=>
		array(
			'label'=> __('Tagged Image File Format', 'filetrip-plugin'),
			'mimes'=>
			array(
				'image/x-tiff','image/tiff'
			),
		),
		'doc'=>
		array(
			'label'=> __('Microsoft Word Document', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/msword',
			),
		),
		'docx'=>
		array(
			'label'=> __('Microsoft Word Open XML Document', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			),
		),
		'xls'=>
		array(
			'label'=> __('Excel Spreadsheet', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/vnd.ms-excel',
				'application/msexcel',
				'application/x-msexcel',
				'application/x-ms-excel',
				'application/vnd.ms-excel',
				'application/x-excel',
				'application/x-dos_ms_excel',
				'application/xls',
			),
		),
		'xlsx'=>
		array(
			'label'=> __('Microsoft Excel Open XML Spreadsheet', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			),
		),
		'pdf'=>
		array(
			'label'=> __('Portable Document Format File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/pdf',
				'application/x-pdf',
				'application/acrobat',
				'applications/vnd.pdf',
				'text/pdf',
				'text/x-pdf',
			),
		),
		'psd'=>
		array(
			'label'=> __('Adobe Photoshop Document', 'filetrip-plugin'),
			'mimes'=>
			array(
				'image/photoshop',
				'image/x-photoshop',
				'image/psd',
				'application/photoshop',
				 'application/psd',
				'zz-application/zz-winassoc-psd',
				'image/vnd.adobe.photoshop',
			),
		),
		'csv'=>
		array(
			'label'=> __('Comma Separated Values File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'text/comma-separated-values',
				'text/csv',
				'application/csv',
				'application/excel',
				'application/vnd.ms-excel',
				'application/vnd.msexcel',
				'text/anytext',
			),
		),
		'ppt'=>
		array(
			'label'=> __('PowerPoint Presentation', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/vnd.ms-powerpoint',
				'application/mspowerpoint',
				'application/ms-powerpoint',
				'application/mspowerpnt',
				'application/vnd-mspowerpoint',
			),
		),
		'pptx'=>
		array(
			'label'=> __('PowerPoint Open XML Presentation', 'filetrip-plugin'),
			'mimes'=>
			array(
				'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			),
		),
		'mp3'=>
		array(
			'label'=> __('MP3 Audio File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'audio/mpeg',
				'audio/x-mpeg',
				'audio/mp3',
				'audio/x-mp3',
				'audio/mpeg3',
				'audio/x-mpeg3',
				'audio/mpg',
				'audio/x-mpg',
				'audio/x-mpegaudio',
			),
		),
		'avi'=>
		array(
			'label'=> __('Audio Video Interleave File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'video/avi',
				'video/msvideo',
				'video/x-msvideo',
				'image/avi',
				'video/xmpg2',
				'application/x-troff-msvideo',
				'audio/aiff',
				'audio/avi',
			),
		),
		'mp4'=>
		array(
			'label'=> __('MPEG-4 Video File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'video/mp4v-es',
				'audio/mp4',
				'application/mp4',
			),
		),
		'm4a'=> array(
			'label'=> __('MPEG-4 Audio File', 'filetrip-plugin'),
			'mimes'=> array(
				'audio/aac', 'audio/aacp', 'audio/3gpp', 'audio/3gpp2', 'audio/mp4', 'audio/MP4A-LATM','audio/mpeg4-generic', 'audio/x-m4a', 'audio/m4a'
			) ),
		'mov'=>
		array(
			'label'=> __('Apple QuickTime Movie', 'filetrip-plugin'),
			'mimes'=>
			array(
				'video/quicktime',
				'video/x-quicktime',
				'image/mov',
				'audio/aiff',
				'audio/x-midi',
				'audio/x-wav',
				'video/avi',
			),
		),
		'mpg'=>
		array(
			'label'=> __('MPEG Video File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'video/mpeg',
				'video/mpg',
				'video/x-mpg',
				'video/mpeg2',
				'application/x-pn-mpg',
				'video/x-mpeg',
				'video/x-mpeg2a',
				'audio/mpeg',
				'audio/x-mpeg',
				'image/mpg',
			),
		),
		'mid'=>
		array(
			'label'=> __('MIDI File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'audio/mid',
				'audio/m',
				'audio/midi',
				'audio/x-midi',
				'application/x-midi',
				'audio/soundtrack',
			),
		),
		'wav'=>
		array(
			'label'=> __('WAVE Audio File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'audio/wav',
				'audio/x-wav',
				'audio/wave',
				'audio/x-pn-wav',
			),
		),
		'wma'=>
		array(
			'label'=> __('Windows Media Audio File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'audio/x-ms-wma',
				'video/x-ms-asf',
			),
		),
		'wmv'=>
		array(
			'label'=> __('Windows Media Video File', 'filetrip-plugin'),
			'mimes'=>
			array(
				'video/x-ms-wmv',
			),
		),
	);

	return $mimes_exts;
}

/**
 * Generate slug => description array for Frontend Uploader settings
 * @return array
 */
function itech_arfaly_get_exts_descs() {
	$mimes = itech_arfaly_get_mime_types();
	$a = array();

	foreach( $mimes as $ext => $mime )
		$a[$ext] = sprintf( '%1$s (.%2$s)', $mime['label'], $ext );

	return $a;
}