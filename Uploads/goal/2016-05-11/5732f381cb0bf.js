/**
 * Created by Administrator on 2015/3/16.
 */


(function($){

    $.dialog=function(opt){

        var css="<style id='style' type='text/css'>";

        css+= ".bg{position: fixed; width: 100%; height: 100%; left: 0; top: 0; background: #000;opacity: 0.1; z-index: 9999; }";

        css+=".dialog-box{position: fixed; width: 100%;height: 100%;left: 0;top: 0;z-index: 10000;}";

        css+=" .dialog{ width: 23%; height: 160px; background: #fff; margin: auto;font-family: '微软雅黑'; border:1px solid #ccc;}";
		css+=" .dialog_title{ padding-left:20px; line-height: 50px; background: #32a480;border-radius: 8px 8px 0 0; font-family: '微软雅黑'; color:#fff; font-size:16px;}";
        css+=".textWrap{font-size: 14px;text-align: center; padding-top: 40px;}";
        css+=" .ask{height: 50px;margin-top: 30px; text-align:center;}";
        css+=" .dialog_icon{ display:inline-block; width:21px; height:21px; background:url("+IMG+"/natural100/icon.png) no-repeat 0 -44px; margin-right:8px; vertical-align:middle;}";
        css+=" .ask-bt{ display:inline-block; width: 25%;background: #ff4940;font-size: 12px;color: #fff;text-align: center;height: 25px;line-height: 25px; border-radius:3px; border:1px solid #ff4940;cursor: pointer;}";
		css+=" .ask-bt:hover{ background: #d63830; color:#fff; border:1px solid #d63830;}";
		css+=" .cancel-bt{ background: #f5f5f5; border-radius:3px; border:1px solid #ccc; margin-left:10px; color:#666;}";
		css+=" .cancel-bt:hover{ background: #ccc; color:#666; border:1px solid #ccc;}";
        css+=".i-hava-konwn{width: 25%;margin: auto;height: 25px;line-height: 25px; background: #f5f5f5; border-radius:3px; border:1px solid #ccc; text-align: center;color: #666;margin-top: 20px;cursor: pointer; }";

        css+="@media screen and (max-width: 640px) {";
        css+=".dialog{ width: 90%;height: 190px;background: #fafafa; border-radius: 8px;margin: auto; font-family: '微软雅黑';}";
        css+=".textWrap{ font-size: 1rem; }";
        css+=" }</style>";

        if($("#style").attr("type")==undefined){
            $(css).appendTo("head");
        }
        var dialogBox="<div class='bg'></div><div class='dialog-box'><div class='dialog'></div></div>";
        var textWrap="<div class='textWrap'><i class='dialog_icon'></i>"+opt.text+"</div>";

        var askDom="<div class='ask'><a class='ask-bt'>确定</a><a class='ask-bt cancel-bt'>取消</a></div>";

        var iKnow="<div class='know'><div class='cancel-bt i-hava-konwn'>知道了</div></div>";

        $(dialogBox).appendTo("body");
        $(".dialog").css("marginTop",($(window).height()-$(".dialog").height())/2);


        function close(){
            $(".aks").remove();
            $(".dialog-box").remove();
            $(".bg").remove();

        }
        //comfirm框
        if(opt.type==0){
            $(textWrap).appendTo(".dialog");
            $(askDom).appendTo(".dialog");

            $(".ask-bt").click(function(){

                close();

                if($(this).index()==0){
                    opt["ok"]();
                }else{
                    return false;
                }

            })
        }
        //alert框
        if(opt.type==1){
            $(textWrap).appendTo(".dialog");
            $(iKnow).appendTo(".dialog");

            $(".i-hava-konwn").click(function(){
                close();
            })
        }
		//无确认的提示框
		if(opt.type==2){
			$(".dialog").css({"background":"none","border":"none"});
			$(".bg").css({"opacity":"0"});
            $(textWrap).appendTo(".dialog");
			$(".dialog_icon").hide();
			$(".textWrap").css({"background":"url("+IMG+"/natural100/dialog_bg.png) repeat","border-radius":"20px","padding":"8px 10px","color":"#fff"});
            window.setTimeout(function () { close(); }, 1000);
        }


    }





})(jQuery)