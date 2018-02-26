;(function ($) {

    $(document).ready(function () {
        var mainApp = new Vue({
            el: '.page-content',
            data: {
            }
        });

        $('.btn-filter').on('click', function () {
            $('.filterToggle').each(function () {
                $(this).toggleClass('is_inactive');
            });
        });

    });
}(jQuery));