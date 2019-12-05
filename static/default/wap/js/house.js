function most(a, b, c) {
    var result = a;
    if (b > result) result = b;
    if (c > result) result = c;
    return result;
}
$.fn.radioForm = function () {
    this.each(function () {
        var list = $(this).find('.gx_radio');
        var forname = $(this).attr('data-name');
        var sid = $('input[name="' + forname + '"]:checked').attr('value');
        if (sid !== '' && !!sid) {
            $(this).find('.gx_radio').removeClass('current');
            $(this).find('.gx_radio[data-val="' + sid + '"]').addClass('current');
        }
        list.click(function (e) {
            e.preventDefault();
            $('input[name="' + forname + '"][value="' + $(this).attr('data-val') + '"]').prop('checked', true);
            list.removeClass('current');
            $(this).addClass('current');
        });
    });
}

function interest() {
    //等额本息法
    //月还款总额
    var loan_a = 0;
    var loan_b = 0;
    var loan_c = 0;
    var year1;      //按揭年数
    var year2;      //公积金贷款年数
    var year3;      //消费贷款年数
    var rate_a = 0;          //按揭利息
    var rate_b = 0;          //公积金利息
    var rate_c = 0;          //消费贷款利息
    var month1 = 0;
    var monthpay1 = 0;
    var month2 = 0;
    var monthpay2 = 0;
    var month3 = 0;
    var monthpay3 = 0;
    var cumulativePrincipal = 0;//累积归还本金
    var cumulativeInterest = 0;//累积偿付利息
    var cumulativeMonthpay = 0;//累积本息总付款额
    var firstMonthpay = 0;//月均还款

    var loantotal = form1.loana.value * 10000 + form1.loanb.value * 10000 + form1.loanc.value * 10000;

    if (form1.loana.value != 0 && form1.year1.value != '') {
        loan_a = form1.loana.value * 10000;
        year1 = form1.year1.value;      //按揭年数
        rate_a = form1.rate2a.value / 12;
        month1 = year1 * 12;
        monthpay1 = Math.round((loan_a * rate_a / 100) * Math.pow((1 + rate_a / 100), month1) / (Math.pow((1 + rate_a / 100), month1) - 1) * 100) / 100;
    }

    if (form1.loanb.value != 0 && form1.year2.value != '') {
        loan_b = form1.loanb.value * 10000;
        year1 = year2 = form1.year2.value;      //按揭年数
        rate_b = form1.rate2b.value / 12;
        month2 = year2 * 12;
        monthpay2 = Math.round((loan_b * rate_b / 100) * Math.pow((1 + rate_b / 100), month2) / (Math.pow((1 + rate_b / 100), month2) - 1) * 100) / 100;
    }

    if (form1.loanc.value != 0 && form1.year3.value != '') {
        loan_c = form1.loanc.value * 10000;
        year3 = form1.year3.value;      //按揭年数
        if (year3 < 4) {
            rate_c = form1.rate1c.value / 12;
        }
        else if (year3 > 5) {
            rate_c = form1.rate3c.value / 12;
        }
        else {
            rate_c = form1.rate2c.value / 12;
        }
        month3 = year3 * 12;
        monthpay3 = Math.round((loan_c * rate_c / 100) * Math.pow((1 + rate_c / 100), month3) / (Math.pow((1 + rate_c / 100), month3) - 1) * 100) / 100;
    }
    var month = most(month1, month2, month3);
    var monthpay = 0;
    var returntotal = monthpay1 * month1 + monthpay2 * month2 + monthpay3 * month3;
    document.all.returntotal.innerText = changeTwoDecimal(returntotal / 10000);
    document.all.interesttotal.innerText = changeTwoDecimal((returntotal - loantotal) / 10000);
    document.all.loantotal.innerText = changeTwoDecimal(loantotal / 10000);

    var interest = 0;  //当月归还利息
    var principal = 0; //当月归还本金
    var payday = new Date(form1.year.value, form1.month.value);
    var bgcolor = '';
    //var str='<table border="0" cellpadding="0" cellspacing="1" class="blackfont">';
    var str = '';
    for (i = 1; i <= month; i++) {
        monthpay = 0;
        interest = 0;
        principal = 0;

        //按揭贷款
        if (form1.loana.value != 0 && form1.year1.value != '' && i <= month1) {
            interest += loan_a * rate_a;
            monthpay += monthpay1;
            principal += monthpay1 - loan_a * rate_a / 100;
            loan_a = Math.round(loan_a * (100 + rate_a) - monthpay1 * 100) / 100;
        }
        else {
            loan_a = 0;
        }

        //公积金贷款
        if (form1.loanb.value != 0 && form1.year2.value != '' && i <= month2) {
            interest += loan_b * rate_b;
            monthpay += monthpay2;
            principal += monthpay2 - loan_b * rate_b / 100;
            loan_b = Math.round(loan_b * (100 + rate_b) - monthpay2 * 100) / 100;
        }
        else {
            loan_b = 0;
        }

        //消费贷款
        if (form1.loanc.value != 0 && form1.year3.value != '' && i <= month3) {
            interest += loan_c * rate_c;
            monthpay += monthpay3;
            principal += monthpay3 - loan_c * rate_c / 100;
            loan_c = Math.round(loan_c * (100 + rate_c) - monthpay3 * 100) / 100;
        }
        else {
            loan_c = 0;
        }

        interest = Math.round(interest) / 100;
        principal = Math.round(principal * 100) / 100;
        cumulativePrincipal += principal;//累积归还本金
        cumulativeInterest += interest;//累积偿付利息
        cumulativeMonthpay += monthpay;//累积本息总付款额

        //灰白相间
        if (i % 2 == 0) bgcolor = ' bgcolor="#fafafa"';
        else bgcolor = '';
        if (i === 1) {
            firstMonthpay = FormatCur(monthpay);
        }

        //显示
        str = str + '<tr' + bgcolor + '>' +
        //'<td>' +i+'</td>'+
        '<td>' + payday.getFullYear() + '年' + (payday.getMonth() + 1) + '月' + '</td>' +
        '<td>' + FormatCur(monthpay) + '</td>' +

        '<td>' + FormatCur(principal) + '</td>' +
        //'<td>' +FormatCur(cumulativePrincipal)+'</td>'+
        '<td>' + FormatCur(interest) + '</td>' +
        //'<td>' +FormatCur(cumulativeInterest)+'</td>'+
        '<td>' + FormatCur(loantotal) + '</td>' +
        //'<td>' +FormatCur(cumulativeMonthpay)+'</td>'+
        '</tr>';

        payday.setMonth(payday.getMonth() + 1);//放贷日
        loantotal = loan_a + loan_b + loan_c;//贷款总额
    }//end for
    //str=str+'</table>';
    //console.info(firstMonthpay);
    window.document.all.i_firstMonthpay.innerHTML = firstMonthpay;
    window.document.all.i_year.innerHTML = year1;
    window.document.all.result.innerHTML = str;
    window.document.all.i_type1.className = 'current';
    window.document.all.i_type2.className = '';
    sdSuccess();
}
function FormatCur(x) {
    var s_x = Math.round(x);
    s_x = '&yen;' + s_x.toString();
    return s_x;
}

function principal() {
    //等额本金法
    //月还款总额
    var loan_a = 0;
    var loan_b = 0;
    var loan_c = 0;
    var year1;      //按揭年数
    var year2;      //公积金贷款年数
    var year3;      //消费贷款年数
    var rate_a = 0;          //按揭利息
    var rate_b = 0;          //公积金利息
    var rate_c = 0;          //消费贷款利息
    var month1 = 0;
    var monthpay1 = 0;
    var month2 = 0;
    var monthpay2 = 0;
    var month3 = 0;
    var monthpay3 = 0;
    var principala;
    var principalb;
    var principalc;
    var cumulativePrincipal = 0;//累积归还本金
    var cumulativeInterest = 0;//累积偿付利息
    var cumulativeMonthpay = 0;//累积本息总付款额
    var firstMonthpay = 0;

    var loantotal = form1.loana.value * 10000 + form1.loanb.value * 10000 + form1.loanc.value * 10000;

    if (form1.loana.value != 0 && form1.year1.value != '') {
        loan_a = form1.loana.value * 10000;
        year1 = form1.year1.value;      //按揭年数

        rate_a = form1.rate2a.value / 12;
        month1 = year1 * 12;
        principala = Math.round(loan_a / month1 * 100) / 100; //当月归还本金
    }

    if (form1.loanb.value != 0 && form1.year2.value != '') {
        loan_b = form1.loanb.value * 10000;
        year1 = year2 = form1.year2.value;      //按揭年数

        rate_b = form1.rate2b.value / 12;
        month2 = year2 * 12;
        principalb = Math.round(loan_b / month2 * 100) / 100; //当月归还本金
    }

    if (form1.loanc.value != 0 && form1.year3.value != '') {
        loan_c = form1.loanc.value * 10000;
        year3 = form1.year3.value;      //按揭年数
        if (year3 < 4) {
            rate_c = form1.rate1c.value / 12;
        }
        else if (year3 > 5) {
            rate_c = form1.rate3c.value / 12;
        }
        else {
            rate_c = form1.rate2c.value / 12;
        }
        month3 = year3 * 12;
        principalc = Math.round(loan_c / month3 * 100) / 100; //当月归还本金
    }
    var month = most(month1, month2, month3);

    var principal = 0;
    var restloan = loantotal;
    var resta = loan_a;
    var restb = loan_b;
    var restc = loan_c;
    var interest = 0;  //当月归还利息
    var payday = new Date(form1.year.value, form1.month.value);
    var bgcolor = '';
    var monthpay = 0;
    var returntotal = 0;
    var str = '';

    for (i = 1; i <= month; i++) {
        interest = 0;
        principal = 0;
        if (form1.loana.value != 0 && form1.year1.value != '' && i <= month1) {
            interest += resta * rate_a
            principal += principala;
            resta = Math.round(resta * 100 - principala * 100) / 100;
        }
        if (form1.loanb.value != 0 && form1.year2.value != '' && i <= month2) {
            interest += restb * rate_b
            principal += principalb;
            restb = Math.round(restb * 100 - principalb * 100) / 100;
        }
        if (form1.loanc.value != 0 && form1.year3.value != '' && i <= month3) {
            interest += restc * rate_c
            principal += principalc;
            restc = Math.round(restc * 100 - principalc * 100) / 100;
        }
        interest = Math.round(interest) / 100;
        monthpay = principal + interest;
        returntotal = returntotal + monthpay;
        cumulativePrincipal += principal;//累积归还本金
        cumulativeInterest += interest;//累积偿付利息
        cumulativeMonthpay += monthpay;//累积本息总付款额

        //灰白相间
        if (i % 2 == 0) bgcolor = ' bgcolor="#fafafa"';
        else bgcolor = '';
        if (i === 1) {
            firstMonthpay = FormatCur(monthpay);
        }
        //显示
        str = str + '<tr' + bgcolor + '>' +

        //'<td>' +i+'</td>'+
        '<td>' + payday.getFullYear() + '年' + (payday.getMonth() + 1) + '月' + '</td>' +
        '<td>' + FormatCur(monthpay) + '</td>' +

        '<td>' + FormatCur(principal) + '</td>' +
        //'<td>' +FormatCur(cumulativePrincipal)+'</td>'+
        '<td>' + FormatCur(interest) + '</td>' +
        //'<td>' +FormatCur(cumulativeInterest)+'</td>'+
        '<td>' + FormatCur(restloan) + '</td>' +
        //'<td>' +FormatCur(cumulativeMonthpay)+'</td>'+
        '</tr>';

        payday.setMonth(payday.getMonth() + 1);//放贷日
        restloan = restloan - principal;//剩余贷款
    }//end for

    //str=str+'</table>';
    document.all.loantotal.innerText = changeTwoDecimal(loantotal / 10000);
    document.all.returntotal.innerText = changeTwoDecimal(returntotal / 10000);
    document.all.interesttotal.innerText = changeTwoDecimal((returntotal - loantotal) / 10000);
    window.document.all.result.innerHTML = str;
    window.document.all.i_firstMonthpay.innerHTML = firstMonthpay;
    window.document.all.i_year.innerHTML = year1;
    window.document.all.i_type2.className = 'current';
    window.document.all.i_type1.className = '';
    sdSuccess();
}

function caculate() {
    if (form1.loana_input.value == '' && form1.loanb_input.value == '' && form1.loanc_input.value == ''){
		layer.msg("商业贷款或者公积金贷款/n至少必须输入一项才能进行计算");
        form1.loana_input.focus();
        return;
    }

    //商业贷款校验
    if (form1.loana_input.value != '') {
        if (String(parseFloat(form1.loana_input.value)) == "NaN") {
			layer.msg("商业贷款金额不正确");
            form1.loana_input.value = "";
            form1.loana_input.focus();
            return;
        }
        if (form1.year1.value != '') {
            if (String(parseFloat(form1.year1.value)) == "NaN") {
				layer.msg("贷款年限不正确");
                form1.year1.value = "";
                form1.year1.focus();
                return;
            }
            if (parseFloat(form1.year1.value) > 30) {
				layer.msg("贷款年限不能超过30年");
                form1.year1.value = "";
                form1.year1.focus();
                return;
            }
        }
        else {
			layer.msg("您还没有输入贷款年限");
            form1.year1.focus();
            return;
        }
        form1.loana.value = form1.loana_input.value;
    }
    else {
        form1.loana.value = 0;
        form1.year1.value = '';
    }

    //公积金贷款校验
    if (form1.loanb_input.value != '') {
        if (String(parseFloat(form1.loanb_input.value)) == "NaN") {
			layer.msg("公积金贷款金额不正确");
            form1.loanb_input.value = "";
            form1.loanb_input.focus();
            return;
        }
        form1.year2.value = form1.year1.value;
        form1.loanb.value = form1.loanb_input.value;
    }
    else {
        form1.loanb.value = 0;
        form1.year2.value = '';
    }

    //消费贷款校验
    if (form1.loanc_input.value != '') {
        if (String(parseFloat(form1.loanc_input.value)) == "NaN") {
			layer.msg("消费贷款金额不正确");
            form1.loanc_input.value = "";
            form1.loanc_input.focus();
            return;
        }
        if (form1.year3.value != '') {
            if (String(parseFloat(form1.year3.value)) == "NaN") {
				layer.msg("消费贷款年限不正确");
                form1.year3.value = "";
                form1.year3.focus();
                return;
            }
            if (parseFloat(form1.year3.value) > 30) {
				layer.msg("消费贷款年限不能超过30年");
                form1.year3.value = "";
                form1.year3.focus();
                return;
            }
        }
        else {
			layer.msg("您还没有输入消费贷款年限");
            form1.year3.focus();
            return;
        }
        form1.loanc.value = form1.loanc_input.value;
    }
    else {
        form1.loanc.value = 0;
        form1.year3.value = '';
    }
    var formtype = $('input[name="type"]:checked').val();
    if (formtype == 1) interest();
    else principal();
}

function caculate1() {
    //商业贷款校验
    if (form1.loana_input.value != '') {
        if (String(parseFloat(form1.loana_input.value)) == "NaN") {
			layer.msg("商业贷款金额不正确");
            form1.loana_input.value = "";
            form1.loana_input.focus();
            return;
        }
        form1.loana.value = form1.loana_input.value;
    }
    else {
		layer.msg("您还没有输入商业贷款金额");
        form1.loana_input.focus();
        return;
    }
    if (form1.year1.value != '') {
        if (String(parseFloat(form1.year1.value)) == "NaN") {
			layer.msg("商业贷款年限不正确");
            form1.year1.value = "";
            form1.year1.focus();
            return;
        }
        if (parseFloat(form1.year1.value) > 30){
			layer.msg("商业贷款年限不能超过30年");
            form1.year1.value = "";
            form1.year1.focus();
            return;
        }
    }
    else {
		layer.msg("您还没有输入商业贷款年限");
        form1.year1.focus();
        return;
    }

    form1.loanb.value = 0;
    form1.loanc.value = 0;

    var formtype = $('input[name="type"]:checked').val();
    if (formtype == 1) interest();
    else principal();
}

function caculate2() {
    //公积金贷款校验
    if (form1.loanb_input.value != '') {
        if (String(parseFloat(form1.loanb_input.value)) == "NaN") {
			layer.msg("公积金贷款金额不正确");
            form1.loanb_input.value = "";
            form1.loanb_input.focus();
            return;
        }
        form1.loanb.value = form1.loanb_input.value;
    }
    else {
		layer.msg("您还没有输入公积金贷款金额");
        form1.loanb_input.focus();
        return;
    }
    if (form1.year1.value != '') {


        if (String(parseFloat(form1.year1.value)) == "NaN") {
			layer.msg("公积金贷款年限不正确");
            form1.year1.value = "";
            form1.year1.focus();
            return;
        }
        if (parseFloat(form1.year2.value) > 30) {
			layer.msg("公积金贷款年限不能超过30年");
            form1.year1.value = "";
            form1.year1.focus();
            return;
        }
    }
    else {
		layer.msg("您还没有输入公积金贷款年限");
        form1.year1.focus();
        return;
    }
    form1.year2.value = form1.year1.value;
    form1.loana.value = 0;
    form1.loanc.value = 0;
    var formtype = $('input[name="type"]:checked').val();
    if (formtype == 1) interest();
    else principal();
}

function caculate3() {
    //消费贷款校验
    if (form1.loanc_input.value != '') {
        if (String(parseFloat(form1.loanc_input.value)) == "NaN") {
			layer.msg("消费贷款金额不正确");
            form1.loanc_input.value = "";
            form1.loanc_input.focus();
            return;
        }
        form1.loanc.value = form1.loanc_input.value;
    }
    else {
		layer.msg("您还没有输入消费贷款金额");
        form1.loanc_input.focus();
        return;
    }
    if (form1.year3.value != '') {
        if (String(parseFloat(form1.year3.value)) == "NaN") {
			layer.msg("消费贷款年限不正确");
            form1.year3.value = "";
            form1.year3.focus();
            return;
        }
        if (parseFloat(form1.year3.value) > 30) {
			layer.msg("消费贷款年限不能超过30年");
            form1.year3.value = "";
            form1.year3.focus();
            return;
        }
    }
    else {
		layer.msg("您还没有输入消费贷款年限");
        form1.year3.focus();
        return;
    }

    form1.loana.value = 0;
    form1.loanb.value = 0;
    var formtype = $('input[name="type"]:checked').val();
    if (formtype == 1) interest();
    else principal();
}

function recaculate() {
    form1.reset();
    document.all.loantotal.innerText = "";
    document.all.returntotal.innerText = "";
    document.all.interesttotal.innerText = "";
    document.all.i_firstMonthpay.innerText = "";
    document.all.i_year.innerText = "";
    document.all.result.innerHTML = "";
    document.getElementById('result_node').style.display = 'none';
    document.getElementById('form_node').style.display = 'block';
}
function sdRate() {
    var rate = document.getElementById('sd-rate');
    var zk = document.getElementById('sd-zk');
    var rate2a = document.getElementById('rate2a');
    rate2a.value = changeTwoDecimal(rate.value * zk.value);
}


function changeTwoDecimal(x) {//至多两位小数 城市联盟
    var f_x = parseFloat(x);
    if (isNaN(f_x)) {
        $.toast('function:changeTwoDecimal->parameter error');
        return false;
    }
    f_x = Math.round(f_x * 100) / 100;
    return f_x;
}

function sdSuccess() {
    document.getElementById('result_node').style.display = 'block';
    document.getElementById('form_node').style.display = 'none';
    var w_h = $(window).height(), result_node_1 = $('#result_node_1'), result_node_2 = $('#result_node_2');
    console.info(w_h, result_node_1.outerHeight())
    result_node_2.css({ 'height': parseInt(w_h - result_node_1.outerHeight() - 36) + 'px' });

}
$.fn.tabs = function () {
    var t = $(this), list = t.find('.item'), zuhe_1 = t.find('.zuhe_1'), zuhe_2 = t.find('.zuhe_2'), btn_list = t.find('.btn'),
	loana_input = $('#loana_input'), loanb_input = $('#loanb_input'), year1 = $('#year1'), year2 = $('#year2');
    list.click(function () {
        loana_input.val('');
        loanb_input.val('');

        list.removeClass('current');
        $(this).addClass('current');
        var val = $(this).attr('data-val');
        if (val === '1') {
            zuhe_1.show();
            zuhe_2.hide();
            btn_list.hide();
            $('#button1').show()
        } else if (val === '2') {
            zuhe_1.hide();
            zuhe_2.show();
            btn_list.hide();
            $('#button2').show()
        } else {
            zuhe_1.show();
            zuhe_2.show();
            btn_list.hide();
            $('#button3').show()
        }
    });
    $('#i_type1').click(function () {
        $('#type1').prop('checked', true);
        submitForm();
    });
    $('#i_type2').click(function () {
        $('#type2').prop('checked', true);
        submitForm();
    });
    function submitForm() {
        var val = t.find('.current').attr('data-val');
        $('#button' + val).trigger('click');
    }
}