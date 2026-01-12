<?php
add_action('news_category_add_form_fields', 'news_category_add_meta_field'); // добавляет поле загрузки для иконки
add_action('created_news_category', 'save_news_category_meta'); // сохраняет загруженную иконку для термина в момент добавленя
add_action('edited_news_category', 'save_news_category_meta'); // сохраняет новую иконку для термина в момент редактирования
add_action('news_category_edit_form_fields', 'news_category_edit_meta_field'); // редактирование иконки
add_action('admin_enqueue_scripts', 'enqueue_media_uploader'); //грузим js на территории админки (модальное окно для загрузки фото)
add_filter('manage_edit-news_category_columns', 'news_category_add_icon_column'); // создаем колонку для отображения загруженных иконок напротив каждого теримна
add_filter('manage_news_category_custom_column', 'news_category_icon_column_content', 10, 3); // отображаем созданную колонку для отображения загруженных иконок напротив каждого теримна

function news_category_add_icon_column($columns)
{
    $columns['news_category_icon'] = __('Icon', 'core-gamestore');
    return $columns;
}
function news_category_icon_column_content($content, $column_name, $term_id)
{
    if ($column_name !== 'news_category_icon') {
        return $content;
    }
    $icon = get_term_meta($term_id, 'news_category_icon', true);
    if ($icon) {
        $content = '<img src="' . esc_url($icon) . '" alt="" style="max-width: 100px;">';
    }
    return $content;
}
function enqueue_media_uploader()
{ // функция для загрузки медиа (на всех страницах терминов таксономии news_category)
    // isset($_GET['taxonomy']  get существует если  в адресной строке: http://localhost:8200/wp-admin/edit-tags.php?taxonomy=news_category&post_type=news
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'news_category') {
        wp_enqueue_media();
        wp_enqueue_script('news-term-meta', GAMESTORE_PLUGIN_URL . '/assets/js/news-term-meta.js', array('jquery'), null, false);
    }
}
function save_news_category_meta($term_id)
{
    if (isset($_POST['news_category_icon'])) {
        update_term_meta($term_id, 'news_category_icon', sanitize_text_field($_POST['news_category_icon']));
    }
}
function news_category_add_meta_field()
{
    ?>
    <div class="form-field term-group">
        <label for="news_category_icon"><?php _e('Icon', 'core-gamestore'); ?></label>
        <input type="text" id="news_category_icon" name="news_category_icon" value="" class="news-category-icon-field">
        <button type="button" class="upload-icon-button button"><?php _e('Upload Icon', 'core-gamestore'); ?></button>
    </div>
    <?php
}
function news_category_edit_meta_field($term)
{
    $icon = get_term_meta($term->term_id, 'news_category_icon', true); ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="news_category_icon"><?php _e('Icon', 'core-gamestore'); ?></label></th>
        <td>
            <?php if ($icon) {
                echo '<img src="' . esc_url($icon) . '" alt="">';
            } ?>
            <input type="text" style="margin-bottom:14px;" id="news_category_icon" name="news_category_icon"
                   value="<?php echo esc_attr($icon); ?>" class="news-category-icon-field">
            <button class="upload-icon-button button"><?php _e('Upload Icon', 'core-gamestore'); ?></button>
        </td>
    </tr>
    <?php
}

/*
    WordPress отправляет POST-запрос, когда ты создаёшь новый термин (нажимаешь основную кнопку “Add New …” на странице edit-tags.php?taxonomy=news_category…).
    В этом POST будут все поля формы, включая твоё кастомное поле news_category_icon — если оно заполнено.
    После успешного создания WP вызывает хук: created_news_category и твоя функция save_news_category_meta($term_id) сохранит $_POST['news_category_icon'].
 * */
