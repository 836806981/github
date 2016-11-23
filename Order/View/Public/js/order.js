var currentpage_zcj = 1;
var pagenum_zcj =20;
var List_zcj=[];
var Num_zcj=[];
var keyword = $("#keyword").val();
var priority = $("#priority").val();
var order_type = $("#type").val();
var order_sales_id = $("#sales_id").val();
var order_status = $("#status").val();
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

        //页码点击效果
        $("#priority").live("change", function () {
            priority = $("#priority").val();
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
        //筛选
        $("#search").live("click", function () {

             order_type = $("#type").val();
             order_sales_id = $("#sales_id").val();
             order_status = $("#status").val();
            currentpage_zcj = 1;
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
        $.AjaxPost(MODULE+"/Order/getList", {order_status:order_status,keyword:keyword,type:type,currentpage:currentpage_zcj, pagenum:pagenum_zcj,order_type:order_type,order_sales_id:order_sales_id}, function (backdata) {
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
                        var is_customer_service = parseInt(item.is_customer_service)>0?'<i style="display:inline-block;width: 20px;height: 20px;border-radius: 10px;line-height: 20px;font-size: 8px;text-align: center;background: red;color: #ffffff;">售</i>':'';
                        if(type=='trainee'){
                            str +='<tr>\
                                    <td>'+item.source+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.add_time+'</td>\
                                    <td>'+item.employee_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/addTrainee/id/'+item.id+'.html">修改</a></td>';
                        }else if(type=='user'){
                            var arr = ['','管理员','客服','销售','阿姨管理人员','财务'];
                            str +='<tr>\
                                    <td>'+item.username+'</td>\
                                    <td>'+arr[item.permission]+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.real_name+'</td>\
                                    <td><a href="'+MODULE+'/Admin/editUser/id/'+item.id+'.html">修改</a> | <a href="'+MODULE+'/Admin/rePermission/id/'+item.id+'.html" title="重置为：tlay123">重置密码</a> </td>';
                        }else  if(type==0){
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_employee+'</td>\
                                    <td>'+item.add_time+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.source+'</td>\
                                    <td><a href="'+MODULE+'/Order/distribution/id/'+item.id+'.html">派单</a></td>';
                        }else  if(type==1){
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_employee+'</td>\
                                    <td>'+item.status_1_time+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.source+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.status_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a></td>';
                        }else  if(type==2){
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_employee+'</td>\
                                    <td>'+item.add_time+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.source+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.covenant_time+'</td>\
                                    <td><a href="'+MODULE+'/Order/covenant_info/id/'+item.id+'.html">查看合同</a></td>';
                        }else  if(type==3){
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_employee+'</td>\
                                    <td>'+item.add_time+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.nurse_phone+'/'+item.nurse_urgent_phone+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.true_expect_time_b+'</td>\
                                    <td>'+item.expect_time_s+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a></td>';
                        }else  if(type==4){//已下户
                            str +='<tr>\
                                    <td>'+is_customer_service+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.nurse_phone+'/'+item.nurse_urgent_phone+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.true_expect_time_b+'</td>\
                                    <td>'+item.expect_time_s+'</td>\
                                    <td>'+item.true_expect_time_s+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/balance/id/'+item.id+'.html">结算</a></td>';
                        }else  if(type==5){//已结算
                            str +='<tr>\
                                    <td>'+is_customer_service+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.phone+'/'+item.urgent_phone+'</td>\
                                    <td>'+item.expect_time_s+'</td>\
                                    <td>'+item.true_expect_time_s+'</td>\
                                    <td>'+item.service_charge+'</td>\
                                    <td>'+item.balance_money+'</td>';
                                    //<td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a> | <a  href="'+MODULE+'/Order/out_customer/id/'+item.id+'" name="out_customer">提醒下户</a></td>';
                        }else  if(type==6){
                            if(item.status==2){
                                var die_time= item.covenant_time;
                            }else if(item.status==10){
                                var die_time= item.die_time;
                            }
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_employee+'</td>\
                                    <td>'+item.add_time+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.status_name+'</td>\
                                    <td>'+item.status_time+'</td>\
                                    <td>'+item.visit_next_time+'</td>\
                                    <td><a href="'+MODULE+'/Order/visit/id/'+item.id+'.html">回访</a></td>';
                        }else  if(type==7){//计划体检
                            str +='<tr>\
                                    <td>'+is_customer_service+item.nurse_name+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.urgent_phone+'</td>\
                                    <td>'+item.wechat+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.estimated_time+'</td>\
                                    <td><a href="'+MODULE+'/Order/body_test/id/'+item.id+'.html">查看体检</a></td>';
                        }else  if(type==8){//投保体检
                            str +='<tr>\
                                    <td>'+is_customer_service+item.nurse_name+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.urgent_phone+'</td>\
                                    <td>'+item.wechat+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.buy_safe_time+'</td>\
                                    <td><a href="'+MODULE+'/Order/safe/id/'+item.id+'.html">查看投保</a></td>';
                        }else  if(type==9){//客户付款
                            str +='<tr>\
                                    <td>'+is_customer_service+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.expect_money+'</td>\
                                    <td>'+item.expect_time+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/expect/id/'+item.id+'.html">催款</a></td>';
                        }else  if(type==10){//已放弃
                            str +='<tr>\
                                    <td>'+is_customer_service+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.source+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.die_time+'</td>\
                                    <td>'+item.die_reason+'</td>\
                                    <td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a></td>';
                        }else  if(type==11){//上户提醒
                            str +='<tr>\
                                    <td>'+is_customer_service+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.phone+'/'+item.urgent_phone+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.expect_time_b_3+'</td>\
                                    <td>'+item.expect_time_b_15+'</td>\
                                    <td>'+item.true_expect_time_b+'</td>\
                                    <td>'+item.expect_time_s+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td><a  href="'+MODULE+'/Order/change_time/id/'+item.id+'" name="change_time_b">更改上户日期</a> | <a href="'+MODULE+'/Order/training/id/'+item.id+'.html">上户培训</a> | <a  href="'+MODULE+'/Order/go_customer/id/'+item.id+'" name="go_customer">提醒上户</a></td>';
                        }else  if(type==12){//下户提醒
                            str +='<tr>\
                                    <td>'+is_customer_service+item.type_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.phone+'/'+item.urgent_phone+'</td>\
                                    <td>'+item.expect_time_b+'</td>\
                                    <td>'+item.true_expect_time_b+'</td>\
                                    <td>'+item.expect_time_s+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td><a  href="'+MODULE+'/Order/change_time/id/'+item.id+'" name="change_time_s">更改下户日期</a> |<a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a> | <a  href="'+MODULE+'/Order/out_customer/id/'+item.id+'" name="out_customer">提醒下户</a></td>';
                        }else  if(type==13){//面试计划
                            str +='<tr>\
                                    <td>'+is_customer_service+item.name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.interview_time+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">去添加面试详情</a></td>';
                        }else if(type==14){//督导计划
                            str +='<tr>\
                                    <td>'+is_customer_service+item.name+'</td>\
                                    <td>'+item.nurse_name+'</td>\
                                    <td>'+item.phone+'/'+item.urgent_phone+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.next_supervisor_time+'</td>\
                                    <td><a href="'+MODULE+'/Order/supervisor/id/'+item.id+'.html">去督导</a></td>';
                        }else  if(type==33){//总订单
                            var paidan = '';
                            if(item.status==0){
                                paidan = '| <a href="'+MODULE+'/Order/distribution/id/'+item.id+'.html">派单</a> '
                            }
                            str +='<tr>\
                                    <td>'+is_customer_service+item.add_time+'</td>\
                                    <td>'+item.add_employee+'</td>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.type_name+'</td>\
                                    <td>'+item.contact_way+':'+item.contact_number+'</td>\
                                    <td>'+item.source+'</td>\
                                    <td>'+item.sales_name+'</td>\
                                    <td>'+item.status_name+'</td>\
                                    <td><a href="'+MODULE+'/Order/info/id/'+item.id+'.html">查看详情</a> | <a href="'+MODULE+'/Order/to_customer_service/id/'+item.id+'.html">售后</a> '+paidan+'</td>';
                        }
                        str += '</tr>';
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