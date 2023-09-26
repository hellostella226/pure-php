$(document).ready(function () {
    let dateTime = newDatetime();

    let table = $('#smsTable').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            'url': '/b***-*abc/app/bioage/table/smsTable.php',
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
                    columns: 'th:not(:last-child, :first-child)'
                }
            },
            {
                extend: 'excelHtml5',
                title: `알림톡정보_${dateTime}`,
                exportOptions: {
                    columns: 'th:not(:last-child, :first-child)'
                },
                action: function (e, dt, node, config){
                    if (limitCnt('cookieExcelDLCnt', 25)) {
                        $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);
                    }
                }
            }
        ],
        'pagingType': 'full_numbers',
        'lengthMenu': [
            [15, 50, 100, 200, 300, 500, 1000],
            [15, 50, 100, 200, 300, 500, 1000]
        ],
        'fnCreatedRow': function (nRow, aData, iDataIndex) {
            $(nRow).attr('id', aData['UsersIdx']);
        },
        'columns': [
            {'data': 'UsersIdx', 'orderable': false, 'searchable': false, 'className': 'dt-body-center',
                'render': function (data, type, full, meta) {
                    return '<input type="checkbox" class="form-check-input" name="idx" value="' + $('<div/>').text(data).html() + '">';
                }},
            {'data': 'MembersIdx'},
            {'data': 'Name'},
            {'data': 'GCRegDate'},
            {'data': 'GCRegNo'},
            {'data': 'ShortUrl'},
            {'data': 'SentYN'},
            {'data': '***Data'}
        ],
        'order': [[1, 'asc']],
    });

    $.fn.dataTable.ext.errMode = 'throw';

    $('.btn-search').on('click', function () {
        let searchList = [];
        $('.searchCondition').each(function () {
            let searchContent = $(this)[0].id.split('_');
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

    $('.selectall').on('click', function () {
        let rows = table.rows({'search': 'applied'}).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
    });

    $('.createUrlBtn').on('click', function () {
        let idxTemp = $('input[name=idx]:checked');
        let idx = [];

        if (idxTemp.length === 0) {
            alert('Url 생성대상 데이터가 선택되지 않았습니다.');
            return false;
        }
        if (!confirm(`${idxTemp.length}건을 선택하셨습니다. Url 생성하시겠습니까?`)) {
            return false;
        }
        $('input[name=idx]:checked').each(function () {
            idx.push($(this).val());
        });
        createShortUrl(idx);
    });
});

function showDiv(select) {
    let showItem = select.value;
    let searchItem = document.getElementsByClassName('searchItem');
    for (let i = 0; i < searchItem.length; i++) {
        searchItem[i].style.display = "none";
    }
    if (showItem !== '') {
        switch (showItem) {
            case 'MembersIdx':
            case 'Name':
            case 'GCRegNo':
            case 'ShortUrl':
                document.getElementById('SearchItemBar').style.display = "block";
                break;
            case 'GCRegDate':
                document.getElementById('DateItemBar').style.display = "block";
                break;
            case 'SentYN':
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
        case 'ShortUrl':
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
        case 'SentYN':
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
    let newConditionId = (typeof item === 'undefined') ? `${columnText}_${minDate}_${maxDate}` : `${columnText}_${itemText}`;
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

function createShortUrl(idx){
    $.ajax({
        url: "/b***-*abc/app/update/createShortUrl.php",
        data: {
            UsersIdx: idx,
            isV2: '2'
        },
        type: "post",
        success: function (data) {
            let response = JSON.parse(data);
            $('#smsDiseaseTable').DataTable().draw();
            alert(response.msg);
        }
    });
}

$(document).on('click', '.btnUrlCreate', function () {
    let idx = $(this).data('id')
    if (!confirm('정말 생성하시겠습니까?')) {
        return false;
    }
    createShortUrl(idx);
});


$(document).on('click', '.btnSmsModal', function (event) {
    let idx = $(this).data('id');

    $.ajax({
        url: "/b***-*abc/app/bioage/modal/smsViewModal.php",
        data: {
            UsersIdx: idx
        },
        type: "post",
        success: function (data) {
            let response = JSON.parse(data);
            let result = response.data;

            if(response.code !== 201){
                alert(response.msg);
                return false;
            }
            $('#MembersId').val(result.MembersIdx);
            $('#sendCnt').text(result.SendCnt);
            $('#sentDatetime').text(result.SentDatetime);

            $('#smsModalTitle').text(`회원정보 - MembersIdx: ${result.MembersIdx}`);
            $('#smsModal').modal('show');
        }
    });
});

$(document).on('click', '.closeModal', function () {
    $('.modal').modal('hide');
});

$('.sendbtn').on('click', function () {
    let idxTemp = $('input[name=idx]:checked');
    let UsersIdx = [];

    if (idxTemp.length === 0) {
        alert('전송을 진행할 데이터가 존재하지 않습니다.');
        return false;
    }

    if (!confirm(`${idxTemp.length}건을 선택하셨습니다. 발송을 진행하시겠습니까?`)) {
        return false;
    }

    $('input[name=idx]:checked').each(function () {
        UsersIdx.push($(this).val());
    });

    $.ajax({
        url: '/b***-*abc/app/sendBizMessage.php',
        type: 'POST',
        data: {
            UsersIdx: UsersIdx,
            processStep: 10,
            btnName: '<?=$sendtalkBtn?>'
        },
        dataType: "json",
        success: function (result) {

            if (result.code == 200) {
                let msg = `성공 : ${result.data.success}건 / 실패 : ${result.data.fail}건 발송완료 되었습니다.`;

                if(result.data.nonTargetList != undefined){
                    msg += `\n` + `url 누락 명단: ${result.data.nonTargetList}`;
                }
                alert(msg);

            } else {
                alert(result.msg);
            }
        }, error: function () {
            alert('발송에 실패하였습니다. 개발팀에 문의 바랍니다.');
        }
    });
});