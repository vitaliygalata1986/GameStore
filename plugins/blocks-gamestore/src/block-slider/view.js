document.addEventListener('DOMContentLoaded', function() {
	var swiperMedia = new Swiper('.slider-media', {
		loop: true,
		autoplay: {
			delay: 2000,
			disableOnInteraction: false,
		},
		slidesPerView: 'auto',
		speed: 1000,
		grabCursor: true,
		mousewheelControl: true,
		keyboardControl: true,
		centeredSlides: true,
	});
});
