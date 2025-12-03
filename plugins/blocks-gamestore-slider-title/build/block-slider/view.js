/******/ (() => { // webpackBootstrap
/*!**********************************!*\
  !*** ./src/block-slider/view.js ***!
  \**********************************/
document.addEventListener('DOMContentLoaded', function () {
  const sliderWrapper = document.querySelector('.wp-block-create-block-blocks-slider');
  if (!sliderWrapper) return;
  const slidesPerView = parseInt(sliderWrapper.dataset.slidesPerView, 10) || 1;
  const autoplayEnabled = sliderWrapper.dataset.autoplay === 'true';
  const speedFactor = parseInt(sliderWrapper.dataset.speedAutoplay, 10) || 3;
  const speed = speedFactor * 500;
  console.log(speed);
  const swiperOptions = {
    loop: true,
    slidesPerView,
    speed: 3500,
    // ← длительность анимации
    grabCursor: true,
    spaceBetween: 8
  };
  if (autoplayEnabled) {
    swiperOptions.autoplay = {
      delay: 1,
      // почти без паузы, чистый “маркиз”
      disableOnInteraction: false
    };
  }
  const swiperGames = new Swiper('.hero-slider__container', swiperOptions);
});
/******/ })()
;
//# sourceMappingURL=view.js.map