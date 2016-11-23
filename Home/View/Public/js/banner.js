$(function() {

	var i = 0,
		interval;
	var bannerImg = $(".banner>ul.bannerImg>li");
	var lunbtnLi = $(".lunbtn>li");
	var lunbtn = $(".lunbtn");

	bannerImg.eq(0).show().siblings().hide();

	if (bannerImg.length > 1) {

		interval = setInterval(changePic, 5000);
		lunbtnLi.mouseenter(function() {
			clearInterval(interval);
		}).mouseleave(function() {
			interval = setInterval(changePic, 5000);
		});

		lunbtnLi.click(function() {
			var flag = $(this).hasClass("btncur");
			if (!flag) {
				bannerImg.eq(i).fadeOut(600);
				i = $(this).index();
				bannerImg.eq(i).fadeIn(600);
				l_btn();
			}
		});

		lunbtn.css("margin-left", -(lunbtn.width() / 2)).show();

	} else if (bannerImg.length == 1) {

	} else {
		$(".banner").remove();
	}

	function changePic() {
		bannerImg.eq(i).fadeOut(600);
		i++;
		i = i % bannerImg.length;
		bannerImg.eq(i).fadeIn(600);
		l_btn();
	}

	function l_btn() {
		lunbtn.children().removeClass("btncur").eq(i).addClass("btncur");
	}

});