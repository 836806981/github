
(function ($) {
    $.extend({
    //显示页码
    ProductPageShow: function (obj,pagenum,currentpage_zcj) {
        var num = obj % pagenum == 0 ? 0 : 1;
        var len = parseInt(obj / pagenum + num);
        var $Container = $("#pagenum").empty();
        var str = '';
        if(len>1) {
            if (currentpage_zcj == 1) {
                str += ' <a href="javascript:" class="yahei next_page">下一页</a>';
            } else if (currentpage_zcj == len) {
                str += '<a href="javascript:" class="yahei prev_page">上一页</a>';
            } else {
                str += ' <a href="javascript:" class="yahei prev_page">上一页</a><a href="javascript:" class="yahei next_page">下一页</a>';
            }
        }
        $Container.append(str);
    }
});
})(jQuery);