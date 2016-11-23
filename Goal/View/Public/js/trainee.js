var currentpage_zcj = 1;
var pagenum_zcj =20;
var List_zcj=[];
var Num_zcj=[];
//var keyword = $("#keyword").val();
//var priority = $("#priority").val();
//var employee_name = $("#employee_name").val();
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
        $.AjaxPost(MODULE+"/Goal/getGoal", {user_id:user_id,status:status,currentpage:currentpage_zcj, pagenum:pagenum_zcj}, function (backdata) {
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
                        var edit_str = '';
                        if (item.type == 1) {
                            var file = '<img src="' + UPLOADS + '/' + item.file + '" title="点击看大图" style="width: 60px;height: 26px;" id="dd"/>';
                        } else if(item.type == 2){
                            var file = '<a href="' +  UPLOADS + '/'+item.file+'" title="下载文件"><button>↓</button>'+item.type_name+'</a>';
                        }else{
                            var file = '无附件';
                        }
                        if(item.is_boss==1&&item.project_id==0){
                            if(item.status<3){
                                edit_str += '<a href="'+MODULE+'/Goal/edit_Goal/id/'+item.id+'">修改</a> | ';
                            }
                            if(item.status==2){
                                edit_str+= '<a href="'+MODULE+'/Goal/edit_goal_status_3/id/'+item.id+'">通过审核</a> | ';
                            }
                        }
                        str +='<tr>\
                        <td>'+item.name+'</td>\
                        <td>'+item.user_name+'</td>\
                        <td>'+item.project_name+'</td>\
                        <td>'+item.b_time+'</td>\
                        <td>'+item.day_number+'</td>\
                        <td>'+item.s_time+'</td>\
                        <td>'+item.s_time_true+'</td>\
                        <td>'+item.status_name+'</td>\
                        <td>'+(item.status==3?(item.time_true==1?'超时':'是'):'否')+'</td>\
                        <td>'+edit_str+'<a href="'+MODULE+'/Goal/goal_info/id/'+item.id+'">查看详情</a></td>\
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