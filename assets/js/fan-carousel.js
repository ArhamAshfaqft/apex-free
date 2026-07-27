(function ($) {
	'use strict';

	function initFanCarousel($scope) {
		var $wrapper = $scope.find('.eas-fan-carousel-wrapper');
		if (!$wrapper.length) return;

		var $cards = $wrapper.find('.eas-poker-card');
		var $leftBtn = $wrapper.find('.deck-left-trigger');
		var $rightBtn = $wrapper.find('.deck-right-trigger');
		
		var activeIdx = 0;
		var total = $cards.length;
		if (total <= 0) return;

		function arrangementEngine() {
			$cards.each(function (i) {
				var $card = $(this);
				
				// Reset fanning layout positions
				$card.removeClass('pos-0 pos-1 pos-2 pos-99 pos-98 hidden-reserve-left hidden-reserve-right');
				
				var offset = i - activeIdx;
				
				// Modulo loop corrections for circular rotation
				if (offset < -Math.floor(total / 2)) offset += total;
				if (offset > Math.floor(total / 2)) offset -= total;

				if (offset === 0) {
					$card.addClass('pos-0');
				} else if (offset === 1) {
					$card.addClass('pos-1');
				} else if (offset === 2) {
					$card.addClass('pos-2');
				} else if (offset === -1) {
					$card.addClass('pos-99'); // maps -1
				} else if (offset === -2) {
					$card.addClass('pos-98'); // maps -2
				} else if (offset > 2) {
					$card.addClass('hidden-reserve-right');
				} else {
					$card.addClass('hidden-reserve-left');
				}
			});
		}

		// Click controls triggers
		$rightBtn.on('click', function (e) {
			e.preventDefault();
			activeIdx = (activeIdx + 1) % total;
			arrangementEngine();
		});

		$leftBtn.on('click', function (e) {
			e.preventDefault();
			activeIdx = (activeIdx - 1 + total) % total;
			arrangementEngine();
		});

		// Card click focus trigger
		$cards.each(function (clickedIdx) {
			$(this).on('click', function () {
				if (clickedIdx !== activeIdx) {
					activeIdx = clickedIdx;
					arrangementEngine();
				}
			});
		});

		// --- TOUCH SWIPE HANDLER ---
		var startX = 0;
		var threshold = 50; // swipe threshold in px

		$wrapper.on('touchstart', function (e) {
			startX = e.originalEvent.touches[0].clientX;
		});

		$wrapper.on('touchend', function (e) {
			var endX = e.originalEvent.changedTouches[0].clientX;
			var dx = endX - startX;

			if (Math.abs(dx) > threshold) {
				if (dx < 0) {
					// Swiped left -> show next card
					activeIdx = (activeIdx + 1) % total;
				} else {
					// Swiped right -> show previous card
					activeIdx = (activeIdx - 1 + total) % total;
				}
				arrangementEngine();
			}
		});

		// Run arrangement initially
		arrangementEngine();
	}

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/eas-fan-carousel.default', initFanCarousel);
	});

})(jQuery);
