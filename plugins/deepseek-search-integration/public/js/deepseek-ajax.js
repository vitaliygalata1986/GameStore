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
            success: function(res) {
                if (res && res.success) {
                    $('#deepseek-response').text(res.data.text || '');
                } else {
                    const msg = (res && res.data && res.data.message) ? res.data.message : 'Unknown error';
                    $('#deepseek-response').text('Error: ' + msg);
                }
            },
            error: function(xhr) {
                $('#deepseek-response').text('Error: HTTP ' + xhr.status);
            }
        });
    });
});