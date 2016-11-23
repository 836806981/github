var num = $(".num");
var imgs = $(".xuanze>img");
var pic = $(".xzCnt>img");
var i = 0 , j = 0;
var interval1,interval2;
var flag = true , flags = true;


$(function(){
	$(window).scroll(function() {
		var seaTop = $(window).scrollTop();
		if (flag && seaTop > 400) {
			if(i<num.length){
				interval1 = setInterval(addAnimation,300);
			}
			flag=false;
		}
		if (flags && seaTop > 2400) {
			if(j<pic.length){
				interval2 = setInterval(addAnimation2,300);
			}
			flags=false;
		}
	});
});

function addAnimation(){
	var no = i - 1;
	num.eq(no).fadeIn(400);
	imgs.eq(no).addClass('anim');
	i++;
	if(i>5) clearInterval(interval1);
}
function addAnimation2(){
	pic.eq(j).addClass('show');
	j++;
	if(j>4) clearInterval(interval2);
}