let mainScript = {
    _purpose: 'insureib', //controller 요청 목적
    _methodType: 'get', //method 타입
    _search: { //pagination 관련 값
        'keyword': '',
        'value': '',
        'entry': 50, //출력 리밋
        'page': 1, //현재 페이지
        'column': '',
        'sort': '',
    },
    init: function () {
        const sub = document.location.href.split('sub=')[1];
        this._purpose = document.location.href.split('sub=')[1] ? sub : document.location.href.split('/')[4];
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
                    alert('등록되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'insureib' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.rendering(data.data, data.pagination.start);
                        adminScript.pagination(data.pagination);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    case 'findAllocateUser' :
                        let f = JSON.stringify(data.data);
                        adminScript.downloadSpreadSheet(f, "/b***-*abc/app/abc/spreadsheet/createDbAllocation.php");
                        break;
                    case 'ibAllocationData' :
                        let d = JSON.stringify(data.data);
                        adminScript.downloadSpreadSheet(d, "/b***-*abc/app/abc/spreadsheet/createDbAllocation.php");
                        break;
                    case 'allDown' :
                        break;
                    case 'searchIbUserData' :
                        mainScript.setModal(response);
                        break;
                    default :
                        mainScript.setModal(response);
                        break;
                }
                for (let key in data) {
                    if (key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }

            } else {
                alert(response.message);
                location.reload();
                return false;
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
            if (!target.getAttribute("data-setting")) {
                target.setAttribute("data-setting", true);
                for (let key in data) {
                    const option = document.createElement('option');
                    option.text = data[key]['text'];
                    option.value = data[key]['value'];
                    target.appendChild(option);
                }
            }
        } else if (type === 'text') {
            for (let key in data) {
                const target = document.getElementById(key);
                target.innerHTML = data[key];
            }
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'insureib' :
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                const clientRegDate = data.ClientRegDate;
                let sendDate = data.TransferRegDate;
                let appointmentDate = data.AppointmentDate;
                let days = ['', '평일', '', '', '', '', '주말', '', '항상가능'];
                let appointmentDay = '';
                if (data.AppointmentDay) {
                    appointmentDay = days[parseInt(data.AppointmentDay)];
                }
                const appointmentHour = data.AppointmentHour === '18' ?
                    data.AppointmentHour + '시이후' : (data.AppointmentHour ? data.AppointmentHour + '시' : '');

                document.getElementById('clientRegDate').textContent = clientRegDate;
                document.getElementById('sendDate').textContent = sendDate;
                document.getElementById('appointmentDate').textContent = appointmentDate;
                document.getElementById('appointmentDay').textContent = appointmentDay;
                document.getElementById('appointmentHour').textContent = appointmentHour;
                selector = '.userInsure';
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
        Object.keys(data).forEach(function (key) {
            num++;
            const row = document.createElement("tr");
            const _indexCell = document.createElement("td");
            const checkBox = document.createElement('input');
            checkBox.className += 'form-check-input';
            checkBox.name = 'orderIdx[]'
            checkBox.type = 'checkbox';
            checkBox.value = data[key]['OrderIdx'];
            checkBox.setAttribute('data-is-allocated', data[key]['ServiceCompanyName']);
            _indexCell.appendChild(checkBox);
            row.appendChild(_indexCell);

            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);
            let transferMethodCode = data[key]['TransferMethodCode'] ?? '';
            let transferMethodName = '';
            if (transferMethodCode) {
                if (transferMethodCode === '1') {
                    // API 전송 : 다운이력: 이력 불문 - 표기, 전송상태: 상태에 따라 Y,N
                    transferMethodName = 'API';
                } else if (transferMethodCode === '2') {
                    // 수동 전송 : 다운이력: 다운로드 유무에 따라 Y/N 표기, 전송상태 : 이력 불문 -
                    transferMethodName = '수동';
                }
            }
            let consultantType = '미접속';
            if (data[key]['ConsultantType']) {
                if (data[key]['ConsultantType'] === 'R') {
                    consultantType = '설명듣기';
                } else if (data[key]['ConsultantType'] === 'L') {
                    consultantType = '나중에';
                } else if (data[key]['ConsultantType'] === 'N') {
                    consultantType = '미응답';
                }
            }

            let state = data[key]['State'] ?? '';
            let city = data[key]['City'] ?? '';
            let address = `${state} ${city}`.trim();
            let values = {
                'calcDate': data[key]['CalcDate'],
                'regDatetime': data[key]['RegDatetime'],
                'UsersIdx': data[key]['UsersIdx'],
                'name': data[key]['Name'],
                'address': address,
                'clientCustomerName': data[key]['ClientCustomerName'],
                'serviceCompanyName': data[key]['ServiceCompanyName'] ?? '',
                'transferMethodName': transferMethodName,
                'isPost': data[key]['IsPost'] ? '전송' : '미전송',
                'consultantType': consultantType,
                'cwCnt' : data[key]['CWCnt'],
                'dhCnt' : data[key]['DHCnt'],
                'ibDown': '',
                'uuid': '',
                'options': '',
            };
            for (let k in values) {
                let cell = document.createElement("td");
                if (k === 'ibDown'
                    && transferMethodCode
                ) {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('down');
                    cell2.className += 'btn btn-sm btn-outline-danger';
                    cell2.setAttribute('name', 'data-download');
                    cell2.setAttribute('data-type', 'ib');
                    cell2.setAttribute('data-order', data[key]['OrderIdx']);
                    cell2.setAttribute('data-trans-code', transferMethodCode);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else if (k === 'uuid') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('down');
                    cell2.className += 'btn btn-sm btn-outline-success';
                    cell2.setAttribute('name', 'data-download');
                    cell2.setAttribute('data-type', 'disease');
                    cell2.setAttribute('data-order', data[key]['OrderIdx']);
                    cell2.setAttribute('data-uuid', data[key]['uuid']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('more');
                    cell2.className += 'btn btn-sm btn-info';
                    cell2.setAttribute('name', 'data-search');
                    cell2.setAttribute('data-value', data[key]['OrderIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else {
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        })
        tbl.setAttribute("border", "2");

        let downBtn = document.getElementsByName('data-download');
        if (downBtn) {
            downBtn.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mainScript._methodType = 'POST';
                    if (this.getAttribute('data-type') === 'disease') {
                        let data = {
                            'purpose': 'get***Report',
                            'orderIdx': this.getAttribute('data-order'),
                            'uuid': this.getAttribute('data-uuid'),
                        };
                        adminScript.locate(data);
                    } else if (this.getAttribute('data-type') === 'ib') {
                        let data = {
                            'purpose': 'getIbReport',
                            'orderIdx': this.getAttribute('data-order'),
                            'tCode': this.getAttribute('data-trans-code'),
                        };
                        adminScript.locate(data);
                    }
                });
            });
        }

        let allChkBtn = document.getElementById('selectall');
        const target = document.getElementsByName('orderIdx[]');
        allChkBtn.addEventListener('click', e => {
            const btnStatus = allChkBtn.checked;
            target.forEach(el => {
                if (btnStatus) {
                    el.checked = true;
                } else {
                    el.checked = false;
                }
            })
        });
        // 모두체크 동기화
        target.forEach(el => {
            el.addEventListener('click', e => {
                for (let checkbox of target) {
                    allChkBtn.checked = true;
                    if (!checkbox.checked) {
                        allChkBtn.checked = false;
                        break;
                    }
                }
            })
        });

        let findAllocateBtn = document.querySelector('.downloadDbAllocation');
        if (!findAllocateBtn.getAttribute("data-click")) {
            findAllocateBtn.setAttribute("data-click", true);
            findAllocateBtn.addEventListener('click', e => {
                if (confirm('미할당 유저를 다운로드합니다.\n당일 다운로드 횟수제한 3회')) {
                    mainScript._methodType = 'get';
                    mainScript._purpose = 'findAllocateUser';
                    mainScript.request();
                }
                return false;
            });
        }
        // 엑셀 등록버튼 클릭시
        let excelBtn = document.querySelectorAll('.excel-btn');
        if (excelBtn) {
            excelBtn.forEach(function (btn) {
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
            });
        }

        // 모두 다운로드 기능
        let allDownBtn = document.querySelectorAll('.all-down');
        if (allDownBtn) {
            allDownBtn.forEach(btn => {
                if (!btn.getAttribute("data-click")) {
                    btn.setAttribute("data-click", true);
                    btn.addEventListener('click', e => {
                        let odr = [];
                        let stopFlag = false;
                        const target = btn.getAttribute('data-value');
                        document.querySelectorAll('input[name="orderIdx[]"]:checked').forEach(function (el) {
                            if (target === 'ib' && el.getAttribute('data-is-allocated') === '') {
                                stopFlag = true;
                            }
                            if (el.value !== '') {
                                odr.push(el.value.replace('idx', ''));
                            }
                        });
                        if (odr.length === 0) {
                            alert("다운받으실 대상을 선택해주세요.");
                            return;
                        }
                        if (odr.length > 30){
                            alert(`한번에 다운로드 가능한 최대 개수는 30개 입니다. \n현재 선택개수 : ${odr.length}개`);
                            return;
                        }
                        if (stopFlag) {
                            alert("다운 받을 수 없는 대상자를 선택하였습니다. 다시 선택하십시오.");
                            return;
                        }
                        let data = {
                            'purpose': 'allDown',
                            'target': target,
                            'orderIdx': JSON.stringify(odr)
                        };
                        mainScript._methodType = 'GET';
                        mainScript._purpose = 'allDown';
                        adminScript.locate(data);
                    })
                }
            });
        }

        // 옵션 버튼 클릭
        let moreBtn = document.getElementsByName('data-search');
        if (moreBtn) {
            moreBtn.forEach(btn => {
                btn.addEventListener('click', el => {
                    const orderIdx = el.target.getAttribute('data-value');
                    mainScript._purpose = 'searchIbUserData';
                    mainScript._methodType = 'get';
                    let data = {
                        'orderIdx': orderIdx
                    };
                    mainScript.request(data);
                })
            })
        }

        let downExcelBtn = document.querySelector('.dbExcelDownload');
        downExcelBtn.addEventListener('click', e => {

            let serviceCompany = document.getElementById('serviceCompany').value;
            let minDate = document.getElementById('minDate').value;
            let maxDate = document.getElementById('maxDate').value;

            if (serviceCompany === "" || minDate === "" || maxDate === "") {
                alert('필수 값 옵션들이 선택되지 않았습니다.');
                return;
            }
            mainScript._purpose = 'ibAllocationData';
            mainScript._methodType = 'get';
            let data = {
                minDate: minDate,
                maxDate: maxDate,
                serviceCompanyIdx: serviceCompany,
            };
            mainScript.request(data);
        });
    },
};