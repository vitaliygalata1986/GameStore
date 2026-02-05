document.addEventListener('DOMContentLoaded', function () {

	const filterForm = document.querySelector('form.gamestore-filter-form');

	if (!filterForm) return;

	const loadMoreButton = document.querySelector('.load-more-button');

	let currentPage = 1;

	filterForm.addEventListener('change', function () {
		currentPage = 1;
		submitForm(false);
	});

	filterForm.addEventListener('reset', function () {
		currentPage = 1;
		setTimeout(() => submitForm(false), 0);
	});

	if (loadMoreButton) {
		loadMoreButton.addEventListener('click', function () {
			currentPage++;
			setTimeout(() => {
				submitForm(true);
			}, 100)
		})
	}

	function submitForm(append = false) {
		const formData = new FormData(filterForm);

		const selectedLanguages = [];
		filterForm.querySelectorAll('input[name^="language-"]:checked').forEach((checkbox) => {
			selectedLanguages.push(checkbox.name.replace('language-', ''));
		})
		// console.log(selectedLanguages) // ['29', '25', '26']

		const selectedGenres = [];
		filterForm.querySelectorAll('input[name^="genre-"]:checked').forEach((checkbox) => {
			selectedGenres.push(checkbox.name.replace('genre-', ''));
		})


		const releasedRaw = (formData.get('released') || '').trim();
		const releasedYear = releasedRaw ? releasedRaw.slice(0, 4) : '';

		fetch(gamestore_params.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: new URLSearchParams({
				action: 'filter_games',
				page: currentPage,
				posts_per_page: formData.get('posts_per_page'),
				platforms: formData.get('platforms') || '',
				publisher: formData.get('publisher') || '',
				singleplayer: formData.get('singleplayer') || '',
				released: releasedYear,
				languages: selectedLanguages.join(','),
				genres: selectedGenres.join(',')
			})
		})
			.then(response => response.text())
			.then(data => {
				const gamesListContainer = document.querySelector('.games-list');
				if (append) { // если нужно добавить к существующему списку
					gamesListContainer.innerHTML += data;
				} else { // если нужно заменить список
					gamesListContainer.innerHTML = data;
				}
			})
			.catch(error => console.error('Error', error));
	}
});


// Событие change на форме (и вообще в DOM) срабатывает не “у формы” как у отдельного поля, а всплывает (bubbles) от элементов управления внутри формы
// То есть твой обработчик: filterForm.addEventListener('change', ...) сработает когда изменится значение любого инпута/селекта/textarea внутри этой формы, и это событие всплывёт до <form>
// formData.append('page', currentPage); // Добавляем текущую страницу в данные формы, чтобы сервер знал, какую страницу запрашивать
// new URLSearchParams(formData).toString(); // Преобразуем FormData в строку запроса (languages%5B%5D=29&genres%5B%5D=32&platform=36&singleplayer=Yes&publisher=Ubisoft&year=2026&posts_per_page=8&page=1)


// javascript
// Коротко: собираем FormData (включая поля с именами типа genres[]), добавляем action и страницу,
// затем превращаем в URLSearchParams и отправляем POST body.
