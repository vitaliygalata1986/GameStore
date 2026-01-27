/******/ (() => { // webpackBootstrap
/*!***************************************!*\
  !*** ./src/block-single-game/view.js ***!
  \***************************************/
document.addEventListener('DOMContentLoaded', function () {
  var swiperGameSlider = new Swiper('.game-single-slider', {
    loop: true,
    slidesPerView: "auto",
    speed: 300,
    keyboardControl: true,
    navigation: {
      nextEl: '.swiper-game-next',
      prevEl: '.swiper-game-prev'
    },
    keyboard: {
      enable: true,
      onlyInViewport: true
    }
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map