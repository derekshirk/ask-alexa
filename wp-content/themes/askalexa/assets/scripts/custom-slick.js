$(document).ready(function(){
	$('.commands-ui').slick({
		arrows: false, 
		prevArrow: '<button type="button" class="slick-prev"><svg id="Layer_3" data-name="Layer 3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26.0012 45.9993"><title>chevron</title><path d="M1.3643,39.58a3.379,3.379,0,0,0,0,4.7455,3.3,3.3,0,0,0,4.6981,0L24.6372,25.3737a3.3812,3.3812,0,0,0,0-4.7474L6.0624,1.6745a3.2977,3.2977,0,0,0-4.6981,0A3.3808,3.3808,0,0,0,1.3623,6.42L16.5954,23Z"/></svg></button>',
		nextArrow: '<button type="button" class="slick-next"><svg id="next-arrow" data-name="next-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26.0012 45.9993"><title>chevron</title><path d="M1.3643,39.58a3.379,3.379,0,0,0,0,4.7455,3.3,3.3,0,0,0,4.6981,0L24.6372,25.3737a3.3812,3.3812,0,0,0,0-4.7474L6.0624,1.6745a3.2977,3.2977,0,0,0-4.6981,0A3.3808,3.3808,0,0,0,1.3623,6.42L16.5954,23Z"/></svg></button>',
		mobileFirst: true,
		responsive: [ {
			breakpoint:769,
				settings: {
					arrows: true, 
				}
		} ]
	});
});