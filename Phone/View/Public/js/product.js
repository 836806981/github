$(function() {
	var i = 0;
	var bannerImg = $(".product>li");
	var window_wid=$(window).width();
	bannerImg.eq(0).show().siblings().hide();

//	interval = setInterval(changePicRgt, 5000);

	function changePic() {
		bannerImg.eq(i).fadeOut();
		i++;
		i = i % bannerImg.length;
		bannerImg.eq(i).fadeIn();
	}
	
	function changePicRgt() {
		bannerImg.eq(i).fadeOut();
		i--;
		
		i = i % bannerImg.length;
		bannerImg.eq(i).fadeIn();
	}
	$(".product").swipe({swipeLeft:function(event,direction,distance){
			if(distance > window_wid / 5){
				changePic();
			}else{
										
			}
		},swipeRight:function(event,direction,distance){
			if(distance > window_wid / 5){
				changePicRgt();
			}else{
										
			}
		}	
	});
});