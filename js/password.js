jQuery(document).ready(function($) {
    $('.rwmb-password-toggle').on('click', function(e) {
        e.preventDefault();
        var input = $('#' + $(this).data('for'));
        var icon = $(this).find('.dashicons');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            input.attr('type', 'password');
            icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });
});