(function ($) {

    $('.ng-expanding-archives-wrap .expanding-archives-title a').click(function (e) {
        e.preventDefault();

        var target = $(this).attr('href');

        $(target).slideToggle();
    });

    $('.ng-expanding-archives-wrap a.expanding-archives-clickable-month').click(function (e) {
        e.preventDefault();

        var childExpander = $(this).find('.expand-collapse');

        // Already done the ajax call
        if ($(this).hasClass('expandable-archive-rendered-true')) {
            $(this).next('.expanding-archive-month-results').slideToggle();

            if ($(childExpander).hasClass('archive-expanded')) {
                $(childExpander).removeClass('archive-expanded').html('+');
            }
            else {
                $(childExpander).addClass('archive-expanded').html('&ndash;');
            }
        }
        else {
            $('.fa', this).addClass('fa-spinner fa-spin');

            var thisMonth = $(this);

            var data = {
                action: 'expanding_archives_load_monthly',
                month: $(this).data('month'),
                year: $(this).data('year'),
                nonce: expanding_archives.nonce
            };

            $.post(expanding_archives.ajaxurl, data, function (response) {
                $(thisMonth).addClass('expandable-archive-rendered-true');
                $('.fa', thisMonth).removeClass('fa-spinner fa-spin');
                var archiveResults = $(thisMonth).next('.expanding-archive-month-results');
                $(archiveResults).html(response.data).slideDown();

                if ($(childExpander).hasClass('archive-expanded')) {
                    $(childExpander).removeClass('archive-expanded').html('+');
                }
                else {
                    $(childExpander).addClass('archive-expanded').html('&ndash;');
                }

            });
        }
    });

})(jQuery);