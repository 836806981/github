var anim = $(".page6>.server>.content>div");
var waitTime1,waitTime2,waitTime3,waitTime4,waitTime5,waitTime6,waitTime7 = null;
var flag = true;

$(function(){
	$(window).scroll(function() {
		var seaTop = $(window).scrollTop();
		if (flag && seaTop > 2900) {
			addAnimation1();
			flag=false;
		} 
	});
});

function addAnimation1(){
	anim.eq(0).addClass("anim");
	waitTime1 = setInterval(addAnimation2,300);
}

function addAnimation2(){
	clearInterval(waitTime1);
	anim.eq(2).addClass("anim");
	waitTime2 = setInterval(addAnimation3,300);
}

function addAnimation3(){
	clearInterval(waitTime2);
	anim.eq(4).addClass("anim");
	waitTime3 = setInterval(addAnimation4,300);
}

function addAnimation4(){
	clearInterval(waitTime3);
	anim.eq(6).addClass("anim");
	waitTime4 = setInterval(addAnimation5,300);
}

function addAnimation5(){
	clearInterval(waitTime4);
	anim.eq(7).addClass("anim");
	waitTime5 = setInterval(addAnimation6,300);
}

function addAnimation6(){
	clearInterval(waitTime5);
	anim.eq(5).addClass("anim");
	waitTime6 = setInterval(addAnimation7,300);
}

function addAnimation7(){
	clearInterval(waitTime6);
	anim.eq(3).addClass("anim");
	waitTime7 = setInterval(addAnimation8,300);
}

function addAnimation8(){
	clearInterval(waitTime7);
	anim.eq(1).addClass("anim");
}
