(function ($) {
    'use strict';

    /**
     * Shows spinner on form submit
     * @return {undefined}
     */
    var showSpinner = function () {
        $('form').submit(function () {
            $(this).find('.spinner').addClass('is-active');
        });
    };

    $(document).ready(function () {
        showSpinner();

        $('.tablink').click(function (event) {
            event.preventDefault();
            $('.tab').addClass('d-none');
            var tabName = $(this).data('tab');
            $('#' + tabName).removeClass('d-none');
        });

        $('#send-test-email').click(function (event) {
            event.preventDefault();
            $('form.test-email-data .response').text('');
            var form = $('form.test-email-data');
            var formData = form.serializeArray();
            var data = {
                action: 'send_test_email'
            };
            for (var i = 0; i < formData.length; i++) {
                data[formData[i]['name']] = formData[i]['value'];
            }

            $.ajax({
                url: ajax_object.url,
                data: data,
                type: 'POST',
                success: function (data) {
                    if (data) {
                        $('form.test-email-data .response').text('Mail has been sent');
                    }
                },
                error: function () {
                    $('form.test-email-data .response').text('Occurs error. Try again.');
                }
            });
        })
    });

})(jQuery);
