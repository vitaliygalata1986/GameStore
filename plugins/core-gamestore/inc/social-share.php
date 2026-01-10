<?php
function gamestore_social_share($url, $title) {
    $encoded_url = urlencode($url);
    $encoded_title = urlencode($title);

    $twitter_url = "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}";
    $facebook_url = "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}";
    $pinterest_url = "https://pinterest.com/pin/create/button/?url={$encoded_url}&description={$encoded_title}";

    return "
        <div class='social-share-buttons'>
            <a href='{$twitter_url}' class='twiter-icon' target='_blank'>Share on Twitter</a>
            <a href='{$facebook_url}' class='facebook-icon' target='_blank'>Share on Facebook</a>
            <a href='{$pinterest_url}' class='pinteres-icon' target='_blank'>Share on Pinterest</a>
        </div>
    ";
}

/*
 Функция urlencode() в PHP кодирует строку так, чтобы её можно было безопасно вставить в URL (например, в параметры после ?).
    Зачем это нужно?
        В URL нельзя напрямую использовать некоторые символы (пробелы, кириллицу, &, ?, = и т.д.), потому что они:
            либо ломают структуру ссылки,
            либо интерпретируются как специальные символы.
        urlencode() превращает такие символы в percent-encoding (формат %XX).

    Пример
        echo urlencode("Hello world!");
    Результат:
        Hello+world%21
            пробел → +
            ! → %21
    Пример с кириллицей
        echo urlencode("Привет мир");
    Результат будет примерно:
        %D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82+%D0%BC%D0%B8%D1%80
 * */