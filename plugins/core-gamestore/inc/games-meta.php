<?php
// поцепимся на хук woocommerce_product_data_tabs - там хранится информация по всем табам
add_filter('woocommerce_product_data_tabs', 'add_gamestore_tab');

/**
 * Helper function to get platforms list
 */

function add_gamestore_tab($tabs) // добавляем новый таб
{
    $tabs['games'] = array(
            'label' => __('GameStore', 'core-gamestore'),
            'target' => 'gamestore_product_data', // id данного массива
            'class' => array('show_if_simple', 'show_if_variable'), // добавим class для simple/variable product
            'priority' => 21
    );
    return $tabs;
}

// дальше будет наполнять это поле полями
add_action('woocommerce_product_data_panels', 'add_gamestore_tab_content');

function add_gamestore_tab_content()
{
    // здесь будем генерировать мета-боксы
    global $post;
    if (!$post) return;
    echo '<div id="gamestore_product_data" class="panel woocommerce_options_panel">';
    echo '<div class="options_group">';
    // simple text field
    woocommerce_wp_text_input(
            array(
                    'id' => '_gamestore_publisher',
                    'label' => __('Publisher', 'core-gamestore'),
                    'description' => __('Publisher of the game', 'core-gamestore'),
                    'desc_tip' => true, // вывод descrition в виде подсказки
                    'placeholder' => __('e.g. Ubisoft', 'core-gamestore'),
            )
    );

    // single player field
    woocommerce_wp_text_input(
            array(
                    'id' => '_gamestore_single_player',
                    'label' => __('Single Player', 'core-gamestore'),
                    'description' => __('Enter the Single Player value.', 'core-gamestore'),
                    'desc_tip' => true,
                    'placeholder' => __('e.g. Yes', 'core-gamestore'),
            )
    );

    // released field
    woocommerce_wp_text_input(
            array(
                    'id' => '_gamestore_released_date',
                    'label' => __('Released', 'core-gamestore'),
                    'description' => __('Enter the Released value.', 'core-gamestore'),
                    'desc_tip' => false, // не выводить подсказку
                    'placeholder' => __('24 January 2026', 'core-gamestore'),
                    'type' => 'date',
            )
    );

    // Multicheckbox field
    echo '<p class="form-field"><label><strong>' . __('Select Platforms', 'core-gamestore') . '</strong></label></p>';
    foreach (get_gamestore_platforms() as $slug => $label) {
        woocommerce_wp_checkbox([
                'id' => "_platform_$slug",
                'label' => $label,
                'description' => sprintf(__('Available on %s', 'core-gamestore'), $label),
        ]);
    }


    // Image Upload field
    $image = get_post_meta($post->ID, '_gamestore_image', true);
    ?>
    <p class="form-field">
        <label for="_gamestore_image"><?php _e('Game Image', 'core-gamestore'); ?></label>
        <input type="text" class="short" name="_gamestore_image" id="_gamestore_image"
               value="<?php echo esc_attr($image); ?>"/>
        <button type="button"
                class="upload_image_button button"><?php _e('Upload/Add image', 'core-gamestore'); ?></button>
    </p>

    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.upload_image_button').click(function (e) {
                e.preventDefault();
                var button = $(this);
                var custom_uploader = wp.media({
                    title: 'Insert image',
                    library: {
                        type: 'image'
                    },
                    button: {
                        text: 'Use this image'
                    },
                    multiple: false
                }).on('select', function () {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    button.prev().val(attachment.url);
                }).open();
            });
        });
    </script>

    <?php
    echo '</div>';
    echo '</div>';
}

/*
 Синхронизация вкладки и панели идёт через совпадение target и id.
    Как это работает в WooCommerce admin:
    В фильтре woocommerce_product_data_tabs ты добавляешь таб:
        'target' => 'gamestore_product_data'
    WooCommerce из этого делает ссылку на панель (условно href="#gamestore_product_data").
    // http://localhost:8200/wp-admin/post.php?post=269&action=edit#gamestore_product_data

    В woocommerce_product_data_panels ты выводишь панель с таким же id:
    <div id="gamestore_product_data" class="panel woocommerce_options_panel">
    Когда кликаешь на вкладку, WooCommerce/JS просто показывает div с этим id, а остальные панели прячет.
    То есть правило простое: target == id панели.
 * */

// в целом работа с метабоксами сводится к: регистрация метабоксов, html, сохранение
add_action('woocommerce_process_product_meta', 'save_gamestore_tab_fields');

function save_gamestore_tab_fields($post_id)
{ // эта функция будет сохранять мета поле Publisher и значения чекбоксов

    // после сабмита формы
    if (isset($_POST['_gamestore_publisher'])) {
        // сохраняем для того поста $post_id откуда она пришла. '_gamestore_publisher', - сам ключ, а значение - sanitize_text_field ($_POST['_gamestore_publisher']
        update_post_meta($post_id, '_gamestore_publisher', sanitize_text_field($_POST['_gamestore_publisher']));
    }

    if (isset($_POST['_gamestore_single_player'])) {
        // сохраняем для того поста $post_id откуда она пришла. '_gamestore_single_player', - сам ключ, а значение - sanitize_text_field ($_POST['_gamestore_single_player']
        update_post_meta($post_id, '_gamestore_single_player', sanitize_text_field($_POST['_gamestore_single_player']));
    }

    if (isset($_POST['_gamestore_released_date'])) {
        // сохраняем для того поста $post_id откуда она пришла. '_gamestore_released_date', - сам ключ, а значение - sanitize_text_field ($_POST['_gamestore_released_date']
        update_post_meta($post_id, '_gamestore_released_date', sanitize_text_field($_POST['_gamestore_released_date']));
    }

    $platforms = get_gamestore_platforms();
    foreach ($platforms as $slug => $label) {
        $key = "_platform_$slug";
        $checkbox_value = isset($_POST[$key]) ? 'yes' : 'no';
        update_post_meta(
                $post_id,
                $key,
                $checkbox_value
        );
    }

    $image = isset($_POST['_gamestore_image']) ? esc_url_raw($_POST['_gamestore_image']) : '';
    update_post_meta($post_id, '_gamestore_image', $image); // сохраняем по ключу _gamestore_image
}

/*
    Это функция WordPress, которая очищает (санitizes) URL перед сохранением в базу.
    Что она делает по сути:
        - убирает пробелы и “плохие” символы, нормализует строку URL
        - фильтрует/удаляет опасные схемы (например, чтобы нельзя было сохранить что-то вроде javascript:alert(1) и потом случайно вывести это как ссылку)
        - оставляет URL в “сыром” виде для хранения (в отличие от esc_url() — тот чаще используют при выводе на экран)


    sanitize_text_field() — это функция WordPress, которая очищает строку текста (обычно из <input type="text">) перед сохранением в БД.

    Что делает sanitize_text_field()
        - Удаляет HTML и теги (чтобы нельзя было сохранить <script>, <b> и т.п.).
        - Убирает/заменяет переносы строк (делает текст “однострочным” — \n, \r превращаются в пробелы).
        - Удаляет лишние пробелы и “мусорные”/управляющие символы.
        - Тримит (обрезает пробелы по краям).
 * */
function woo_custom_description_metabox()
{
    add_meta_box(
            'woo_custom_description_metabox', // ID метабокса (уникальный идентификатор)
            __('Game Description', 'core-gamestore'), // аголовок метабокса (“Game Description”),
            'woo_custom_description_metabox_content', // callback-функция, которая должна вывести HTML содержимое метабокса
            'product', // на каком типе записи показывать (WooCommerce товары)
            'normal', // область на странице редактирования (основная колонка, не sidebar).
            'high' // приоритет отображения (старается быть выше других метабоксов в этой области).
    );
}

add_action('add_meta_boxes', 'woo_custom_description_metabox'); // добавляет кастомный метабокс (дополнительный блок полей) в админке WordPress на странице редактирования товара

// Эта функция рисует содержимое метабокса на странице редактирования товара в админке WordPress (когда её передали как callback в add_meta_box)
function woo_custom_description_metabox_content($post)
{
    // $post — текущая запись (в твоём случае товар WooCommerce), которую сейчас редактируют.
    // get_post_meta - берёт из базы мета-поле (custom field) с ключом _gamestore_full_description для этого товара.
    // true означает “верни одно значение строкой”, а не массив.
    $content = get_post_meta($post->ID, '_gamestore_full_description', true);
    wp_editor($content, 'gamestore_full_description', array('textarea_name' => 'gamestore_full_description'));
    /*
     Выводит визуальный редактор WordPress (TinyMCE/Block editor-like classic editor) с начальным текстом $content.
        'gamestore_full_description' — ID редактора.
        'textarea_name' => 'gamestore_full_description' — имя поля в форме, поэтому при сохранении ты потом читаешь $_POST['gamestore_full_description'].
     * */
}
// Эта функция сохраняет содержимое редактора (который ты вывел через wp_editor) в мета-поле товара при нажатии Update/Publish в админке.
// WordPress вызывает хук save_post каждый раз, когда сохраняется любая запись (пост, страница, товар и т.д.). В этот момент WordPress передаёт ID записи в твою функцию.
function save_custom_description($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return; // Защита от автосохранения
    // WordPress иногда делает автосохранение черновиков. Ты не хочешь перезаписывать мета-данные во время автосейва — поэтому выходишь.
    if (!isset($_POST['gamestore_full_description'])) return; // Если на странице не было твоего редактора/поля — значит сохранять нечего.
    if (!current_user_can('edit_post', $post_id)) return; // Убеждаемся, что текущий пользователь имеет право редактировать эту запись.

    if (isset($_POST['gamestore_full_description'])) {
        update_post_meta($post_id, '_gamestore_full_description', wp_kses_post($_POST['gamestore_full_description']));
        // wp_kses_post() очищает HTML, оставляя разрешённые для контента теги (который доступны в editor WP) (как в обычном тексте поста). Это защита от опасного HTML/скриптов.
        // update_post_meta() записывает значение в таблицу мета-данных WP для этого товара под ключом _gamestore_full_description.
        /*
        Связка с wp_editor
            Ты в метабоксе сделал:
                textarea_name => 'gamestore_full_description'
                Поэтому при сохранении WordPress отправляет это поле в $_POST['gamestore_full_description'], а твой обработчик кладёт его в мету _gamestore_full_description.
         * */
    }
}

add_action('save_post', 'save_custom_description'); // обработчик на сохранение поста

function get_gamestore_platforms()
{
    return [
            'xbox' => 'Xbox',
            'playstation' => 'PlayStation',
            'microsoft' => 'Microsoft'
    ];
}

/*
 woocommerce_process_product_meta — это action-хук WooCommerce, который срабатывает когда в админке сохраняют/обновляют товар
 (страница “Edit product”, кнопка Update/Publish).

add_action('add_meta_boxes', 'woo_custom_description_metabox');
    Он срабатывает когда WordPress формирует экран редактирования записи в админке (post edit screen)
    и добавляет метабоксы (боковые/нижние блоки типа “Изображение записи”, “Рубрики”, “Произвольные поля” и т.д.).
    То есть это момент, когда ты можешь сказать: “Добавь мне ещё один блок (metabox) на страницу редактирования товара/записи”.

woocommerce_product_data_panels - отвечает за вывод панелей (контента) вкладок в блоке Product data на странице редактирования товара.

 * */
