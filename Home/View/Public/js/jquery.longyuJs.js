// JavaScript Document

/**
 * 
 * This plugin needs at least jQuery 1.4.4
 *
 * @author yu.long
 * @version 1.0
 * 
 * 添加请求模式(GET/POST) . 修改于 2013-04-19 by yu.long
 * 修改mySubString方法，增加判断传入字符串是否为空。  修改于2013年10月29日 16:11:03  by  yu.long
 * 修改keyToLongTime方法，增加判断传入字符串是否为约定的时间戳格式 。 修改于2013年10月31日 16:06:46 by yu.long
 * 添加deleteById方法，用于删除功能的简化。 添加于2013年11月21日 16:55:27 by yu.long
 */
(function ($) {
    $.extend({
        AjaxPost: function (_url, _data, _backfun, isMask, _async, _type,before) {
            if (isMask) {
                var r = Math.random().toString().substring(3, 10);
                $.waitMask(r);
            }
            if (_async === undefined)
                _async = true;
            if (_type === undefined)
                _type = 'POST';

            $.ajax({
                type: _type,
                url: _url,
                data: _data,
                dataType: "json",
                cache: false,
                traditional:true,
                success: function (backdata) {
                    _backfun(backdata);
                },
                beforeSend:function(){
                    before();
                },
                timeout: 15000,
                complete: function (comdata) {
                    $.ClosewaitMask(r);
                },
                error: function (XMLHttpRequest) {
                    $.ClosewaitMask(r);
                    if (!!XMLHttpRequest) {
                        if (XMLHttpRequest.status != null && XMLHttpRequest.status) {
                            alert("请求出现异常.状态代码为:" + XMLHttpRequest.status);
                        } else {
                            alert("请求出现异常");
                        }
                    } else {
                        alert("请求出现异常");
                    }
                },
                async: _async
            });
        },
        //数据源翻页处理1
        //@keys:查询字段，@dataSource:数据源,@conditions:条件,@sortKey:排序规则,@tot:总条数,@$Container:列表容器,@$pagedom:翻页脚码容器，@pageNumber:每页条数,@maNum:脚码个数,@backfun:回调函数
        initPagination: function (keys, dataSource, conditions, sortKey, tot, $Container, $pagedom, pageNumber, maNum, backfun) {
            // Create pagination element
            $pagedom.pagination(tot, {
                num_edge_entries: 0,
                num_display_entries: maNum,
                callback: function (page_index, jq) {
                    $.getnewslist(keys, dataSource, conditions, sortKey, page_index, $Container, pageNumber, backfun);
                },
                items_per_page: 1
            });
        },
        //数据源翻页处理2
        //@keys:查询字段，@dataSource:数据源,@conditions:条件,@sortKey:排序规则,@page_index:下页页码,@$Container:列表容器,@pageNumber:每页条数,回调函数.
        getnewslist: function (keys, dataSource, conditions, sortKey, pg, $Container, pageNumber, backfun) {
            var sqlStr = "select " + keys + " from " + dataSource + " where (1==1" + conditions + ") " + sortKey;
            var arrObj = jsonsql.query(sqlStr, dataSource);
            var data = arrObj.slice(pg * pageNumber, (pg + 1) * pageNumber);
            backfun(data, $Container, sortKey);
        },
        //根据数据源翻页处理1（单纯分页）
        //@dataSource:数据源,@$Container:列表容器,@$pagedom:翻页脚码容器，@pageNumber:每页条数,@maNum:脚码个数,@backfun:回调函数
        initPaginationByDataSources: function (dataSource, $Container, $pagedom, pageNumber, maNum, backfun) {
            // Create pagination element
            tot = Math.ceil(dataSource.length / pageNumber);
            $pagedom.pagination(tot, {
                num_edge_entries: 0,
                num_display_entries: maNum,
                callback: function (page_index, jq) {
                    $.getnewslistByDataSources(dataSource, page_index, $Container, pageNumber, backfun);
                },
                items_per_page: 1
            });
        },
        //根据数据源翻页处理2（单纯分页）
        //@keys:查询字段，@dataSource:数据源,@conditions:条件,@sortKey:排序规则,@page_index:下页页码,@$Container:列表容器,@pageNumber:每页条数,回调函数.
        getnewslistByDataSources: function (dataSource, pg, $Container, pageNumber, backfun) {
            var data = dataSource.slice(pg * pageNumber, (pg + 1) * pageNumber);
            backfun(data, $Container);
        },
        //异步请求数据翻页处理1
        //@tot:总条数,@_Container:列表容器，@_pagedom:翻页脚码容器,@_postUrl:请求地址,@_postData:请求对象,@_onePageNumber:每页条数,@maNum:脚码个数,@backfun:回调函数
        initPaginationByAjax: function (tot, _Container, _pagedom, _postUrl, _postData, _onePageNumber, maNum, backfun) {
            // Create pagination element
            if (tot != 0) {
                $(_pagedom).pagination(tot, {
                    num_edge_entries: 0,
                    num_display_entries: maNum,
                    callback: function (page_index, jq) {
                        $.getnewslistByAjax((page_index + 1), _Container, _postUrl, _postData, _onePageNumber, backfun);
                    },
                    items_per_page: 1
                });
            } else {
                $(_pagedom).empty();
                $.getnewslistByAjax(1, _Container, _postUrl, _postData, _onePageNumber, backfun);
            }
        },
        //异步请求数据翻页处理2
        //@pg:下页页码,@_Container:列表容器,@_postUrl:post地址,@_postData:post对象,@_onePageNumber:每页条数,@backfun:回调函数.
        getnewslistByAjax: function (pg, _Container, _postUrl, _postData, _onePageNumber, backfun) {
            _postData.pageIndex = pg;
            _postData.pageSize = _onePageNumber;
            $.AjaxPost(_postUrl, _postData, function (backdata) {
                if (backdata.ReCode == 1000) {
                    backfun(backdata.ReData, _Container);
                } else {
                    alert(backdata.msg);
                }
            });
        },
        waitMask: function (_r) {
        },
        ClosewaitMask: function (_r) {
            $("#wait_Mask" + _r).remove();
        },
        //时间戳转换成日期,格式：2013-5-4
        keyToDateTime: function (keyDate) {
            var v = parseInt(keyDate.substring(6, keyDate.length - 2));
            var d = new Date();
            d.setTime(v);
            var mydate = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
            return mydate;
        },
        //时间戳转换成日期,格式：5月4日 (日期减1)
        keyToDateTime24: function (keyDate) {
            var v = parseInt(keyDate.substring(6, keyDate.length - 2));
            var d = new Date();
            d.setTime(v);
            var mydate = (d.getMonth() + 1) + "月" + (d.getDate()-1)+"日";
            return mydate;
        },
        //时间戳转换成年月,格式：2013-5
        keyToMonthTime: function (keyDate) {
            var v = parseInt(keyDate.substring(6, keyDate.length - 2));
            var d = new Date();
            d.setTime(v);
            var mydate = d.getFullYear() + "-" + (d.getMonth() + 1);
            return mydate;
        },
        //时间戳转换成时间，格式：2013-5-4 21:7:26
        keyToLongTime: function (keyDate) {
            var v = parseInt(keyDate.substring(6, keyDate.length - 2));
            if (!/^[0-9]*[1-9][0-9]*$/.test(v)) {
                return keyDate.substring(0, keyDate.length - 3);
            }
            var d = new Date();
            d.setTime(v);
            var mydate = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
            return mydate;
        },
        //时间戳转换成时间，格式：2013-5-4-21-7-26
        keyToSecondsTime: function (keyDate) {
            var v = parseInt(keyDate.substring(6, keyDate.length - 2));
            if (!/^[0-9]*[1-9][0-9]*$/.test(v)) {
                return keyDate.substring(0, keyDate.length - 3);
            }
            var d = new Date();
            d.setTime(v);
            var mydate = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() + "-" + d.getHours() + "-" + d.getMinutes() + "-" + d.getSeconds();
            return mydate;
        },
        //字符串截取，默认10个字符。
        mySubString: function (str, num) {
            if (str == null) {
                return "";
            }
            if (!num) {
                num = 10;
            }
            if (typeof str != "undefined" && str.toString().length > num) {
                return str.substring(0, num) + '..';
            } else {
                return str;
            }
        },
        //头部导航定位。  navArr:传入对象，内涵字符串集合
        setNavByArr: function (navArr) {
            var currentNav = 0;
            var initialUrl = window.location.href.toLowerCase();
            var urls = initialUrl.split("/index.php");
            var childDirectory = urls[1];
		    if(childDirectory!=undefined){
				for (var p in navArr) {
				   var oLen = navArr[p].length;
				   for (var o = 0; o < oLen; o++) {
					   var str = (navArr[p][o]).toLowerCase();
					   if (childDirectory.indexOf(str) != -1) {
						   currentNav = p.split("_")[1];
					   }
				   }
				}
		    }
            return currentNav;
        },
        //头部导航定位。  navArr:传入对象，内涵字符串集合
        setMemNavByArr: function (navMemArr) {
            var currentNav = 0;
            var initialUrl = window.location.href.toLowerCase();
            var urls = initialUrl.split("/member");
            if (urls.length <= 1) {
                if (urls[0].indexOf('/member') != -1) {
                    currentNav = 0;
                }
            } else {
                var childDirectory = urls[1];
                for (var p in navMemArr) {
                    var oLen = navMemArr[p].length;
                    for (var o = 0; o < oLen; o++) {
                        var str = (navMemArr[p][o]).toLowerCase();
                        if (childDirectory.indexOf(str) != -1) {
                            currentNav = p.split("_")[1];
                        }
                    }
                }
            }
            return currentNav;
        },
        //获取url参数
        getRequest: function (strName) {
            var strHref = document.location.href.split("#")[0];
            var intPos = strHref.indexOf("?");
            var strRight = strHref.substr(intPos + 1);
            var arrTmp = strRight.split("&");
            for (var i = 0; i < arrTmp.length; i++) {
                var arrTemp = arrTmp[i].split("=");
                if (arrTemp[0].toUpperCase() == strName.toUpperCase()) return arrTemp[1];
            }
            return "";
        },
        //处理小数点(四舍五入)，保留N位小数。
        getDecimal: function (num, n) {
            return Math.round(num * Math.pow(10, n)) / Math.pow(10, n);
        },
        //根据ID删除
        //@Id:ID， @Url:请求地址， @method:post/get
        deleteById: function (Id, Url, method, backfun) {
            if (!(Id && Url && method)) {
                alert("参数错误");
                return false;
            }
            if (!window.confirm("确认要删除吗？")) {
                return false;
            }
            if (method.toLowerCase() == "post") {
                if (typeof (Id) == "string") {
                    $.AjaxPost(Url, { Id: Id }, function (backdata) {
                        if (typeof (backfun) == "function")
                            backfun(backdata);
                    }, true);
                } else if (typeof (Id) == "object") {
                    $.AjaxPost(Url, { Ids: Id }, function (backdata) {
                        if (typeof (backfun) == "function")
                            backfun(backdata);
                    }, true);
                }
            } else if (method.toLowerCase() == "get") {
                $.AjaxPost(Url + "?id=" + Id, null, function (backdata) {
                    if (typeof (backfun) == "function")
                        backfun(backdata);
                }, true, true, "GET");
            }
        }
    });

})(jQuery);





//去掉数组重复项.
function ly_uniq(arr) {
    var temp = {}, len = arr.length;

    for (var i = 0; i < len; i++) {
        if (typeof temp[arr[i]] == "undefined") {
            temp[arr[i]] = 1;
        }
    }
    arr.length = 0;
    len = 0;
    for (var i in temp) {
        arr[len++] = i;
    }
    return arr;
}  