
(function ($) {
    $.extend({
    //显示页码
    ProductPageShow: function (obj,pagenum) {

        var num = obj % pagenum == 0 ? 0 : 1;
        var len = parseInt(obj / pagenum + num);
        var $Container = $("#pagenum").empty();
        var str = '';
        var classstr = '';
        for (var i = 1; i < len + 1; i++) {
            if (i == 1) {
                str = '<a href="javascript:" class="yahei prev_page">上一页</a>\
                          <a href="javascript:" class="yahei pagenum current">' + i + '</a>';
                if (len == 1) {
                    str = '';
                }
            } else if (i == len) {
                str = '<a href="javascript:" class="yahei pagenum">' + i + '</a>\
                          <a href="javascript:" class="yahei next_page">下一页</a>';
            } else {
                if (i > 8) {
                    classstr = 'pagehide';
                } else {
                    classstr = '';
                }
                str = '<a href="javascript:" class="yahei pagenum ' + classstr + '">' + i + '</a>';
            }
            $Container.append(str);
        }
    },
    //根据传入的页码显示对应的样式
    CurrentPageShow: function (currentpage) {
        var page = $("#pagenum").find("a.pagenum");
        var len = $(page).length;
        $("#pagenum").find("a.pagenum").removeClass("current");
        $("#pagenum").find("a.pagenum").addClass("pagehide");
        $("#pagenum").find("span.numoff").remove();
        var spanstr = '<span class="yahei" style="width: 25px;height: 25px;border: 1px solid #666666;display: inline-block;">···</span>';
        for (var i = 0; i < len; i++) {
            if (len < 10) {
                if ($(page[i]).text() == currentpage) {
                    $(page[i]).addClass("current");
                    $(page[i]).removeAttr('href');
                    $(page[i]).attr('read','readonly');
                    $(page[i]).css('cursor','crosshair');
                    $(page[i]).removeClass("pagehide");
                    $(page[0]).removeClass("pagehide");
                    $(page[1]).removeClass("pagehide");
                    $(page[2]).removeClass("pagehide");
                    $(page[3]).removeClass("pagehide");
                    $(page[4]).removeClass("pagehide");
                    $(page[5]).removeClass("pagehide");
                    $(page[6]).removeClass("pagehide");
                    $(page[7]).removeClass("pagehide");
                    $(page[8]).removeClass("pagehide");
                }
            } else {
                if ($(page[i]).text() == currentpage) {
                    $(page[i]).addClass("current");
                    $(page[i]).removeAttr('href');
                    $(page[i]).attr('read','readonly');
                    $(page[i]).css('cursor','crosshair');
                    $(page[i]).removeClass("pagehide");
                    $(page[0]).removeClass("pagehide");
                    $(page[len - 1]).removeClass("pagehide");
                    if (i < 5) {
                        $(page[1]).removeClass("pagehide");
                        $(page[2]).removeClass("pagehide");
                        $(page[3]).removeClass("pagehide");
                        $(page[4]).removeClass("pagehide");
                        $(page[5]).removeClass("pagehide");
                        $(page[6]).removeClass("pagehide");
                        $(page[7]).removeClass("pagehide");
                        $(page[len - 1]).before(spanstr);
                    } else if (i > len - 6) {
                        $(page[len - 2]).removeClass("pagehide");
                        $(page[len - 3]).removeClass("pagehide");
                        $(page[len - 4]).removeClass("pagehide");
                        $(page[len - 5]).removeClass("pagehide");
                        $(page[len - 6]).removeClass("pagehide");
                        $(page[len - 7]).removeClass("pagehide");
                        $(page[len - 8]).removeClass("pagehide");
                        $(page[0]).after(spanstr);
                    } else {
                        $(page[i - 1]).removeClass("pagehide");
                        $(page[i - 2]).removeClass("pagehide");
                        $(page[i - 3]).removeClass("pagehide");
                        $(page[i + 1]).removeClass("pagehide");
                        $(page[i + 2]).removeClass("pagehide");
                        $(page[i + 3]).removeClass("pagehide");
                        $(page[i - 3]).before(spanstr);
                        $(page[i + 3]).after(spanstr);
                    }
                }
            }
        }
        $("#currentpage").text(currentpage);
    }

});
})(jQuery);