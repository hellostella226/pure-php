let server = window.location.hostname;
let link;
switch (server.substring(0, 1)) {
    case 't':
        link = 'http://tadmin.g******com';
        break;
    case 'l':
        link = 'http://ladmin.g******com';
        break;
    default:
        link = 'https://admin.g******com';
        break;
}

(function () {
    "use strict";

    /**
     * Easy selector helper function
     */
    const select = (el, all = false) => {
        el = el.trim()
        if (all) {
            return [...document.querySelectorAll(el)]
        } else {
            return document.querySelector(el)
        }
    }

    /**
     * Navbar links active state when location match href
     */
    let urlView = window.location.pathname;
    let navlinks = select('.nav-select', true);

    if (urlView.includes('bioage')){
        select('.bioage').classList.remove('btn-success');
        select('.bioage').classList.add('btn-secondary');
    } else {
        select('.***').classList.remove('btn-success');
        select('.***').classList.add('btn-secondary');
    }

    if (urlView === '/' || urlView === '/abc/') {
        select('.main-nav').classList.add('active');
    } else if (urlView === '/bioage/') {
        select('.main-nav').classList.add('active');
    } else {
        navlinks.forEach(navlink => {
            let linkView = navlink.getAttribute('href');
            if (linkView === urlView) {
                navlink.classList.add('active');
                if (navlink.parentNode.parentNode.classList.contains('dropdown')){
                    navlink.parentNode.parentNode.firstElementChild.classList.add('active');
                }
            }
        });
    }

    /**
     * 개발자 페이지 가기
     */
    let allEvent = [];
    let cnt = 0;

    window.addEventListener('keydown', function (event) {
        let key = event.key;
        let devKey = ['ArrowUp', 'ArrowLeft', 'ArrowUp', 'ArrowUp', 'ArrowLeft', 'ArrowLeft', 'ArrowDown', 'ArrowDown', 'ArrowRight', 'ArrowRight']

        if (cnt >= 10) {
            if (JSON.stringify(allEvent) === JSON.stringify(devKey)) {
                window.location.replace("/dev/");
            }
        } else {
            allEvent.push(key)
            cnt++;
        }
    });

})()

function changeDisplayToNoneById(id) {
    let element = document.getElementById(id);
    element.style.display = "none";
}

function changeDisplayToShowById(id) {
    let element = document.getElementById(id);
    element.style.display = "";
}

function createCookie(name, value, expires, path = '/b***-****') {
    let cookie;

    if (!!$.cookie(name)) {
        cookie = $.cookie(name);
    } else {
        $.cookie(name, value, {expires: expires, path: path});
        cookie = $.cookie(name);
    }

    return cookie;
}

function checkInput(type, value, isString = false) {
    let regTest;
    let result;
    switch (type.toLowerCase()) {
        case 'number':
            regTest = /^[\d]*$/;
            result = regTest.test($.trim(value));
            break;
        case 'string':
            regTest = /^[가-힣a-zA-Z\s0-9\-\_\(\)\.\@\,\~]*$/;
            result = regTest.test($.trim(value));
            break;
        case 'name':
            regTest = /^[가-힣a-zA-Z]{0,20}$/;
            result = regTest.test($.trim(value));
            break;
        case 'email':
            regTest = /^[_\.0-9a-zA-Z\@]+$/;
            result = regTest.test($.trim(value));
            break;
        case 'english':
            regTest = /^[a-zA-Z0-9\_]+$/;
            result = regTest.test($.trim(value));
    }

    if (isString) {
        if (!result) {
            result = '';
        } else {
            result = $.trim(value);
        }
    }

    return result;
}

function checkPattern(type, value, isString = false) {
    let result;
    switch (type) {
        case 'MembersIdx':
        case 'Birth':
        case 'Age':
        case 'Phone':
        case 'GCRegNo':
        case 'MonthlyPremium':
        case 'DueDay':
        case 'InsuranceIdx':
        case 'ProductIdx':
            let numberTest = /\d+$/;
            result = numberTest.test($.trim(value));
            break;
        case 'Name':
        case 'ConsultantName':
        case 'InsuranceCompany':
        case 'CompanyName':
        case 'ProductName':
        case 'ServiceCompanyName':
            let str20Test = /^[가-힣a-zA-Z\(\)]{1,20}$/;
            result = str20Test.test($.trim(value));
            break;
        case 'ItemName':
            let str30Test = /^[가-힣|a-zA-Z]{1,30}$/;
            result = str30Test.test($.trim(value));
            break;
        case 'Gender':
            result = value === '남' || value === '여';
            break;
        case 'Email':
            let emailTest = /^[_\.0-9a-zA-Z\@]+$/;
            result = emailTest.test($.trim(value));
            break;
        case 'OverAge':
        case 'Inflow':
        case 'AuthTempt':
        case 'NHisTempt':
        case 'NHisData':
        case 'ReportData':
        case 'SentYN':
        case '***Data':
        case 'LabSentYN':
        case 'RegisterBizM':
        case 'TestBizM1':
        case 'TestBizM2':
        case 'TestBizM3':
        case '****BizM':
        case 'ConsultingBizM':
        case 'IsSend':
        case 'IsDownload':
        case 'ALL_AGRE_YN':
            result = value === 'Y' || value === 'N';
            break;
        case 'EmailStrict':
            let emailStrictTest = /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,3}$/;
            result = emailStrictTest.test($.trim(value));
            break;
        case 'BirthStrict':
            let birthStrictTest = /^\d{8}$/;
            result = birthStrictTest.test($.trim(value));
            break;
        case 'PhoneStrict':
            let phoneStrictTest = /^\d{11}$/;
            result = phoneStrictTest.test($.trim(value));
            break;
        case 'Consult1st':
        case 'Consult2nd':
        case 'Consult3rd':
            let statusTest = /^[a-zA-Z]$/;
            result = statusTest.test($.trim(value)) ||
                value === '계약체결' || value === '종결' || value === '결번' || value === '상담거절' ||
                value === '무응답' || value === '중복' || value === '부재' || value === '병력' ||
                value === '통화예약' || value === '상담완료' || value === '방문약속' || value === '계약대기' ||
                value === '상담' || value === '거절' || value === '보완' || value === '인수불가' ||
                value === '신청오류' || value === '기타'
            break;
        case 'CompanyCode':
        case 'ProductCode':
            let companyCodeTest = /^[_a-zA-Z]+$/;
            result = companyCodeTest.test($.trim(value));
            break;
        case 'ShortUrl':
            let shortUrlTest = /https:\/\/me2.do\/[a-zA-Z0-9]{8}$/;
            result = shortUrlTest.test($.trim(value));
            break;
    }

    if (isString) {
        if (!result) {
            result = '';
        } else {
            result = $.trim(value);
        }
    }

    return result;
}

function checkDate(minDate, maxDate) {
    if (minDate > maxDate) {
        alert('종료일이 시작일보다 빠를 수 없습니다.')
        return false;
    }
    return true;
}

function checkDateRange(table, minDate, maxDate) {
    let min = minDate;
    let max = maxDate;

    if (min > max) {
        alert('종료일이 시작일보다 빠를 수 없습니다.')
    } else {
        if (min !== '' && max !== '') {
            table.draw();
        } else {
            alert('검색할 날짜를 모두 선택하세요.');
        }
    }
}

function newDatetime() {
    let dateTime;
    let date;
    let time;

    let today = new Date();
    date = `${today.getFullYear()}${(today.getMonth() + 1)}${today.getDate()}`;
    time = `${today.getHours()}${today.getMinutes()}${today.getSeconds()}`;
    dateTime = `${date}${time}`;

    return dateTime;
}

function limitCnt(name, limitCnt, type = 'download') {

    let result;
    let limitCookie = createCookie(name, 0, 1, '/b***-****');
    if (type === 'download') {
        result = confirm('정말 다운로드 받으시겠습니까?');
    } else {
        result = confirm('정말 업로드 하시겠습니까?');
    }
    if (result) {
        if (limitCookie >= limitCnt) {
            if (type === 'download') {
                alert('하루 다운로드 횟수를 초과하였습니다. 개발팀에 문의하세요.');
            } else {
                alert('하루 업로드 횟수를 초과하였습니다. 개발팀에 문의하세요.');
            }
            return false;
        }
        limitCookie++;
        $.cookie(name, limitCookie, {expires: 1, path: '/b***-****'});
        return true;
    }
}

function validFileSize(file) {
    if (file.size > 500000) {
        return false;
    }
    return true;
}

function validFileTypes(file) {
    let fileTypes = ['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
    return fileTypes.includes(file.type);
}

function get****TestMembers() {
    let response;
    $.ajax({
        url: "/b***-*abc/app/get****TestMembers.php",
        type: "post",
        async: false,
        success: function (d) {
            response = d;
        }
    });

    return response;
}

let randerDataTable = function (targetUrl, columns, targetTable, excelName, orderNo) {
    let result = get****TestMembers();
    let ****TestMembers = JSON.parse(result);
    let ****Members = ****TestMembers.****Members;
    let testMembers = ****TestMembers.testMembers;
    let orderColumn = orderNo ?? 1;

    return $(targetTable).DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            'url': targetUrl,
            'type': 'POST',
            'data': function (d) {
                let data = JSON.stringify(
                    $.extend({}, d, {
                        'searchColumn': $(".searchColumn").val(),
                    })
                );
                return JSON.parse(data);
            }
        },
        'scrollX': true,
        'responsive': true,
        'search': {
            'return': true,
        },
        'dom': '<"div"<"col-sm"<"toolbar">>><"row"<"col-sm-3"l><"col-sm-9 d-flex' +
            ' justify-content-end"B>>rti<"d-flex' +
            ' justify-content-center"p>',
        'buttons': [
            {
                extend: 'copyHtml5',
                className: 'btn btn-secondary',
                exportOptions: {columns: 'th:not(:last-child)'}
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-secondary',
                title: `${excelName}${newDatetime()}`,
                action: function (e, dt, node, config) {
                    if (limitCnt('cookieExcelDLCnt', 25)) {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                    }
                },
            }
        ],
        'pagingType': 'full_numbers',
        'lengthMenu': [
            [50, 100, 200, 300, 500, 1000, 1500, 2000, 2500, 3000],
            [50, 100, 200, 300, 500, 1000, 1500, 2000, 2500, 3000]
        ],
        'fnCreatedRow':
            function (nRow, aData, iDataIndex) {
                $(nRow).attr('idx', aData['UsersIdx']);
            },
        'columns': columns,
        'order': [
            [orderColumn, 'desc']
        ],
        'createdRow': function (row, data) {
            $('div.toolbar').html("<p style=\"color:red\"><strong>* 테스트 또는 xxxx 회원일 경우, 리스트에서 연보라색으로 표기됨</strong></p>");
            if (data['UsersIdx']) {
                if (****Members.includes(data['UsersIdx'].toString()) || testMembers.includes(data['UsersIdx'].toString())) {
                    $(row).css('background-color', '#002bff26');
                }
            }
        }
    });
}

let consultStatusDef = function (val, def = true) {
    let codeDef = "";
    let code = "";
    switch (val) {
        case "A":
        case "계약체결":
            code = "A";
            codeDef = "계약체결";
            break;
        case "B":
        case "종결":
            code = "B";
            codeDef = "종결";
            break;
        case "C":
        case "결번":
            code = "C";
            codeDef = "결번";
            break;
        case "D":
        case "상담거절":
            code = "D";
            codeDef = "상담거절";
            break;
        case "E":
        case "무응답":
            code = "E";
            codeDef = "무응답";
            break;
        case "F":
        case "중복":
            code = "F";
            codeDef = "중복";
            break;
        case "G":
        case "부재":
            code = "G";
            codeDef = "부재";
            break;
        case "H":
        case "병력":
            code = "H";
            codeDef = "병력";
            break;
        case "I":
        case "통화예약":
            code = "I";
            codeDef = "통화예약";
            break;
        case "J":
        case "상담완료":
            code = "J";
            codeDef = "상담완료";
            break;
        case "K":
        case "방문약속":
            code = "K";
            codeDef = "방문약속";
            break;
        case "L":
        case "계약대기":
            code = "L";
            codeDef = "계약대기";
            break;
        case "M":
        case "상담":
            code = "M";
            codeDef = "상담";
            break;
        case "N":
        case "거절":
            code = "N";
            codeDef = "거절";
            break;
        case "O":
        case "보완":
            code = "O";
            codeDef = "보완";
            break;
        case "P":
        case "인수불가":
            code = "P";
            codeDef = "인수불가";
            break;
        case "Q":
        case "신청오류":
            code = "Q";
            codeDef = "신청오류";
            break;
        case "Z":
        case "기타":
            code = "Z";
            codeDef = "기타";
            break;
        default:
            codeDef = "-";
            code = "-";
            break;
    }

    if (def) {
        return codeDef;
    } else {
        return code;
    }
}

let downloadSpreadSheet = function (data, location) {

    let form = $(document.createElement('form'));
    $(form).attr("action", location);
    $(form).attr("method", "POST");
    $(form).attr("target", "_self");
    $(form).css("display", "none");

    let input1 = $("<input>")
        .attr("type", "text")
        .attr("name", "data")
        .val(data);
    $(form).append($(input1));

    form.appendTo(document.body);
    $(form).submit();
    $(form).remove();
}