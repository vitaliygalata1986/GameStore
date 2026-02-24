<?php

namespace Vitos\DeepSeek\Admin;

defined('ABSPATH') || exit;

class DeepSeek_Admin
{
    const OPTION_KEY = 'deepseek_api_key';

    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page(): void
    {
        add_options_page(
            'DeepSeek Settings',
            'DeepSeek',
            'manage_options',
            'deepseek-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void
    {

        register_setting(
            'deepseek_settings_group',
            self::OPTION_KEY,
            [
                'type' => 'string',
                'sanitize_callback' => function ($value) {
                    return sanitize_text_field(trim((string)$value));
                },
                'default' => '',
            ]
        );
        add_settings_section(
            'deepseek_main',
            'API Settings',
            function () {
                echo '<p>Вставь API ключ DeepSeek. Он будет храниться в базе WordPress (options).</p>';
            },
            'deepseek-settings'
        );

        add_settings_field(
            'deepseek_api_key_field',
            'DeepSeek API Key',
            [$this, 'render_api_key_field'],
            'deepseek-settings',
            'deepseek_main'
        );
    }

    public function render_api_key_field(): void
    {
        $value = (string)get_option(self::OPTION_KEY, '');
        echo '<input type="password" name="' . esc_attr(self::OPTION_KEY) . '" value="' . esc_attr($value) . '" style="width:420px;" autocomplete="off" />';
    }

    public function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) return;

        echo '<div class="wrap">';
        echo '<h1>DeepSeek Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('deepseek_settings_group');
        do_settings_sections('deepseek-settings');
        submit_button('Save');
        echo '</form>';
        echo '</div>';
    }

    public static function get_api_key(): string
    {
        return trim((string)get_option(self::OPTION_KEY, ''));
    }
}

/*
   ABSPATH определён только когда WordPress загрузился.
   Если кто-то откроет файл напрямую по URL — WordPress не загружен → ABSPATH нет → exit.
   Это стандартная защита плагинов.

   const OPTION_KEY = 'deepseek_api_key'; - Мы храним API key в таблице wp_options. Название записи в базе: deepseek_api_key. Константа нужна, чтобы не писать строку 20 раз и не ошибиться.

   Когда ты создаёшь объект new Deepseek_Admin();, срабатывает __construct() и он регистрирует 2 события:
       admin_menu
           срабатывает, когда WP строит левое меню админки
           мы добавляем пункт “DeepSeek” в “Settings”
       admin_init
           срабатывает при загрузке админки
           мы регистрируем настройки (Settings API): опцию, секции, поля

       Добавление страницы в Settings → DeepSeek
        add_options_page(
           'DeepSeek Settings',     // title страницы (в <title> и заголовках)
           'DeepSeek',              // текст пункта меню
           'manage_options',        // capability: кто имеет доступ (админы)
           'deepseek-settings',     // slug страницы (идентификатор)
           [$this, 'render_settings_page'] // callback: что рисовать
       );
       Итог:
           появляется Настройки → DeepSeek
           URL будет примерно:
           wp-admin/options-general.php?page=deepseek-settings

       register_settings — “регистрируем” настройку и поля

        register_setting(
           'deepseek_settings_group', // группа (используется в settings_fields)
           self::OPTION_KEY,          // имя опции в базе (deepseek_api_key)
           [
               'type' => 'string',
               'sanitize_callback' => function ($value) {
                   return sanitize_text_field(trim((string) $value));
               },
               'default' => '',
           ]
       );


       Что это даёт:
           WordPress теперь официально знает, что есть опция deepseek_api_key

       Когда ты отправляешь форму, WP:
           берёт значение из POST
           прогоняет через sanitize_callback
           сохраняет в wp_options

       sanitize_callback:
           trim убирает пробелы по краям
           sanitize_text_field убирает мусор/теги/переносы

       add_settings_section — секция на странице

        add_settings_section(
           'deepseek_main',       // ID секции
           'API Settings',        // заголовок секции
           function () { echo '<p>...</p>'; }, // описание
           'deepseek-settings'    // page slug (куда выводить)
       );

       Это просто “блок” на странице, куда будут добавляться поля.


       add_settings_field — поле ввода

        add_settings_field(
           'deepseek_api_key_field',      // ID поля
           'DeepSeek API Key',            // label слева
           [$this, 'render_api_key_field'], // callback: HTML поля
           'deepseek-settings',           // page slug
           'deepseek_main'                // секция, куда вставить
       );

       То есть WP знает: на странице deepseek-settings, в секции deepseek_main, показать поле, которое рисует render_api_key_field().

       render_api_key_field — HTML инпута
       $value = (string) get_option(self::OPTION_KEY, '');
       echo '<input type="password" name="' . esc_attr(self::OPTION_KEY) . '" value="' . esc_attr($value) . '" ... />';

       get_option('deepseek_api_key') берёт сохранённый ключ из базы.
       name="deepseek_api_key" важно: по этому имени WP понимает, что сохранять.
       type="password" скрывает символы на экране.
       esc_attr защищает от поломки HTML и XSS.

       render_settings_page — рисуем страницу целиком
       if (!current_user_can('manage_options')) return;
       Ещё раз защита: только админы.

       echo '<form method="post" action="options.php">';
       settings_fields('deepseek_settings_group');
       do_settings_sections('deepseek-settings');
       submit_button('Save');

       action="options.php"
       WordPress стандартно обрабатывает сохранение настроек через wp-admin/options.php.

       settings_fields('deepseek_settings_group')
           выводит скрытые поля (nonce, option_page, и т.д.)
           без этого сохранение не пройдет (WP не примет запрос)

       do_settings_sections('deepseek-settings')
           выводит все секции и все поля, которые мы зарегистрировали для страницы deepseek-settings

       submit_button('Save')
       кнопка “Save”

В итоге цепочка такая
   Ты открыл Settings → DeepSeek
   WP вызвал render_settings_page()
   Эта функция вывела форму + settings_fields() + do_settings_sections()
   do_settings_sections() вызвал render_api_key_field() и показал input
   Ты нажал Save → POST ушёл в options.php
   WP прогнал sanitize_callback → сохранил deepseek_api_key в wp_options
*/