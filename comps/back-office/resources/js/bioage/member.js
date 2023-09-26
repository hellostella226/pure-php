$(document).ready(function () {
    let dateTime = newDatetime();

    let table = $('#MembersTable').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            'url': '/b***-*abc/app/bioage/table/MembersTable.php',
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
                title: `회원정보_${dateTime}`,
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
        'lengthMenu': [
            [15, 50, 100, 200, 300, 500, 1000],
            [15, 50, 100, 200, 300, 500, 1000]
        ],
        'fnCreatedRow': function (nRow, aData, iDataIndex) {
            $(nRow).attr('id', aData['MembersIdx']);
        },
        'columns': [
            {'data': 'MembersIdx'},
            {'data': 'Name'},
            {'data': 'Birth'},
            {'data': 'Gender'},
            {'data': 'Phone'},
            {'data': 'Email'},
            {'data': 'RegDatetime'},
            {'data': 'Details', 'orderable': false, 'searchable': false}
        ],
        'order': [[0, 'asc']]
    });

    $.fn.dataTable.ext.errMode = 'throw';

    $('.btn-search').on('click', function () {
        let searchList = [];
        $('.searchCondition').each(function () {
            let searchContent = $(this)[0].id.split('_');
            searchList.push(searchContent);
        });

        if (searchList.length === 0) {
            if (!confirm('선택하신 검색조건이 없습니다. 계속 진행하시겠습니까?')) {
                return false;
            }
        }
        let searchValue = JSON.stringify(searchList);
        table.search(searchValue).draw();
    });
});

function showDiv(select) {
    let showItem = select.value;
    const searchItem = document.getElementsByClassName('searchItem');
    for (let i = 0; i < searchItem.length; i++) {
        searchItem[i].style.display = "none";
    }
    if (showItem !== '') {
        switch (showItem) {
            case 'MembersIdx':
            case 'Name':
            case 'Birth':
            case 'Age':
            case 'Phone':
            case 'Email':
                document.getElementById('SearchItemBar').style.display = "block";
                break;
            case 'RegDatetime':
                document.getElementById('DateItemBar').style.display = "block";
                break;
            case 'Gender':
                document.getElementById('GenderItemBar').style.display = "block";
                break;
            case 'OverAge':
                document.getElementById('OverAgeItemBar').style.display = "block";
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
        case 'Birth':
        case 'Age':
        case 'Phone':
        case 'Email':
            item = $('#searchValue').val();
            itemText = $('#searchValue').val();

            if (!checkPattern(column, itemText)) {
                alert('올바른 형식이 아닙니다. 다시 입력해주십시오.');
                return false;
            }
            break;
        case 'RegDatetime':
            minDate = $('#minDate').val();
            maxDate = $('#maxDate').val();
            if (!checkDate(minDate, maxDate)) {
                return false;
            }
            break;
        case 'Gender':
        case 'OverAge':
            item = $(`#${column}ItemBar option:selected`).val();
            itemText = $(`#${column}ItemBar option:selected`).text();
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
    let searchListDiv = document.getElementById('searchList');
    let lastElement = searchListDiv.lastElementChild;
    let firstElement = searchListDiv.firstElementChild;

    if (lastElement !== firstElement) {
        searchListDiv.removeChild(lastElement);
    } else {
        $('.searchBar').css('display', 'none');
    }
});

$(document).on('click', '.closeModal', function () {
    $('.modal').modal('hide');
});

$(document).on('click', '.btnMembersModal', function () {
    let MembersIdx = $(this).data('id');

    $.ajax({
        url: "/b***-*abc/app/bioage/modal/MembersViewModal.php",
        data: {
            MembersIdx: MembersIdx
        },
        type: "post",
        success: function (d) {
            let response = JSON.parse(d);
            let result = response.data;

            if (response.code !== 201) {
                alert(response.msg);
                return false;
            }
            $('#MembersId').val(result.MembersIdx);
            $('#inputName').val(result.Name);
            $('#inputBirth').val(result.Birth);
            $('#inputGender').val(result.Gender);
            $('#inputPhone').val(result.Phone);
            $('#inputEmail').val(result.Email);

            $('#MembersModalTitle').text(`회원정보 수정 - MembersIdx: ${MembersIdx}`);
            $('#MembersEditModal').modal('show');
        }
    });
});

$('.confirmModal').on('click', function () {
    alert('아직 개발 중인 기능입니다. 개발팀에 문의주시길 바랍니다.');
    return false;

    if (!confirm('정말 수정하시겠습니까?')) {
        return false;
    }

    let MembersIdx = checkPattern('MembersIdx', $('#MembersId').val(), true);
    let name = checkPattern('Name', $('#inputName').val(), true);
    let birth = checkPattern('BirthStrict', $('#inputBirth').val(), true);
    let gender = checkPattern('Gender', $('#inputGender').val(), true);
    let phone = checkPattern('PhoneStrict', $('#inputPhone').val(), true);
    let email = checkPattern('EmailStrict', $('#inputEmail').val(), true);

    if (MembersIdx === '' || name === '' || birth === '' || gender === '' || phone === '') {
        alert('형식이 올바르지 않습니다. 다시 입력해주세요.');
        return false;
    }

    $.ajax({
        url: "/b***-*abc/app/update/updateMembers.php",
        data: {
            MembersIdx: MembersIdx,
            name: name,
            birth: birth,
            gender: gender,
            phone: phone,
            email: email
        },
        type: "post",
        success: function (d) {
            let response = JSON.parse(d);
            let msg = response.msg;
            if (response.code !== 203) {
                alert(msg);
                return false;
            }
            let result = JSON.parse(response.data);
            msg += `: 수정완료 (${result.MembersIdx})`;
            if(result.IsSent.length + result.IsFail.length > 1){
                msg += `\n성공: ${result.IsSent.join(', ')}`;
                msg += `\n실패: ${result.IsFail.join(', ')}`;
            }
            alert(msg);
            $('#MembersTable').DataTable().draw();
            $('#MembersEditModal').modal('hide');
        }, failure: function () {
            alert('Error: 개발팀에게 문의주시길 바랍니다.')
        }
    });
});