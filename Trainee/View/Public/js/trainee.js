var currentpage_zcj = 1;
var pagenum_zcj =20;
var List_zcj=[];
var Num_zcj=[];
var keyword = $("#keyword").val();
var priority = $("#priority").val();
var employee_name = $("#employee_name").val();
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

        $('#keyword').bind('keypress',function(event){
            if(event.keyCode == "13")
            {
                $("#keyword").blur();
                keyword = $("#keyword").val().trim();
                if(keyword==''||!keyword){
                    alert('请输入');
                    return false;
                }else{
                    currentpage_zcj = 1;
                    $.getNewsListByAllTerm();
                }
            }
        });

        //筛选1
        $("#priority").live("change", function () {
            priority = $("#priority").val();
            employee_name = $("#employee_name").val();
            currentpage_zcj = 1;
            $.getNewsListByAllTerm();
        });
        //筛选2
        $("#employee_name").live("change", function () {
            priority = $("#priority").val();
            employee_name = $("#employee_name").val();
            currentpage_zcj = 1;
            $.getNewsListByAllTerm();
        });

        //搜索
        $("#find").live("click", function () {

            keyword = $("#keyword").val().trim();
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
        $.AjaxPost(MODULE+"/Trainee/getTraineeList", {priority:priority,employee_name:employee_name,keyword:keyword,type:type,currentpage:currentpage_zcj, pagenum:pagenum_zcj}, function (backdata) {
            if (backdata.code == 1000) {
                List_zcj = backdata.data.list;
                Num_zcj = backdata.data.num;
                $.ProductPageShow(Num_zcj,pagenum_zcj,"b");
                $.CurrentPageShow(currentpage_zcj);
				var str='';
				var $dom=$("#nurse_list");
                $("#nurse_list tr:gt(0)").remove();
                $("#zanwushuju").remove();
                var status_arr = ['','新意向学员','跟进中学员','已缴费学员','已报到学员','已放弃学员'];
                var priority_arr = ['','非常有意向','比较有意向','一般','没有意向'];
				if(List_zcj!=null){
                    $.each(List_zcj,function(i,item){

                        str +='<tr>\
                        <td>'+item.source+'</td>\
                        <td>'+item.name+'</td>\
                        <td>'+item.age+'</td>\
                        <td>'+item.phone+'</td>';
                        if(parseInt(backdata.data.status)==6) {
                            str += '<td>'+status_arr[item.status]+'</td>';
                        }
                        if(jQuery.inArray(backdata.data.status,['1','2','6'])!=-1) {
                            str += '<td>'+priority_arr[item.priority]+'</td>';
                        }
                        if(item.next_time&&item.next_time!==''){
                            var jiahao = '('+item.record_employee_name+')<button id="showUse" class="btn btn-default" title="查看联系详情">+</button>';
                        }else{
                            var jiahao= '';
                        }

                        str +='<td>'+item.next_time+jiahao+' \
                                <div id="use" style="display: none;"><p>'+item.remark+'</p></div>\
                                </td>';
                        if(parseInt(backdata.data.status)==4){
                            str +='<td>'+item.cover+item.cover_get+'</td>';
                        }

                        if(parseInt(backdata.data.status)==7){
                            str +='<td>'+item.employee_name+'</td>';
                            str +='<td><a href="'+MODULE+'/Trainee/addTrainee/id/'+item.id+'.html">添加学员</a></td>\
                                </tr>';
                        }else{
                            str +='<td><a href="'+MODULE+'/Trainee/info/id/'+item.id+'.html">查看详情</a></td>\
                                </tr>';
                        }



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