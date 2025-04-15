(function($) {
    'use strict';
    
    $(document).ready(function() {
        $('#save-taxofilter-admin').on('click', function() {
            var button = $(this);
            var spinner = button.next('.spinner');
            var filters = {};
            
            $('.taxofilter-admin-option').each(function() {
                var name = $(this).attr('name').match(/\[(.*?)\]/)[1];
                filters[name] = $(this).is(':checked') ? 1 : 0;
            });
            
            spinner.addClass('is-active');
            
            $.ajax({
                url: taxoFilterAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_tax_filter_screen_options',
                    nonce: taxoFilterAdmin.nonce,
                    post_type: typenow,
                    filters: filters
                },
                success: function(response) {
                    spinner.removeClass('is-active');
                    
                    if (response.success) {
                        // Reload the page to apply changes
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    spinner.removeClass('is-active');
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
})(jQuery);