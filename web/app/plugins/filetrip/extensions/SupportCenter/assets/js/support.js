/*!
 * Support TechFlare Core Bundle
 */

/*
 * Bring it Back to Default as safe object Data Access
 */
if (typeof itf_wp_translator != 'object') {
	var itf_wp_translator = {
		"show_php":"Show PHP Info",
		"hide_php":"Hide PHP Info",
		"json_ok":"Ok",
		"check_update_fail":"Could not check Update",
		"error_notification":"There was error"
	};
}

(function ($) {
	/*!
	 * Document Ready
	 */
	$(document).ready( function($) {
		if ($.fn.pointer) {
			var wp_button_pointer_array = [];
			$('.help_tip').on('click', function (e) {
				e.preventDefault();
				var __tile = $(this).attr('data-title') || '&nbsp;';
				var __content = '<h3>' + __tile + '</h3>'
					+ '<p>' + $(this).attr('data-tip') + '<p>';
				if ($('.wp-pointer').is(":visible")) {
					// if a pointer is already open...
					var openid = $('.wp-pointer:visible').attr("id").replace('wp-pointer-', '');
					openid = parseInt(openid);
					if (typeof wp_button_pointer_array[openid] == 'object') {
						$(wp_button_pointer_array[openid]).pointer('toggle');
					}
				}
				//jQuery selector to point to
				$(this).pointer({
					content: __content,
					position: 'bottom',
					show: function (event, t) {
						var id_element = t.pointer[0].id;
						var pointerid = id_element.replace('wp-pointer-', '');
						pointerid = parseInt(pointerid);
						wp_button_pointer_array[pointerid] = this;
						t.pointer[0].className = t.pointer[0].className.replace(/\s+(wp-pointer-[^\s'"])/, '$1');
						// console.log(t);
						t.pointer.show();
						t.opened();
					}
				}).pointer('open').pointer('repoint');
			});
		}

		/**
		 * STATUS TEST
         */
		var ssl_test = $('#ssl-test'),
			wp_update = $('#wordpress-update'),
			wp_test = $('#wordpress-test'),
			gh_test = $('#github-test');

		/**
		 * Connection test
		 *
		 * @param {object} selector
		 * @param {string} target
         * @param {function} cb
         */
		function connectionTest(selector, target, cb)
		{
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				dataType: "json",
				data: {
					'action': 'itf_wp_extension_status',
					'detail': target
				},
				success: function(response) {
					if (!response.error) {
						selector.html('<span class="status-ok">' + itf_wp_translator.json_ok +'</span>');
					} else {
						selector.html('<span class="status-error">'+response.error+'</span>');
					}
					if (typeof cb == 'function') {
						cb();
					}
				},
				error: function(xhr) {
					selector.html('<span class="status-error">'+ xhr.responseText + '</span>');
					if (typeof cb =='function') {
						cb();
					}
				}
			});
		}
		if (wp_test.length || gh_test.length || wp_update.length || ssl_test.length) {
			connectionTest(ssl_test, 'ssltest', function () {
				connectionTest(gh_test, 'github', function () {
					connectionTest(wp_test, 'wordpress', function(){});
				});
			});
			if (wp_update.length) {
				$.ajax({
					url: ajaxurl,
					method: 'POST',
					dataType: "json",
					data: {
						'action': 'itf_wp_extension_status',
						'detail': 'wpupdate'
					},
					success: function (response) {
						if (response.message) {
							wp_update.html('<span class="status-ok">' + response.message + '</span>');
						} else {
							wp_update.html('<span class="status-error">' + response.error + '</span>');
						}
					},
					error: function() {
						wp_update.html('<span class="status-error">' + itf_wp_translator.check_update_fail +'</span>');
					}
				});
			}
		}
	});
})(jQuery);
