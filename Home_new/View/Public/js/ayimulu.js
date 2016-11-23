var currentpage_zcj = 1;
var pagenum_zcj =10;
var List_zcj=[];
var Num_zcj=[];
var keyword = $("#name").val();

$(function () {
        $.regEvent();
        $.initinfo();
    
});

$.extend({
    regEvent: function () {

        $(".search").live("click", function () {
            keyword = $("#name").val();
            $.getNewsListByAllTerm();
        });

        $('#name').bind('keypress',function(event){
            if(event.keyCode == "13")
            {
                $("#name").blur();
                keyword = $("#name").val();
                $.getNewsListByAllTerm();
            }
        });


        //页码点击效果
        $(".pagenum").live("click", function () {
            currentpage_zcj = $(this).text();
			$.getNewsListByAllTerm();
        });
        //上一页
        $(".prev_page").live("click", function () {
            var current = $("#pagenum").find("a.current").text();
            if (current != 1) {
                currentpage_zcj = parseInt(current) - 1;
				$.getNewsListByAllTerm();
            }
        });
        //下一页
        $(".next_page").live("click", function () {
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
        $.AjaxPost(MODULE+"/ayiList", {currentpage:currentpage_zcj, pagenum:pagenum_zcj,keyword:keyword}, function (backdata) {
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                var now_num = Num_zcj%10;
                var px = now_num*350;
                if(Num_zcj){
                    $("#list").css('height',px+'px')
                }

                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);
				var str='';
				var $dom=$("#list");
				$dom.find(".ayi-information").remove();
				if(List_zcj!=null){
				$.each(List_zcj,function(i,item){
					str+='<div class="ayi-information">\
                    <ul class="infor-ul f-cb">\
                    <li class="infor-li1"><img src="'+UPLOADS+'/'+item.title_img+'" width="180px" height="185px" /></li>\
                    <li class="infor-name">姓名</li>\
                    <li class="infor-name-content">'+item.name+'</li>\
                    <li class="infor-height">身高</li>\
                    <li class="infor-height-content">'+item.hight+'cm</li>\
                    <li class="infor-age">年龄</li>\
                    <li class="infor-age-content">'+item.age+'岁</li>\
                    <li class="infor-weight">体重</li>\
                    <li class="infor-weight-content">'+item.weight+'kg</li>\
                    <li class="infor-address">籍贯</li>\
                    <li class="infor-address-content">'+item.native_place+'</li>\
                    <li class="infor-level">等级</li>\
                    <li class="infor-level-content">'+item.level+'</li>\
                    <li class="infor-zodiac">属相</li>\
                    <li class="infor-zodiac-content">'+item.zodiac+'</li>\
                    <li class="infor-marriage">婚姻</li>\
                    <li class="infor-marriage-content">'+item.marriage+'</li>\
                    <li class="infor-star">星座</li>\
                    <li class="infor-star-content">'+item.constellation+'</li>\
                    <li class="infor-birth">生育</li>\
                    <li class="infor-birth-content">'+item.birth+'</li>\
                    <li class="yuding"><a href="'+ROOT+'/ayi/'+item.id+'.html">点击查看</a></li>\
                    </ul>\
                    </div>';
				});
				$dom.append(str);
				}

            } else {

            }
        }, true);
    }
    
});