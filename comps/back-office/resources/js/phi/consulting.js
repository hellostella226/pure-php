let mainScript = {
    _purpose: 'consulting',
    _methodType: 'get',
    _search: {
        'keyword': '',
        'value': '',
        'entry': 50, //출력 리밋
        'page': 1, //현재 페이지
    },
    init: function () {
        this._purpose = document.location.href.split('/')[4];
        this.request();
    },
    dataset: function (data = []) {
        var formData = new FormData;
        formData.append('purpose', this._purpose);
        if (data) {
            for (let key in data) {
                formData.append(key, data[key]);
            }
        }
        return formData;
    },
    request: function (f = this._search) {
        let data = null;
        let url = document.location.href;
        if (this._methodType === 'get') {
            data = [...this.dataset(f).entries()];
            data = data
                .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
                .join('&');

            const operator = url.indexOf('?') > 0 ? '&' : '?';
            url += operator + data;
        } else {
            data = this.dataset(f);
        }
        sendRequest(this._methodType, url, data, '', '', this.callback);
    },
    callback: function (response) {
        if (response) {
            if (response.code === '20200') {
                if (mainScript._methodType === 'POST') {
                    alert(`등록되었습니다. 성공: ${response.data.success}건 / 실패: ${response.data.failure}`);
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'consulting' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    case 'downloadConsultingResult':
                        break;
                    default : //searchConsulting, uploadConsulting
                        mainScript.setModal(response);
                        break;
                }
                for (let key in data) {
                    if (key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }
            }
        }
    },
    paging: function (num) {
        this._search.page = num;
        this.request();
    },
    setForm: function (key, data) {
        const type = key.split('::')[0];
        const id = key.split('::')[1];
        // 셀렉트박스 세팅
        if (type === 'select') {
            const target = document.getElementById(id);
            for (let key in data) {
                const option = document.createElement('option');
                option.text = data[key]['text'];
                option.value = data[key]['value'];
                target.appendChild(option);
            }
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'consulting' :
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if (mainScript._purpose === 'searchConsulting') {
                    selector = `#${mainScript._purpose}`;

                    document.getElementById('***TransferDatetime').innerText = data.***TransferDatetime;
                    document.getElementById('consultantFixDate').innerText = data.consultantFixDate;
                    document.getElementById('consultDate1').innerText = data.consultDate1;
                    document.getElementById('consultDate2').innerText = data.consultDate2;
                    document.getElementById('consultDate3').innerText = data.consultDate3;
                    document.getElementById('requestMemo').innerText = data.requestMemo ?? '';
                }
                const modalEl = document.querySelector(selector);
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();

            } else {
                alert('error,,');
            }
        } else {
            alert('main error,,');
        }
    },
    rendering: function (data, num) {
        deleteElement('adminTable');
        const tbl = document.getElementById('adminTable');
        if (!data) {
            return;
        }
        Object.keys(data).reverse().forEach(function (key) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);

            let statusCode = data[key]['StatusCode'] ? data[key]['StatusCode'].split('') : [];
            let statusCodeDef = {};
            let i = 0;
            statusCode.forEach(function (c) {
                switch (c) {
                    case 'A':
                        statusCodeDef[i] = '계약체결';
                        break;
                    case 'B':
                        statusCodeDef[i] = '종결';
                        break;
                    case 'C':
                        statusCodeDef[i] = '결번';
                        break;
                    case 'D':
                        statusCodeDef[i] = '상담거절';
                        break;
                    case 'E':
                        statusCodeDef[i] = '무응답';
                        break;
                    case 'F':
                        statusCodeDef[i] = '중복';
                        break;
                    case 'G':
                        statusCodeDef[i] = '부재';
                        break;
                    case 'H':
                        statusCodeDef[i] = '병력';
                        break;
                    case 'I':
                        statusCodeDef[i] = '통화예약';
                        break;
                    case 'J':
                        statusCodeDef[i] = '상담완료';
                        break;
                    case 'K':
                        statusCodeDef[i] = '방문약속';
                        break;
                    case 'L':
                        statusCodeDef[i] = '계약대기';
                        break;
                    case 'M':
                        statusCodeDef[i] = '상담';
                        break;
                    case 'N':
                        statusCodeDef[i] = '거절';
                        break;
                    case 'O':
                        statusCodeDef[i] = '보완';
                        break;
                    case 'P':
                        statusCodeDef[i] = '인수불가';
                        break;
                    case 'Q':
                        statusCodeDef[i] = '신청오류';
                        break;
                    case 'Z':
                        statusCodeDef[i] = '기타';
                        break;
                    default:
                        statusCodeDef[i] = '';
                        break;
                }
                i++;
            })
            let values = {
                'regDatetime': data[key]['RegDatetime'],
                'UsersIdx': data[key]['UsersIdx'],
                'name': data[key]['Name'],
                'serviceCompanyName': data[key]['ServiceCompanyName'],
                'consultantName': data[key]['ConsultantName'] ?? '',
                'isSent': data[key]['***TransferDatetime'] ? 'Y' : 'N',
                'statusCode1': statusCodeDef[0] ?? '',
                'statusCode2': statusCodeDef[1] ?? '',
                'statusCode3': statusCodeDef[2] ?? '',
                'parentItemName': data[key]['ParentItemName'] ?? '',
                'itemName': data[key]['ItemName'] ?? '',
                'monthlyPremium': data[key]['MonthlyPremium'] ?? '',
                'dueDay': data[key]['DueDay'] ?? '',
                'contractDate': data[key]['ContractDate'] ?? '',
                'options': key,
            };
            for (let k in values) {
                let cell = document.createElement("td");
                if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('더보기');
                    cell2.className += 'btn btn-sm btn-info';
                    cell2.name = 'data-view';
                    cell2.setAttribute('data-name', data[key]['Name'] ?? '');
                    cell2.setAttribute('data-company-idx', data[key]['ServiceControlIdx'] ?? '');
                    cell2.setAttribute('data-phone', data[key]['Phone'] ?? '');
                    cell2.setAttribute('data-transfer-date', data[key]['***TransferDatetime'] ?? '');
                    cell2.setAttribute('data-consultant-date', data[key]['ConsultantFixDate'] ?? '');
                    cell2.setAttribute('data-consult1-date', data[key]['ConsultDate1'] ?? '');
                    cell2.setAttribute('data-consult2-date', data[key]['ConsultDate2'] ?? '');
                    cell2.setAttribute('data-consult3-date', data[key]['ConsultDate3'] ?? '');
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else {
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        });

        //수정 버튼 클릭시
        document.getElementsByName('data-view').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                let data = {};
                data.serviceCompanyIdx = btn.getAttribute('data-company-idx');
                data.name = btn.getAttribute('data-name');
                data.phone = btn.getAttribute('data-phone');
                data.***TransferDatetime = btn.getAttribute('data-transfer-date');
                data.consultantFixDate = btn.getAttribute('data-consultant-date');
                data.consultDate1 = btn.getAttribute('data-consult1-date');
                data.consultDate2 = btn.getAttribute('data-consult2-date');
                data.consultDate3 = btn.getAttribute('data-consult3-date');

                mainScript._purpose = 'searchConsulting';
                mainScript.request(data);
            });
        });

        // 엑셀 다운로드버튼 클릭시
        let downBtn = document.getElementsByName('data-download');
        if (downBtn) {
            downBtn.forEach(function (btn) {
                if (!btn.getAttribute("data-click")) {
                    btn.setAttribute("data-click", true);
                    btn.addEventListener('click', function () {
                        mainScript._methodType = 'POST';
                        let data = {
                            'purpose': 'consultingDown',
                        };
                        adminScript.locate(data);
                    });
                }
            });
        }

        // 엑셀 등록버튼 클릭시
        let excelBtn = document.querySelectorAll('.excel-btn');
        if (excelBtn) {
            excelBtn.forEach(function (btn) {
                if (!btn.getAttribute("data-click")) {
                    btn.setAttribute("data-click", true);
                    btn.addEventListener('click', function () {
                        let data = {};
                        const selector = btn.getAttribute('data-target');
                        const form = document.querySelector('.' + selector);
                        form.querySelectorAll('input,select').forEach(function (el, i) {
                            if (el.value) {
                                if (el.type === 'checkbox') {
                                    if (el.checked) {
                                        data[el.name] = el.value;
                                    }
                                } else if (el.type === 'file') {
                                    data[el.name] = el.files[0];
                                } else {
                                    data[el.name] = el.value;
                                }
                            }
                        });
                        data['enctype'] = 'multipart/form-data';
                        mainScript._methodType = 'POST';
                        mainScript._purpose = selector;
                        mainScript.request(data);
                    });
                }
            });
        }

        // 상담상태치환규칙 popover
        const popoverEl = document.querySelector('.consultCode');
        new bootstrap.Popover(popoverEl, {
                html: true,
                sanitize: false,
                content: document.getElementById('popoverContent').innerHTML,
                trigger: 'click'
            });
        const popover = bootstrap.Popover.getOrCreateInstance(popoverEl);

        tbl.setAttribute("border", "2");
    },
};