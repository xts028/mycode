/*
 @功能：购物车页面js
 @作者：diamondwang
 @时间：2013年11月14日
 */

$(function() {

    //减少
    $(".reduce_num").click(function() {
        var amount = $(this).parent().find(".amount");
        if (parseInt($(amount).val()) <= 1) {
            alert("商品数量最少为1");
        } else {
            $(amount).val(parseInt($(amount).val()) - 1);
        }

        //发送ajax请求到后端,修改购物车数量
        var data = {
            goods_id: $(amount).attr('data-id'),
            amount: parseInt($(amount).val()),
        };
        $.getJSON(change_amount_url,data,function(data){
            if(!data.status){
                $(amount).val(parseInt($(amount).val()) + 1);
            }
        });

        //小计
        var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
        $(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
        //总计金额
        var total = 0;
        $(".col5 span").each(function() {
            total += parseFloat($(this).text());
        });

        $("#total").text(total.toFixed(2));
    });

    //增加
    $(".add_num").click(function() {
        var amount = $(this).parent().find(".amount");
        $(amount).val(parseInt($(amount).val()) + 1);
        //发送ajax请求到后端,修改购物车数量
        var data = {
            goods_id: $(amount).attr('data-id'),
            amount: parseInt($(amount).val()),
        };
        $.getJSON(change_amount_url,data,function(data){
            if(!data.status){
                $(amount).val(parseInt($(amount).val()) - 1);
            }
        });
        
        
        //小计
        var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(amount).val());
        $(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
        //总计金额
        var total = 0;
        $(".col5 span").each(function() {
            total += parseFloat($(this).text());
        });

        $("#total").text(total.toFixed(2));
    });

    //直接输入
    $(".amount").blur(function() {
        if (parseInt($(this).val()) < 1) {
            alert("商品数量最少为1");
            $(this).val(1);
        }
        
        //发送ajax请求到后端,修改购物车数量
        var data = {
            goods_id: $(this).attr('data-id'),
            amount: parseInt($(this).val()),
        };
        console.debug(data)
        $.getJSON(change_amount_url,data,function(data){
        });
        
        //小计
        var subtotal = parseFloat($(this).parent().parent().find(".col3 span").text()) * parseInt($(this).val());
        $(this).parent().parent().find(".col5 span").text(subtotal.toFixed(2));
        //总计金额
        var total = 0;
        $(".col5 span").each(function() {
            total += parseFloat($(this).text());
        });

        $("#total").text(total.toFixed(2));

    });
    
    
    //删除
    $('.delete').click(function(){
        //找到对应商品的id
        var node = $(this).parent().parent().find('.amount');
        var goods_id = node.attr('data-id');
        //发送ajax请求到后端,修改购物车数量
        var data = {
            goods_id: goods_id,
            amount: 0,
        };
        $.getJSON(change_amount_url,data,function(data){
        });
        
        //删除当前行
        $(this).parent().parent().remove();
        
        //总计金额
        var total = 0;
        $(".col5 span").each(function() {
            total += parseFloat($(this).text());
        });

        $("#total").text(total.toFixed(2));
    });
});