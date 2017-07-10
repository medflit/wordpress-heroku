;(function ($) {
    $(document).ready(function () {
        $('.button-selector').not('.primary-button').on('click', function (e) {
            e.preventDefault();
            $(this).addClass('button-primary');
            $('.button-selector').not(this).removeClass('button-primary');
            var _target = $(this).attr('data-target');
            if (_target.length) {
                var target1 = $('.plugin-card');
                var t2 = target1.not(_target);
                t2.addClass('hidden');
                t2.removeClass('unhidden');
                target1.removeClass('the-odd the-even');
                var c = 0;
                $('#itf_wp_extensions').find(_target).each(function () {
                    $(this).addClass('unhidden');
                    $(this).removeClass('hidden');
                    c++;
                    if ((c % 2)  === 0) {
                        $(this).addClass('the-even');
                    } else {
                        $(this).addClass('the-odd');
                    }
                });
            }
        })
    })
})(jQuery);