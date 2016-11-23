var currentpage_zcj = 1;
var pagenum_zcj =20;
var List_zcj=[];
var Num_zcj=[];
var jianzhi = $("#jianzhi").val();
var age1 = $("#age1").val();
var age2 = $("#age2").val();
var l_price = $("#l_price").val();
var h_price = $("#h_price").val();
var zodiac = $("#zodiac").val();
var b_time = $("#b_time").val();
var s_time = $("#s_time").val();
var add_time_b = $("#add_time_b").val().trim();
var add_time_s = $("#add_time_s").val().trim();
var keyword = '';

$(function () {
        $.regEvent();
        $.initinfo();
    
});

function duibi(a, b) {
    var arr = a.split("-");
    var starttime = new Date(arr[0], arr[1], arr[2]);
    var starttimes = starttime.getTime();

    var arrs = b.split("-");
    var lktime = new Date(arrs[0], arrs[1], arrs[2]);
    var lktimes = lktime.getTime();
    if (starttimes >= lktimes) {
        alert('上单时间大于结束时间，请检查');
        return false;
    }else{
        return true;
    }
}
var reg=/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/;


$.extend({
    regEvent: function () {
        //页码点击效果
        $(".pagenum").live("click", function () {
            if($(this).attr('read')=='readonly'){
                return false;
            }else{
                currentpage_zcj = $(this).text();
                $.getNewsListByAllTerm();
            }
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

        //查询
        $("#search").live("click", function () {
            jianzhi = $("#jianzhi").val();
            age1 = $("#age1").val().trim();
            age2 = $("#age2").val().trim();
            l_price = $("#l_price").val().trim();
            h_price = $("#h_price").val().trim();
            zodiac = $("#zodiac").val().trim();
            b_time = $("#b_time").val().trim();
            s_time = $("#s_time").val().trim();
            add_time_b = $("#add_time_b").val().trim();
            add_time_s = $("#add_time_s").val().trim();
            employee_name = $("#employee_name").val();
            if(parseInt(l_price)>parseInt(h_price)){
                alert('请检查价格！');
                return false;
            }

            if(parseInt(age1)>parseInt(age2)){
                alert('请检查年龄！');
                return false;
            }
            if(!(!add_time_b||reg.test(add_time_b))){
                alert('日期格式不正确或大小不对');
                return false;
            }
            if(!(!add_time_s||reg.test(add_time_s))){
                alert('日期格式不正确或大小不对');
                return false;
            }

            if(b_time&&s_time){
                if(!reg.test(s_time) || !reg.test(b_time)||s_time<b_time){
                    alert('添加日期格式不正确或大小不对');
                    return false;
                }
            }

            name = '';
            $("#name").val('');
            currentpage_zcj = 1;
            $.getNewsListByAllTerm();
        });

        $('#keyword').bind('keypress',function(event){
            if(event.keyCode == "13")
            {
                $("#keyword").blur();
                keyword = $("#keyword").val();
                if(keyword==''||!keyword){
                    alert('请输入');
                    return false;
                }else{
                    currentpage_zcj = 1;
                    $.getNewsListByAllTerm();
                }
            }
        });

        //页码点击效果
        $("#priority").live("change", function () {
            priority = $("#priority").val();
            currentpage_zcj = 1;
            $.getNewsListByAllTerm();
        });

        //搜索
        $("#find").live("click", function () {

            keyword = $("#keyword").val();
            if(keyword==''||!keyword){
                alert('请输入');
                return false;
            }else{
                currentpage_zcj = 1;
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
        $.AjaxPost(MODULE+"/Order/getAyilist", {order_id:order_id,type:type,jianzhi:jianzhi,age1:age1,age2:age2,l_price:l_price,h_price:h_price,zodiac:zodiac,b_time:b_time,s_time:s_time,add_time_s:add_time_s,add_time_b:add_time_b,name:name,currentpage:currentpage_zcj, pagenum:pagenum_zcj,name:keyword}, function (backdata) {
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);
				var str='';
				var $dom=$("#nurse_list");
                $("#nurse_list tr:gt(0)").remove();
                $("#zanwushuju").remove();

				if(List_zcj!=null){
                    $.each(List_zcj,function(i,item){
                        var use = '';
                        $.each(item.nurse_use,function(k,ktem){
                            use += '<p>'+ktem.b_time+'~~'+ktem.s_time+'</p>';
                        });
                        if(use==''){
                            use = '<p>该阿姨还没有上单记录</p>';
                        }
                        var check = '';
                        if(item.our_nurse==1){
                            check = ' checked';
                        }
                        str +='<tr>\
                                    <td><input name="change_order_nurse_re" type="checkbox" '+check+' nurse_id='+item.id+'></td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.l_price+'~'+item.h_price+' <button id="showUse" class="btn btn-default" title="查看上单记录">+</button>\
                                    <div id="use" style="display: none"><p>'+item.type_name+'('+item.others_name1+item.others_name2+')</p></div>\
                                    </td>\
                                    <td> '+item.add_time_nurse+'</td>\
                                    <td> '+use+'</td>\
                                    <td style="max-width: 250px;"> '+item.specialty+'</td>\
                                    <td> '+item.zodiac+'</td>\
                                    <td><a href="'+MODULE+'/Order/ayi_info/id/'+item.id+'">查看详情</a></td>\
                                    </tr>';

                    });
                    $dom.append(str);
                    $("#nurse_list tr:gt(0)").attr('onmouseover',"this.style.backgroundColor='#ffff66';");
                    $("#nurse_list tr:gt(0)").attr('onmouseout',"this.style.backgroundColor='#d4e3e5';");
				}else{
                    str ='<div style="color: #c3c3c3; width: 80px; display: block; margin: 50px auto;" id="zanwushuju">暂无数据</div>';
                    $(".table").append(str);
                }

            } else {

            }
        }, true);
    }
    
});