$(function(){
//	二级导航
	$(".first-li").mouseenter(function(){
		  if(!$(this).children(".second-ul").is(":animated"))
			 $(this).children(".second-ul").stop(true, true).slideDown();
	});

		
	$(".first-li").mouseenter(function(){

		$(this).children(".second-ul").stop(true, true).slideDown();
		$(this).siblings().children(".second-ul").stop(true, true).slideUp();
	});
	$(".second-ul").mouseleave(function(){
		$(this).stop(true, true).slideUp();
	});
	
	
	
//	三级导航
	$(".second-li").mouseenter(function(){
		$(this).children(".third-ul").stop(true, true).slideDown();
		$(this).siblings().children(".third-ul").stop(true, true).slideUp();
	});
	
});

