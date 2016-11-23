var currentpage_zcj = 1;
var pagenum_zcj =10;
var List_zcj=[];
var Num_zcj=[];
var belong = $("#belong").val();
var t = '';
$(function () {
        $.regEvent();
        $.initinfo();
});

$.extend({
    regEvent: function () {
        //页码点击效果
        $(".pagenum").live("click", function () {
            t = $(".tuijianwenzhang").offset().top;
            $(window).scrollTop(t);
            currentpage_zcj = $(this).text();
			$.getNewsListByAllTerm();
        });
        //上一页
        $(".prev_page").live("click", function () {
            t = $(".tuijianwenzhang").offset().top;
            $(window).scrollTop(t);
            var current = $("#pagenum").find("a.current").text();
            if (current != 1) {
                currentpage_zcj = parseInt(current) - 1;
				$.getNewsListByAllTerm();
            }
        });
        //下一页
        $(".next_page").live("click", function () {
            t = $(".tuijianwenzhang").offset().top;
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
        $.AjaxPost(MODULE+"/getNewsList", {currentpage:currentpage_zcj, pagenum:pagenum_zcj,belong:belong}, function (backdata) {
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                List_num = backdata.data.list_num;
                var px = List_num*400;
                if(Num_zcj){
                    $("#news_list").css('height',px+'px')
                }
                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);
                if(belong==1){
                    var belong_url = 'news';
                }else if(belong==2){
                    var belong_url = 'yuesao/article';
                }else if(belong==3){
                    var belong_url = 'yuersao/article';
                }else if(belong==4){
                    var belong_url = 'baomu/article';
                }else if(belong==5){
                    var belong_url = 'jiajiao/article';
                }
				var str='';
				var $dom=$("#news_list");
				$dom.find(".tuijian-bg").remove();
				if(List_zcj!=null){
				$.each(List_zcj,function(i,item){
                    var keyword = '';
                    $.each(item.keyword,function(j,jtem) {
                        if(j<3){
                            keyword += '<span style="margin-right:10px;">'+jtem+'</span>';
                        }
                    });
					str+='<div class="tuijian-bg">\
                    <img class="toutu" src="'+UPLOADS+'/'+item.title_img+'" />\
                    <a class="wenzhang-biaoti" href="'+ROOT+'/'+belong_url+'/'+item.id+'.html">'+item.title+'</a>\
                    <p class="wenzhang-guanjianci">关键词：'+keyword+'&emsp;&emsp;发布日期：'+item.add_time+'</p>\
                    <span class="wenzhang-jianjie">'+item.description+'</span>\
                    </div>';
				});
				$dom.append(str);
				}

            } else {

            }
        }, true);
    }
    
});