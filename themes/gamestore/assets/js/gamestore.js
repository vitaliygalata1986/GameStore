document.addEventListener('DOMContentLoaded', function () {

    const styleToggle = document.querySelector(".header-mode-switcher");
    const searchContainer = document.querySelector('.popup-games-search-container');
    const searchResults = document.querySelector('.popup-search-results');
    const searchInput = document.getElementById('popup-search-input');
    const openButton = document.querySelector('.header-search');
    const closeButton = document.getElementById('close-search');
    const titleElement = document.querySelector('.search-popup-title');

    // Dark Mode Style
    let styleMode = localStorage.getItem("styleMode");

    const enableDarkStyle = () => {
        document.body.classList.add('dark-mode-gamestore');
        localStorage.setItem("styleMode", 'dark');
    }
    const disableDarkStyle = () => {
        document.body.classList.remove('dark-mode-gamestore');
        localStorage.removeItem("styleMode");
    }

    if (styleToggle) {
        styleToggle.addEventListener('click', () => {
            styleMode = localStorage.getItem('styleMode');
            if (styleMode !== 'dark') { // если не темная версия, то включаем темную версию
                enableDarkStyle();
            } else {
                disableDarkStyle()
            }
        });
    }

    // если юзер зашел на сайт и заранее включил черную версия сайта, то ему нужно это показать
    if (localStorage.getItem('styleMode') === 'dark') {
        enableDarkStyle();
    }

    openButton.addEventListener('click', function () {
        searchContainer.style.display = 'block';
        titleElement.textContent = 'You might be interested';
        showPlaceholders();
        loadDefaultGames()
    });

    closeButton.addEventListener('click', function () {
        searchContainer.style.display = 'none';
        searchResults.innerHTML = '' // каждый раз при заходе на поиск - у нас будет грузиться свежая пустая форма
    });

    searchInput.addEventListener('input', function () {
        const searchItem = searchInput.value;
        titleElement.textContent = 'Search Results';
        showPlaceholders();

        fetch(gamestore_params.ajaxurl, {
            method: "POST",
            header: {
                'Content-Type': "application/x-www-form-urlncoded",
            },
            body: new URLSearchParams({
                action: 'search_games_by_title',
                search: searchItem
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    titleElement.textContent = 'Search Results';
                    renderGames(data.data);
                } else {
                    titleElement.textContent = 'Nothing was found. You might be interested in';
                    showPlaceholders();
                    loadDefaultGames();
                }
            })
            .catch(error => console.log('Error fetching latest games', error));


    });

    function showPlaceholders() {
        searchResults.innerHTML = ''
        for (let i = 0; i < 12; i++) {
            const placeholder = document.createElement('div');
            placeholder.className = 'game-placeholder'
            searchResults.appendChild(placeholder)
        }
    }

    function renderGames(games) {
        searchResults.innerHTML = '';
        games.forEach(function (game) {
            const gameDiv = document.createElement('div');
            gameDiv.className = 'game-result';
            gameDiv.innerHTML = `
                <a href="${game.link}">
                  <div class="game-featured-image">${game.thumbnail}</div>
                  <div class="game-meta">
                    <div class="game-price">${game.price}</div>
                    <h3>${game.title}</h3>
                    <div class="game-platforms">${game.platforms}</div>
                  </div>
                </a>
      `;
            searchResults.appendChild(gameDiv);
        });
    }

    function loadDefaultGames() {
        //ajax
        fetch(gamestore_params.ajaxurl, {
            method: "POST",
            headers: {
                'Content-Type': "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: 'load_latest_games' // колбек для серверной части, чтобы из базы вытащить нужные игры
            })
        })
            .then(response => response.json())
            .then(data => {
                console.log(data); // {success: true, data: Array(10)}
                if (data.success) {
                    renderGames(data.data);
                }
            })
            .catch(error => console.log('Error fetching latest games', error));
    }
});

/*
    1) 'Content-Type': "application/x-www-form-urlncoded"
    «Я отправляю данные в формате, который обычно отправляют HTML-формы (<form method="POST">)».
    Пример такого формата:
    action=load_latest_games&something=value
        Особенности:
            ключ=значение
            значения экранируются (пробел → +, спецсимволы кодируются)
            параметры соединяются &
        Это классический формат POST-форм, и WordPress любит именно его в admin-ajax.php.

    2) Что делает new URLSearchParams()
    Он превращает объект JS:
    {
        action: 'load_latest_games'
    }
    в строку формата формы:
    action=load_latest_games

    Это удобно, потому что:
        тебе не нужно вручную писать action=...&key=...
        автоматически экранирует спецсимволы
        полностью совместим с WP admin-ajax

    То есть:
    body: new URLSearchParams({
        action: 'load_latest_games'
    })
    превращается в:
    action=load_latest_games

    3) Почему WordPress любит именно этот формат?
    Потому что admin-ajax.php ожидает данные:
        либо через обычную HTML форму
        либо через application/x-www-form-urlencoded
    WordPress автоматически получает $_POST['action'] и вызывает соответствующий callback.

    Итог простыми словами
        URLSearchParams — это как собрать форму в JS:
            action=load_latest_games&query=GTA

        Content-Type: application/x-www-form-urlencoded — говорит серверу:
            "Мы отправляем данные в формате обычной HTML формы".
            WordPress ожидает именно такой формат — поэтому это правильный способ.
* */
