jQuery(function($){
    $(document).on('click', '#deepseek-generate-btn', function(){
        const btn = $(this);
        const productId = btn.data('product-id');
        const nonce = btn.data('nonce');
        const extra = $('#deepseek_extra').val();

        $('#deepseek-gen-status').text('Generating...');

        $.ajax({
            url: deepseekWoo.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: deepseekWoo.action,
                product_id,
                nonce,
                extra
            },
            success: function(res){
                if (res && res.success) {
                    const text = res.data.text || '';

                    // 1) Classic editor / textarea fallback
                    const $ta = $('#excerpt');
                    if ($ta.length) $ta.val(text);

                    // 2) TinyMCE (если используется)
                    if (typeof tinymce !== 'undefined') {
                        const ed = tinymce.get('excerpt');
                        if (ed) ed.setContent(text);
                    }

                    $('#deepseek-gen-status').text('Done! Inserted into Short description (save to persist).');
                } else {
                    const msg = res?.data?.message || 'Unknown error';
                    $('#deepseek-gen-status').text('Error: ' + msg);
                }
            },
            error: function(xhr){
                $('#deepseek-gen-status').text('HTTP error: ' + xhr.status);
            }
        });
    });
});

/*
    1) Это обновляет “сырое” textarea
    $('#excerpt') находит элемент <textarea id="excerpt">
    .val(text) записывает текст прямо в textarea
    Это сработает, если TinyMCE не включён (или как запасной вариант)

    2) Это обновляет визуальный редактор TinyMCE
    if (typeof tinymce !== 'undefined') {
        const ed = tinymce.get('excerpt');
        if (ed) ed.setContent(text);
    }

    tinymce — глобальный объект, который существует, если TinyMCE загружен
    tinymce.get('excerpt') ищет редактор, привязанный к textarea с id excerpt
    setContent(text) вставляет текст в визуальный редактор (чтобы ты сразу видел его в Visual)
* */