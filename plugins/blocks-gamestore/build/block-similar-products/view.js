/******/ (() => { // webpackBootstrap
/*!********************************************!*\
  !*** ./src/block-similar-products/view.js ***!
  \********************************************/
document.addEventListener('DOMContentLoaded', function () {
  var swiperSimilar = new Swiper('.similar-games-list', {
    loop: false,
    autoplay: false,
    spaceBetween: 16,
    slidesPerView: 6,
    speed: 500,
    grabCursor: true,
    navigation: {
      nextEl: '.similar-right',
      prevEl: '.similar-left'
    }
  });
});
/******/ })()
;
//# sourceMappingURL=view.js.map