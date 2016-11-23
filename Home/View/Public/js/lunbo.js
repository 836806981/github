var i=0;
$(function(){
	$(".rgt_pic>a").eq(0).show().siblings().hide();
	setInterval(changePic, 3000);
});

function changePic() {
	var imgs = $(".rgt_pic>a");
	
	if(imgs.length>1){
		imgs.eq(i).fadeOut(600);
		i++;
		i = i % imgs.length;
		imgs.eq(i).fadeIn(600);
	}
	
}