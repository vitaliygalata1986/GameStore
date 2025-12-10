/******/ (() => { // webpackBootstrap
/*!********************************!*\
  !*** ./src/block-hero/view.js ***!
  \********************************/
/** Home slider **/
document.addEventListener('DOMContentLoaded', function () {
  var swiperHero = new Swiper('.hero-slider .slider-container', {
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false
    },
    slidesPerView: 7,
    speed: 1500,
    grabCursor: true,
    mousewheel: true,
    // Разрешает листать слайды колесиком мыши.
    keyboardControl: true // Разрешает управлять слайдером с клавиатуры (стрелками).
  });
});

/*
	disableOnInteraction: false в Swiper означает:
	автопрокрутка (autoplay) НЕ остановится, если пользователь взаимодействует со слайдером — например:
		свайпнет мышью/тачем,
		переключит слайд стрелкой,
		прокрутит колесиком и т.д.
	Если бы было true (это дефолтное поведение у Swiper), то после первого ручного действия autoplay выключился бы совсем.
	То есть у тебя сейчас логика такая:
	слайды сами листаются каждые 5 секунд, и даже если я руками полистал — через 5 секунд оно продолжит само.

	grabCursor - Показывает курсор “рука-захват” (grab) когда наводишься на слайдер.
	Чисто UX-фишка: намекает, что блок можно “тянуть” мышкой.

* */
/******/ })()
;
//# sourceMappingURL=view.js.map