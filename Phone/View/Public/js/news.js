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
        //上一页
        $(".prev_page").live("click", function () {
            var t = $("#go_news_top").offset().top;
            $(window).scrollTop(t);

            currentpage_zcj = currentpage_zcj - 1;
            $.getNewsListByAllTerm();
        });
        //下一页
        $(".next_page").live("click", function () {
            var t = $("#go_news_top").offset().top;
            $(window).scrollTop(t);

            currentpage_zcj = currentpage_zcj + 1;
            $.getNewsListByAllTerm();
        });
    },
    initinfo: function () {
        //根据页码获取数据
		$.getNewsListByAllTerm();
		
    },
	//根据所有条件查询新闻列表


    getNewsListByAllTerm: function () {

        //异步提交数据,参数：currentpage,要查询的页码;pagenum，每页的条数
        $.AjaxPost(ROOT+"/m/getNewsList", {currentpage:currentpage_zcj, pagenum:pagenum_zcj,belong:belong,keywords:keywords}, function (backdata) {
            layer.closeAll();
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                List_num = backdata.data.list_num;
                $.ProductPageShow(Num_zcj,pagenum_zcj,currentpage_zcj,"b");

                var str='';
                var $dom=$(".newslist2");
                $dom.find(".news_exp").remove();
                if(List_zcj!=null&&List_zcj.length!=0){
                    $.each(List_zcj,function(i,item){
                        if(item.belong<10) {
                            var keyword_str = '';
                            str +='<li class="news_exp">\
                            <a class="f-cb" href="' + ROOT + '/m/' + item.belong_url + '/' + item.id + '.html">\
                            <img src="' + UPLOADS + '/' + item.title_img + '" class="newsimg f-fl" />\
                            <div class="newscnt f-fr">\
                            <p class="p1">' + item.title + '</p>\
                            <p class="p2">' + item.add_time + '</p>\
                            <p class="p3">' + item.description + '</p>\
                            </div></a>\
                            </li>';
                        }else if(item.belong==21){
                            str += '<li class="news_exp">\
                            <a class="f-cb" href="'+ROOT+'/m/newsEvents/'+item.id+'.html">\
                            <div class="newscnt f-fr">\
                            <p class="p1">'+item.title+'</p>\
                            <p class="p2">'+item.add_time+'</p>\
                            <p class="p3">'+item.description+'</p>\
                            </div></a>\
                            </li>';
                        }else if(item.belong==22){
                            str += '<li class="news_exp">\
                            <a class="f-cb" href="'+ROOT+'/m/notice/'+item.id+'.html">\
                            <div class="newscnt f-fr">\
                            <p class="p1">'+item.title+'</p>\
                            <p class="p2">'+item.add_time+'</p>\
                            <p class="p3">'+item.description+'</p>\
                            </div></a>\
                            </li>';
                        }
                    });
                    $dom.append(str);
                }else{
                    var $dom=$("#news_list");
                    $dom.find(".news_exp").remove();
                    var str = '<p style="font-size: 30px;color: #dddddd;">敬请期待</p>';

                    $dom.find('#pagenum').before(str);
                }
            } else {
                layer.open({content: '加载失败',skin: 'msg',time: 1});
                location.reload();
            }
        }, true,true,'POST',function(){
            layer.open({type: 2});
        });
    }








    
});