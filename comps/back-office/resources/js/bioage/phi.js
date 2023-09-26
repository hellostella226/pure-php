$(document).ready(function () {
    let dateTime = newDatetime();

    let table = $('#***Table').DataTable({
            'processing': true,
            'serverSide': true,
            'ajax': {
                'url': '/b***-*abc/app/bioage/table/***Table.php',
                'type': 'POST',
            },
            'scrollX': true,
            'responsive': true,
            'search': {
                'return': true,
            },
            'dom': '<"row"<"col-sm-3"l><"col-sm-9 d-flex justify-content-end"B>>rti<"d-flex justify-content-center"p>',
            'buttons': [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: 'th:not(:last-child)'
                    }
                },
                {
                    extend: 'excelHtml5',
                    title: `테스트정보_${dateTime}`,
                    exportOptions: {
                        columns: 'th:not(:last-child)'
                    },
                    action: function (e, dt, node, config){
                        if (limitCnt('cookieExcelDLCnt', 25)) {
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                        }
                    }
                }
            ],
            'pagingType': 'full_numbers',
            'lengthMenu':
                [
                    [15, 50, 100, 200, 300, 500, 1000],
                    [15, 50, 100, 200, 300, 500, 1000]
                ],
            'fnCreatedRow':
                function (nRow, aData, iDataIndex) {
                    $(nRow).attr('id', aData['UsersIdx']);
                },
            'columns': [
                {'data': 'MembersIdx'},
                {'data': 'Name'},
                {'data': 'GCRegDate'},
                {'data': 'GCRegNo'},
                {'data': 'ALL_AGRE_YN'},
                {'data': 'NHisData'},
                {'data': '***Data'},
                {'data': 'Details', 'orderable': false, 'searchable': false}
            ],
            'order':
                [[0, 'asc']],
        })
    ;

    $('.btn-search').on('click', function () {
        let searchList = [];
        $('.searchCondition').each(function () {
            let searchContent = $(this)[0].id.split('/');
            searchList.push(searchContent);
        });

        if(searchList.length === 0){
            if(!confirm('선택하신 검색조건이 없습니다. 계속 진행하시겠습니까?')){
                return false;
            }
        }
        let searchValue = JSON.stringify(searchList);
        table.search(searchValue).draw();
    });

    $.fn.dataTable.ext.errMode = 'throw';
});

function showDiv(select) {
    const showItem = select.value;
    const searchItem = document.getElementsByClassName('searchItem');
    for (let i = 0; i < searchItem.length; i++) {
        searchItem[i].style.display = "none";
    }
    if (showItem !== '') {
        switch (showItem) {
            case 'MembersIdx':
            case 'Name':
            case 'GCRegNo':
                document.getElementById('SearchItemBar').style.display = "block";
                break;
            case 'GCRegDate':
                document.getElementById('DateItemBar').style.display = "block";
                break;
            case 'ALL_AGRE_YN':
            case 'NHisData':
            case '***Data':
                document.getElementById('YnItemBar').style.display = "block";
                break;
        }
    }
}

function addSearchBadge() {
    $('.searchBar').css('display', 'block');

    let column = $('.searchColumn option:selected').val();
    let columnText = $('.searchColumn option:selected').text();

    if (column === 'none') {
        alert('검색할 컬럼을 선택해주십시오.');
        return false;
    }

    let item;
    let itemText;
    let minDate;
    let maxDate;
    switch (column) {
        case 'MembersIdx':
        case 'Name':
        case 'GCRegNo':
            item = $('#searchValue').val();
            itemText = $('#searchValue').val();

            if (!checkPattern(column, itemText)) {
                alert('올바른 형식이 아닙니다. 다시 입력해주십시오.');
                return false;
            }
            break;
        case 'GCRegDate':
            minDate = $('#minDate').val();
            maxDate = $('#maxDate').val();
            if (!checkDate(minDate, maxDate)) {
                return false;
            }
            break;
        case 'ALL_AGRE_YN':
        case 'NHisData':
        case '***Data':
            item = $('#YnItemBar option:selected').val();
            itemText = $('#YnItemBar option:selected').text();

            if (!checkPattern(column, itemText)) {
                alert('올바른 형식이 아닙니다. 다시 입력해주십시오.');
                return false;
            }
            break;
    }

    let newCondition = (typeof item === 'undefined') ? `${columnText}:${minDate}~${maxDate}` : `${columnText}=${itemText}`;
    let newConditionId = (typeof item === 'undefined') ? `${columnText}/${minDate}/${maxDate}` : `${columnText}/${itemText}`;
    let existCondition = [];

    let badge = `<span class="badge bg-primary searchCondition" id="${newConditionId}">${newCondition}</span>`;

    if ((!item || item === 'none') && (!minDate || !maxDate)) {
        alert('검색 값을 선택 또는 입력 해주시길 바랍니다.');
        return;
    }

    $('.searchCondition').each(function () {
        existCondition.push($(this).text().split('=')[0]);
    });

    let isNew = existCondition.indexOf(columnText);

    if (isNew === -1) {
        $('#searchList').append(badge);
    } else {
        alert('항목당 한 개의 검색값만 선택 가능합니다.')
    }
}

$('.addSearch').on('click', function () {
    addSearchBadge();
});
$('.searchBox').keyup(function (e) {
    if (e.key === "Enter") {
        addSearchBadge();
    }
});

$('.removeSearch').on('click', function () {
    const searchListDiv = document.getElementById('searchList');
    const lastElement = searchListDiv.lastElementChild;
    const firstElement = searchListDiv.firstElementChild;

    if (lastElement !== firstElement) {
        searchListDiv.removeChild(lastElement);
    } else {
        $('.searchBar').css('display', 'none');
    }
});

$(document).on('click', '.btn***Modal', function () {
    const UsersIdx = $(this).data('id');

    $.ajax({
        url: "/b***-*abc/app/bioage/modal/***ViewModal.php",
        data: {
            UsersIdx: UsersIdx
        },
        type: "post",
        success: function (d) {
            let response = JSON.parse(d);
            let result = response.data;

            if(response.code !== 201) {
                alert(response.msg);
                return false;
            }

            $('#MembersId').val(result.MembersIdx);
            (result.RecentCheckupYear) ? $('#viewRecentYear').text(`${result.RecentCheckupYear}년`) : $('#viewRecentYear').text(result.RecentCheckupYear);
            $('#viewReportDate').text(result.***ReportDate);

            $('#***ModalTitle').text(`회원정보 - MembersIdx: ${result.MembersIdx}`);
            $('#***Modal').modal('show');
        }
    });
});

$(document).on('click', '.closeModal', function () {
    $('#***Modal').modal('hide');
});

$(document).on('click', '.btnDownload', function (event) {
    let UsersIdx = $(this).data('id');
    let orderIdx = $(this).data('orderidx');

    if(!!$.cookie(`cookieDownloadCnt${UsersIdx}`)){
        let dlcnt = $.cookie(`cookieDownloadCnt${UsersIdx}`);
    } else {
        $.cookie(`cookieDownloadCnt${UsersIdx}`, 0, {expires: 1, path: '/b***-****'});
        let dlcnt = $.cookie(`cookieDownloadCnt${UsersIdx}`);
    }

    let result = confirm('정말 다운로드 받으시겠습니까?');

    if(result){

        if(dlcnt >= 5){
            alert('해당 회원에 대한 하루 다운로드 횟수를 초과하였습니다. 개발팀에 문의하세요.');
            return false;
        } else {
            $.ajax({
                url: "/b***-*abc/app/getUuid.php",
                data: {
                    UsersIdx: UsersIdx,
                    orderIdx: orderIdx
                },
                type: "post",
                success: function (data) {
                    let response = JSON.parse(data);
                    getPdf(response.data.uuid, response.data.filename);
                    dlcnt++;
                    $.cookie(`cookieDownloadCnt${UsersIdx}`, dlcnt, {expires: 1, path: '/b***-****'});
                }
            });
        }
    } else {
        return false;
    }

    function getPdf(uuid, filename){
        let count = $('form[name=newForm]').length;

        // if form created update uuid only
        if(count>0){
            document.newForm.uuid.value = uuid;
            document.newForm.filename.value = filename;
            $('form[name=newForm]').submit();
        } else {
            // create element (form)
            let newForm = document.createElement('form');
            // set attribute (form)
            newForm.name = 'newForm';
            newForm.method = 'post';
            newForm.action = '/basic/process/pdf.php';
            newForm.target = '_self';

            // create element (input)
            let input1 = document.createElement('input');
            let input2 = document.createElement('input');
            // set attribute (input)
            input1.setAttribute("type", "hidden");
            input1.setAttribute("name", "uuid");
            input1.setAttribute("value", uuid);

            input2.setAttribute("type", "hidden");
            input2.setAttribute("name", "filename");
            input2.setAttribute("value", filename);
            // append input (to form)
            newForm.appendChild(input1);
            newForm.appendChild(input2);
            // append form (to body)
            document.body.appendChild(newForm);
            // submit form
            newForm.submit();
            //$("form[name='newForm']").remove();
            setTimeout(function () {
                $('.download').show();
            }, 30000);
        }
    }
});