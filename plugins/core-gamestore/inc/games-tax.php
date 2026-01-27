<?php

// Register Custom Taxonomy for Languages
function gamestore_register_languages_taxonomy() {
    $labels = array(
        'name'                       => _x('Languages', 'Taxonomy General Name', 'core-gamestore'),
        'singular_name'              => _x('Language', 'Taxonomy Singular Name', 'core-gamestore'),
        'menu_name'                  => __('Languages', 'core-gamestore'),
        'all_items'                  => __('All Languages', 'core-gamestore'),
        'parent_item'                => __('Parent Language', 'core-gamestore'),
        'parent_item_colon'          => __('Parent Language:', 'core-gamestore'),
        'new_item_name'              => __('New Language Name', 'core-gamestore'),
        'add_new_item'               => __('Add New Language', 'core-gamestore'),
        'edit_item'                  => __('Edit Language', 'core-gamestore'),
        'update_item'                => __('Update Language', 'core-gamestore'),
        'view_item'                  => __('View Language', 'core-gamestore'),
        'separate_items_with_commas' => __('Separate languages with commas', 'core-gamestore'),
        'add_or_remove_items'        => __('Add or remove languages', 'core-gamestore'),
        'choose_from_most_used'      => __('Choose from the most used', 'core-gamestore'),
        'popular_items'              => __('Popular Languages', 'core-gamestore'),
        'search_items'               => __('Search Languages', 'core-gamestore'),
        'not_found'                  => __('Not Found', 'core-gamestore'),
        'no_terms'                   => __('No languages', 'core-gamestore'),
        'items_list'                 => __('Languages list', 'core-gamestore'),
        'items_list_navigation'      => __('Languages list navigation', 'core-gamestore'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => false,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
        // 'rest_base' => 'product-languages',
        // 'rewrite' => [ 'slug' => 'product-language', 'with_front' => false ],
    );
    register_taxonomy('languages', array('product'), $args);
}
add_action('init', 'gamestore_register_languages_taxonomy', 0);



// Register Custom Taxonomy for Genres
function gamestore_register_genres_taxonomy() {
    $labels = array(
        'name'                       => _x('Genres', 'Taxonomy General Name', 'core-gamestore'),
        'singular_name'              => _x('Genre', 'Taxonomy Singular Name', 'core-gamestore'),
        'menu_name'                  => __('Genres', 'core-gamestore'),
        'all_items'                  => __('All Genres', 'core-gamestore'),
        'parent_item'                => __('Parent Genre', 'core-gamestore'),
        'parent_item_colon'          => __('Parent Genre:', 'core-gamestore'),
        'new_item_name'              => __('New Genre Name', 'core-gamestore'),
        'add_new_item'               => __('Add New Genre', 'core-gamestore'),
        'edit_item'                  => __('Edit Genre', 'core-gamestore'),
        'update_item'                => __('Update Genre', 'core-gamestore'),
        'view_item'                  => __('View Genre', 'core-gamestore'),
        'separate_items_with_commas' => __('Separate genres with commas', 'core-gamestore'),
        'add_or_remove_items'        => __('Add or remove genres', 'core-gamestore'),
        'choose_from_most_used'      => __('Choose from the most used', 'core-gamestore'),
        'popular_items'              => __('Popular Genres', 'core-gamestore'),
        'search_items'               => __('Search Genres', 'core-gamestore'),
        'not_found'                  => __('Not Found', 'core-gamestore'),
        'no_terms'                   => __('No genres', 'core-gamestore'),
        'items_list'                 => __('Genres list', 'core-gamestore'),
        'items_list_navigation'      => __('Genres list navigation', 'core-gamestore'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => false,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );
    register_taxonomy('genres', array('product'), $args);
}
add_action('init', 'gamestore_register_genres_taxonomy', 0);


// Register Custom Taxonomy for Platform
function gamestore_register_platform_taxonomy() {
    $labels = array(
        'name'                       => _x('Platforms', 'Taxonomy General Name', 'core-gamestore'),
        'singular_name'              => _x('Platform', 'Taxonomy Singular Name', 'core-gamestore'),
        'menu_name'                  => __('Platforms', 'core-gamestore'),
        'all_items'                  => __('All Platforms', 'core-gamestore'),
        'parent_item'                => __('Parent Platform', 'core-gamestore'),
        'parent_item_colon'          => __('Parent Platform:', 'core-gamestore'),
        'new_item_name'              => __('New Platform Name', 'core-gamestore'),
        'add_new_item'               => __('Add New Platform', 'core-gamestore'),
        'edit_item'                  => __('Edit Platform', 'core-gamestore'),
        'update_item'                => __('Update Platform', 'core-gamestore'),
        'view_item'                  => __('View Platform', 'core-gamestore'),
        'separate_items_with_commas' => __('Separate platforms with commas', 'core-gamestore'),
        'add_or_remove_items'        => __('Add or remove platforms', 'core-gamestore'),
        'choose_from_most_used'      => __('Choose from the most used', 'core-gamestore'),
        'popular_items'              => __('Popular Platforms', 'core-gamestore'),
        'search_items'               => __('Search Platforms', 'core-gamestore'),
        'not_found'                  => __('Not Found', 'core-gamestore'),
        'no_terms'                   => __('No platforms', 'core-gamestore'),
        'items_list'                 => __('Platforms list', 'core-gamestore'),
        'items_list_navigation'      => __('Platforms list navigation', 'core-gamestore'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => false,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );
    register_taxonomy('platforms', array('product'), $args);
}
add_action('init', 'gamestore_register_platform_taxonomy', 0);

/*
    -  labels
    Массив текстов/названий, которые WordPress показывает в админке (меню, кнопки “Добавить”, “Редактировать”, “Поиск” и т.д.). Ты его уже описал в $labels.

    - hierarchical => true
    Делает таксономию иерархической как “Рубрики” (Categories):
    можно задавать родитель/дочерний термин (например: English → US English).
    Если false — будет как “Метки” (Tags), без родителей.

    - public => true
    Таксономия считается публичной: её термины и страницы терминов могут быть доступны на фронтенде (и участвовать в публичных запросах). Влияет на то, можно ли “нормально” работать с ней снаружи.

    - show_ui => true
    Показывать интерфейс управления терминами в админке (в меню, экраны добавления/редактирования).

    - show_admin_column => false
    Показывать ли колонку с этой таксономией в списке записей в админке.
    Например, в списке Products можно показывать колонку “Languages”. Тут выключено.

    - show_in_nav_menus => true
    Разрешает добавлять термины этой таксономии в Меню (Внешний вид → Меню).

    - show_tagcloud => true
    Разрешает использовать эту таксономию в виджете/экране “облако меток” (сейчас это редко нужно, но опция осталась).

    - show_in_rest => true
    Включает поддержку REST API и редактора Gutenberg (и вообще работу через /wp-json/...).
    Если нужно управлять терминами через API или чтобы таксономия корректно отображалась в блок-редакторе — ставят true.

    - rest_base => 'product-languages'
    Как будет называться endpoint в REST API.
    То есть вместо стандартного /wp-json/wp/v2/languages будет что-то вроде:
    /wp-json/wp/v2/product-languages (в зависимости от контекста/регистрации).

    - rewrite => [ 'slug' => 'product-language', 'with_front' => false ]
    Настройки “красивых ссылок” (permalinks) для страниц терминов:

    - slug: базовый путь в URL. Термин “English” будет примерно: /product-language/english/

    **with_front => false**: не добавлять префикс из настроек постоянных ссылок (например если общий префикс /blog/`, то он не будет добавлен).
 * */