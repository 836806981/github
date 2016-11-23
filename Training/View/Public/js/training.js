var currentpage_zcj = 1;
var pagenum_zcj =20;
var List_zcj=[];
var Num_zcj=[];
var keyword = $("#keyword").val();
var priority = $("#priority").val();
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
    },
    initinfo: function () {
        //根据页码获取数据
        $.getNewsListByAllTerm();
		
    },
	//根据所有条件查询新闻列表
    getNewsListByAllTerm: function () {
        //异步提交数据,参数：currentpage,要查询的页码;pagenum，每页的条数
        $.AjaxPost(MODULE+"/Training/getTrainingList", {keyword:keyword,type:type,currentpage:currentpage_zcj, pagenum:pagenum_zcj}, function (backdata) {
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
                var zao = '';
				if(List_zcj!=null){
                    $.each(List_zcj,function(i,item){
                            zao = '';
                            if(parseInt(item.trainee_id)==0){
                               zao = '<i style="width: 20px; height: 20px; border-radius: 10px;display: inline-block;background: red;font-size:10px;color: #ffffff; line-height: 20px;text-align: center;">招</i>';
                            }
                            if(backdata.data.type=='10'){
                                str +='<tr>\
                                    <td>'+item.name+'</td>\
                                    <td>'+item.cover+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td><a href="'+MODULE+'/Training/addTraining/id/'+item.id+'.html">报道该学员</a></td>';
                            }else if(backdata.data.type=='1'){
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td><a href="'+MODULE+'/Training/info/id/'+item.id+'.html">查看</a></td>';
                            }else if(backdata.data.type=='2'){
                                var zhengshu = item.stop_zhengshu==1?'√':'×';
                                var to_nurse =  item.to_nurse==0?' | <a href="'+MODULE+'/Training/to_nurse/id/'+item.id+'.html">输出</a>':'已输出';
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.stop_time+'</td>\
                                    <td>'+zhengshu+'</td>\
                                    <td><a href="'+MODULE+'/Training/info/id/'+item.id+'.html">查看</a> '+to_nurse+'</td>';
                            }else if(backdata.data.type=='3'){
                                var to_nurse =  item.to_nurse==0?' | <a href="'+MODULE+'/Training/to_nurse/id/'+item.id+'.html">输出</a>':'已输出';
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.use.b_time+'--'+item.use.s_time+'</td>\
                                    <td>'+item.use.use_customer+'</td>\
                                    <td>'+item.use.teacher+'</td>\
                                    <td><a href="'+MODULE+'/Training/tra_use/id/'+item.id+'.html">查看签单记录</a> '+to_nurse+'</td>';
                            }else if(backdata.data.type=='4'){
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.die_time+'</td>\
                                    <td>'+item.die_reason+'</td>\
                                    <td><a href="'+MODULE+'/Training/info/id/'+item.id+'.html">查看</a></td>';
                            }else if(backdata.data.type=='5'){
                                var to_nurse =  item.to_nurse==0?' | <a href="'+MODULE+'/Training/to_nurse/id/'+item.id+'.html">输出</a>':'已输出';
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.zhengshu.test_time+'</td>\
                                    <td>'+item.zhengshu.test_level+'</td>\
                                    <td>'+item.zhengshu.get_time+'</td>\
                                    <td><a href="'+MODULE+'/Training/zhengshu/id/'+item.id+'.html">查看考证记录</a>'+to_nurse+'</td>';
                            }else if(backdata.data.type=='6'){
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.body_test_time+'</td>\
                                    <td><a href="'+MODULE+'/Training/body_test/id/'+item.id+'.html">查看体检记录</a></td>';
                            }else if(backdata.data.type=='7'){
                                if(backdata.data.sess.permission==1||backdata.data.sess.edit_per==1){
                                    var edit = ' | <a href="javascript:" add_day="edit" date="'+item.date+'"  id_value="'+item.id+'"  teacher="'+item.teacher+'"  content="'+item.content+'" remark="'+item.remark+'" meeting_people="'+item.meeting_people+'" company_needs="'+item.company_needs+'" meeting_content="'+item.meeting_content+'">重新生成</a>';
                                }
                                str +='<tr>\
                                    <td>'+zao+item.date+'</td>\
                                    <td>'+item.number+'</td>\
                                    <td>'+item.teacher+'</td>\
                                    <td><a href="'+MODULE+'/Training/day_report/id/'+item.id+'.html">查看日报</a>'+edit+'\
                                    </td>';
                            }else if(backdata.data.type=='8'){
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.birthday+'</td>';
                            }else if(backdata.data.type=='9'){
                                var zhengshu = item.stop_zhengshu==1?'√':'×';
                                var to_nurse =  item.to_nurse==2?'已报到':'未报到';
                                str +='<tr>\
                                    <td>'+zao+item.name+'</td>\
                                    <td>'+item.age+'</td>\
                                    <td>'+item.phone+'</td>\
                                    <td>'+item.id_card+'</td>\
                                    <td>'+item.stop_time+'</td>\
                                    <td>'+zhengshu+'</td>\
                                    <td>'+to_nurse+'</td>';
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