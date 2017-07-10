;(function ($) {
    $(document).ready(function() {
        var onProgress = false;
        jQuery('#itf_wp_get_phpinfo').on('click', function () {
            var _target = $('#itf_wp_php_info');
            if (!_target.length) {
                return;
            }
            if ($(this).hasClass('success') && _target.html().length > 10) {
                if (!onProgress && !_target.is(':hidden')) {
                    onProgress = true;
                    $(this).html(itf_wp_translator.show_php);
                    _target.slideUp(function () {
                        onProgress = false;
                    });
                } else if (!onProgress) {
                    onProgress = true;
                    $(this).html(itf_wp_translator.hide_php);
                    _target.slideDown(function () {
                        onProgress = false;
                        $('html, body').animate({scrollTop: _target.offset().top + 50 + 'px'});
                    });
                }
                return;
            }

            var _this = this;
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: "json",
                data: {
                    'action': 'itf_wp_extension_status',
                    'detail': 'phpinfo'
                },
                before: function () {
                    var s_s = $(_this).next('.itfwp-info');
                    if (s_s.length) {
                        s_s.remove();
                    }
                    $(_this).attr('disabled', true);
                },
                success: function(response) {
                    try {
                        response.message = response.message.replace(/(\/title\>)/g, '$1<style>td.v, tr.v td{word-wrap:break-word;-ms-word-break: break-all;word-break: break-all;}<\/style>');
                    } catch(err) {
                        console.log(err);
                    }
                    var iframe = document.createElement('iframe');
                        iframe.src = 'about:blank';//'data:text/html;charset=utf-8,' + encodeURI(response.message);
                        iframe.style = "width:100%;height:400px;border:0;";
                        iframe.frameBorder = 0;
                    _target.html(iframe);
                    var doc = iframe.contentWindow.document;
                    doc.writeln(response.message);
                    _target.slideDown();
                    $(_this).removeAttr('disabled');
                    $(_this).addClass('success');
                    $(_this).html(itf_wp_translator.hide_php);
                    $('html, body').animate({scrollTop: _target.offset().top + 50 + 'px'});
                },
                error: function () {
                    var _info = $('<span class="itfwp-info status-error" style="margin: 0 10px;position:relative;top:3px;font-size:80%;">' + itf_wp_translator.error_notification + '</span>');
                    $(_this).after(_info);
                    _info.fadeOut(3000, function () {
                        $(this).remove();
                    });
                },
                complete: function () {
                    $(_this).removeAttr('disabled');
                }
            });
        });
    });
})(jQuery);