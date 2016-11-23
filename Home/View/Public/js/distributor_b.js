var currentpage_zcj = 1;
var pagenum_zcj = 2;
var List_zcj=[];
var Num_zcj=[];

$(function () {
        $.regEvent();
        $.initinfo();
    
});

$.extend({
    regEvent: function () {
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
        $.AjaxPost(MODULE+"/getList", {currentpage:currentpage_zcj, pagenum:pagenum_zcj}, function (backdata) {
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);
				var str='';
				var $dom=$(".customr_table");
				$dom.find("tr:gt(0)").remove();
				if(List_zcj!=null){
				$.each(List_zcj,function(i,item){
					str+='<tr class="tr_body">\
							  <td><img src="'+item.image+'" class="customer_img" /></td>\
							  <td class="color_33">'+item.nick_name+'</td>\
							  <td class="color_33">'+item.username+'</td>\
							  <td class="color_33">'+item.number+'</td>\
							  <td class="color_33">'+item.time+'</td>\
							  <td class="color_33">'+item.income+'</td>\
						   </tr>';
				});
				$dom.append(str);
				}

            } else {
                $.dialog({
                         type:2,
                         text:"查询失败！"
                });
            }
        }, true);
    }
    
});