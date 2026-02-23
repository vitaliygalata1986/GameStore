jQuery(document).ready(function($) {
    $('#deepseek-search-form').on('submit', function(e) {
        e.preventDefault();

        var prompt = $('#deepseek-prompt').val();
        $('#deepseek-response').html('Loading...');

        $.ajax({
            url: deepseek_ajax_params.ajax_url,
            type: 'POST',
            data: {
                action: 'deepseek_search',
                prompt: prompt,
                nonce: deepseek_ajax_params.nonce
            },
            success: function(response) {
                $('#deepseek-response').html(response);
            },
            error: function() {
                $('#deepseek-response').html('Error occurred.');
            }
        });
    });
});