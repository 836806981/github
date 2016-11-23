var currentpage_zcj = 1;
var pagenum_zcj =10;
var List_num = '';
var List_zcj=[];
var Num_zcj=[];
var belong = $("#belong").val();
var keywords  = $("#keywords").val();
$(function () {
        $.regEvent();
        $.initinfo();
    
});

$.extend({
    regEvent: function () {
        //页码点击效果
        $(".pagenum").live("click", function () {
            t = $(".news_nav").offset().top;
            $(window).scrollTop(t);

            currentpage_zcj = $(this).text();
			$.getNewsListByAllTerm();
        });
        //上一页
        $(".prev_page").live("click", function () {
            t = $(".news_nav").offset().top;
            $(window).scrollTop(t);

            var current = $("#pagenum").find("a.current").text();
            if (current != 1) {
                currentpage_zcj = parseInt(current) - 1;
				$.getNewsListByAllTerm();
            }
        });
        //下一页
        $(".next_page").live("click", function () {
            t = $(".news_nav").offset().top;
            $(window).scrollTop(t);

            var current = $("#pagenum").find("a.current").text();
            var pages = $("#pagenum").find("a.pagenum");
            if (current != $(pages).length) {
                currentpage_zcj = parseInt(current) + 1;
                $.getNewsListByAllTerm();
            }
        });
    },
    initinfo: function () {
        //根据页码获取数据
		$.getNewsListByAllTerm();
		
    },
	//根据所有条件查询新闻列表



    getNewsListByAllTerm: function () {

        //异步提交数据,参数：currentpage,要查询的页码;pagenum，每页的条数
        $.AjaxPost(MODULE+"/getNewsList", {currentpage:currentpage_zcj, pagenum:pagenum_zcj,belong:belong,keywords:keywords}, function (backdata) {
            layer.closeAll();
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                List_num = backdata.data.list_num;
                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);

                var str='';
                var $dom=$("#news_list");
                $dom.find(".news_exp").remove();
                if(List_zcj!=null&&List_zcj.length!=0){
                    $.each(List_zcj,function(i,item){
                        if(item.belong<10) {
                            var px = List_num*200;
                            if(px<400){
                                px = 400;
                            }
                            px +=200;
                            if(Num_zcj){
                                $("#news_list").css('height',px+'px');
                            }
                            var keyword_str = '';
                            $.each(item.keyword, function (j, jtem) {
                                //if(j<3){
                                keyword_str += '<a style="margin-right:10px;" href="' + ROOT + '/keywords/' + jtem + '">' + jtem + '</a>&nbsp;';
                                //}
                            });
                            str += '<div class="news_exp f-cb">\
                                <div class="news_img">\
                                    <a href="' + ROOT + '/' + item.belong_url + '/' + item.id + '.html"><img src="' + UPLOADS + '/' + item.title_img + '"/></a>\
                                </div>\
                                <div class="news_info">\
                                    <p class="news_tle"><a href="' + ROOT + '/' + item.belong_url + '/' + item.id + '.html">' + item.title + '</a></p>\
                                    <p class="news_key">关键词：' + keyword_str + '</p>\
                                    <p class="news_pf">' + item.description + '</p>\
                                </div>\
                            </div>';
                        }else if(item.belong==21){
                            str += '<div class="news_li">\
                            <p class="news_tle"><a href="'+ROOT+'/newsEvents/'+item.id+'.html">'+item.title+'</a></p>\
                            <p class="news_pf">'+item.description+'</p>\
                            <div class="green"></div>\
                            </div>';

                        }else if(item.belong==22){
                            str += '<div class="news_li">\
                            <p class="news_tle"><a href="'+ROOT+'/notice/'+item.id+'.html">'+item.title+'</a></p>\
                            <p class="news_pf">'+item.description+'</p>\
                            <div class="green"></div>\
                            </div>';

                        }
                    });
                    $dom.find('#pagenum').before(str);
                }else{
                    var $dom=$("#news_list");
                    $dom.find(".news_exp").remove();
                    var str = '<p style="font-size: 30px;color: #dddddd;">敬请期待</p>';

                    $dom.find('#pagenum').before(str);
                }
            } else {
                layer.msg('加载失败');
                setTimeout(function(){
                    layer.closeAll('loading');
                }, 2000);
                location.reload();
            }
        }, true,true,'POST',function(){
            layer.load();
        });
    }








    
});