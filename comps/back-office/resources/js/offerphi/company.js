let mainScript = {
    _purpose: 'company', //controller 요청 목적
    _methodType: 'get', //method 타입
    _search: { //pagination 관련 값
        'keyword': '',
        'value': '',
        'startDate': '',
        'endDate': '',
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
        let formData = new FormData;
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
                    if(response.message) {
                        alert(response.message);
                    } else {
                        alert('등록되었습니다.');
                    }
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'search' :
                    case 'company' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.rendering(data.data, data.pagination.start);
                        adminScript.pagination(data.pagination);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    case 'excelFileDown' :
                        let d = JSON.stringify(data.data);
                        adminScript.downloadSpreadSheet(d, "/b***-*abc/app/abc/spreadsheet/excelDown.php");
                        break;
                    default : // searchTicketData
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
            if (target) {
                if (target.length === 1) {
                    for (let key in data) {
                        const option = document.createElement('option');
                        option.text = data[key]['text'];
                        option.value = data[key]['value'];
                        target.appendChild(option);
                    }
                }
            }
        }
    },
    setEventListener: function (purpose) {

        switch (purpose) {
            case 'company' :
                // 특정 상담사 무료잔량수정버튼 클릭
                const freeTicketBtn = document.querySelectorAll('.update-client');
                if(freeTicketBtn) {
                    freeTicketBtn.forEach(el =>{
                        el.addEventListener('click', e=> {
                            e.preventDefault();
                            const ClientControlIdx = e.target.getAttribute('data-idx');
                            let data = {
                                'ClientControlIdx' : ClientControlIdx,
                            };
                            mainScript._methodType ='get';
                            mainScript._purpose = 'searchTicketData';
                            mainScript.request(data);
                        });
                    });
                }

                const naviBtn = document.querySelectorAll('.navi-update');
                if(naviBtn) {
                    naviBtn.forEach(el => {
                        el.addEventListener('click', e=> {
                            let className = e.target.className;
                            if(className.includes('active')) {
                                e.target.style = "";
                            } else {
                                e.target.style = "";
                            }
                        });
                    });
                }

                // 수량 업데이트
                const updateCount = document.getElementById('updateCount');
                let oldIssuedCount = parseInt(document.getElementById('oldIssuedCount').value);
                if(updateCount) {
                    let finalValue = oldIssuedCount;
                    updateCount.addEventListener("keyup", e => {
                        let updateValue = parseInt(e.target.value);
                        finalValue = parseInt(document.getElementById('oldIssuedCount').value) + updateValue;
                        if(finalValue < 0) {
                            alert('삭제수량이 지급량보다 큽니다. 다시 계산하세요..');
                            document.getElementById('issuedCount').value = finalValue = document.getElementById('oldIssuedCount').value;
                            e.target.value = 0;
                            return;
                        } else if(finalValue === 0 && parseInt(document.getElementById('issuedCount').value) !== 0) {
                            if(!confirm('모든 무료지급권을 삭제하시겠습니까?')) {
                                document.getElementById('issuedCount').value = finalValue = document.getElementById('oldIssuedCount').value;
                                e.target.value = 0;
                                return;
                            }
                        }
                        document.getElementById('issuedCount').value = finalValue;
                    });

                    updateCount.addEventListener("click", e => {

                        let updateValue = parseInt(e.target.value);
                        finalValue = parseInt(document.getElementById('oldIssuedCount').value) + updateValue;
                        if(finalValue < 0) {
                            alert('삭제수량이 지급량보다 큽니다. 다시 계산하세요..');
                            document.getElementById('issuedCount').value = finalValue = document.getElementById('oldIssuedCount').value;
                            e.target.value = 0;
                            return;
                        } else if(finalValue === 0 && parseInt(document.getElementById('issuedCount').value) !== 0) {
                            if(!confirm('모든 무료지급권을 삭제하시겠습니까?')) {
                                document.getElementById('issuedCount').value = finalValue = document.getElementById('oldIssuedCount').value;
                                e.target.value = 0;
                                return;
                            }
                        }
                        document.getElementById('issuedCount').value = finalValue;
                    });
                }

                // 상담사 사용여부 업데이트 버튼
                let updateBtn = document.querySelectorAll('.update-btn');
                if (updateBtn) {
                    updateBtn.forEach(el => {
                        el.addEventListener('click', e => {
                            let alertText = "중지";
                            let converse = el.getAttribute('data-converse');
                            let key = el.getAttribute('data-key');
                            let value = el.getAttribute('data-value');

                            if(converse === 'Y') {
                                if(value === "Y") {
                                    value = "0";
                                } else {
                                    alertText = "사용";
                                    value = "1";
                                }
                            }
                            if(!confirm("아래의 확인 벼튼을 클릭하시면\n ["+ alertText +"] 상태로 전환됩니다.\n진행하시려면 확인을 클릭하세요.")) {
                                return;
                            }
                            let data = {
                                'idx' : el.getAttribute('data-idx'),
                                'key' : key,
                                'value' : value,
                            };
                            mainScript._purpose = 'updateIsActiveClient';
                            mainScript._methodType = 'POST';
                            mainScript.request(data);
                        })
                    })
                }

                // 지급 잔량 수정 버튼
                let updateTicketCount = document.getElementById('updateTicketCount');
                if(updateTicketCount) {
                    updateTicketCount.addEventListener('click', el => {
                        el.preventDefault();
                        let updateTarget = 'client';
                        let updateTab = document.querySelectorAll('.update-data');
                        updateTab.forEach(el=>{
                            let className = el.className;
                            if(className.includes('active')) {
                                if(el.id === 'update-ticket-tab') {
                                    updateTarget = 'ticket';
                                }
                            }
                        });
                        let data;
                        if(updateTarget === 'client') {
                            if (document.getElementById('clientCustomerNameClient').value === '') {
                                alert('상담사명은 필수값입니다.');
                                return;
                            } else if (document.getElementById('clientCustomerNameClient').value === '') {
                                alert('전화번호는 필수값입니다.');
                                return;
                            }
                            //상담사정보 수정
                            data = {
                                'ClientControlIdx': document.getElementById('ClientControlIdx').value,
                                'cCGroup': document.getElementById('cCGroupClientValue').value,
                                'clientCustomerName': document.getElementById('clientCustomerNameClient').value,
                                'cCTel': document.getElementById('cCTelClient').value,
                            };
                            mainScript._purpose = 'updateClientData';
                        } else {
                            //무료지급수량 수정
                            if (parseInt(document.getElementById('oldIssuedCount').value) ===
                                parseInt(document.getElementById('issuedCount').value)) {
                                alert('수정할 내용이 없습니다.');
                                return;
                            }
                            let updateCount = parseInt(document.getElementById('updateCount').value);
                            if(isNaN(updateCount)) {
                                alert('수정할 내용이 없습니다.');
                                return;
                            }
                            data = {
                                'ClientControlIdx': document.getElementById('ClientControlIdx').value,
                                'SaleGoodsIdx': document.getElementById('SaleGoodsIdx').value,
                                'updateCount': updateCount
                            };
                            mainScript._purpose = 'updateFreeTicket';
                        }
                        mainScript._methodType = 'POST';
                        mainScript.request(data);
                        el.preventDefault();
                    });
                }

                break;
            default :
                break;
        }
        const client = document.querySelectorAll('input[name="clientIdx"]');
        client.forEach( el=>{
            el.addEventListener('click', e=> {
                if (el.checked === true) {
                    for (key in client) {
                        client[key].checked = false;
                    }
                    el.checked = true;
                }
            });
        });
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if (mainScript._purpose === 'searchTicketData') { // 상담사 조회
                    // modal 대상
                    selector = '#updateFreeTicket';
                    // 공통
                    document.getElementById('SaleGoodsIdx').value = data.SaleGoodsIdx;
                    document.getElementById('ClientControlIdx').value = data.ClientControlIdx;
                    // 정보수정탭
                    document.getElementById('cCGroupClientValue').value = data.cCGroup;
                    document.getElementById('clientCustomerCodeClient').value = data.clientCustomerCode;
                    document.getElementById('clientCustomerNameClient').value = data.clientCustomerName;
                    document.getElementById('serviceCompanyNameClient').value = data.serviceCompanyName;
                    document.getElementById('cCTelClient').value = data.cCTel;
                    // 잔량수정탭
                    document.getElementById('cCGroup').value = data.cCGroup;
                    document.getElementById('clientCustomerCode').value = data.clientCustomerCode;
                    document.getElementById('clientCustomerName').value = data.clientCustomerName;
                    document.getElementById('serviceCompanyName').value = data.serviceCompanyName;
                    document.getElementById('oldIssuedCount').value = data.oldIssuedCount;
                    document.getElementById('issuedCount').value =  data.oldIssuedCount;

                } else if (mainScript._purpose === 'searchCompany') { //거래처 조회
                    // modal 대상
                    selector = '#companyUpdate';
                    document.getElementById('client').value = data.ParentClientCustomerIdx;
                    document.querySelector("#companyUpdate input[name='ClientControlIdx']").value = data.ClientControlIdx;
                    document.querySelector("#companyUpdate input[name='companyCode']").value = data.ClientCustomerCode;
                    document.querySelector('#companyUpdate #companyName').value = data.ClientCustomerName;
                    document.querySelector('#companyUpdate #phone').value = data.CCTel;
                }

                if(mainScript._purpose === 'searchCompanyIssue'){
                    // modal 대상
                    selector = '#issueUpdateModal';
                    document.querySelector("#issueUpdateModal input[name='ClientControlIdx']").value = data.ClientControlIdx;
                    document.querySelector("#issueUpdateModal input[name='parentClientCustomerIdx']").value = data.ParentClientCustomerIdx;
                    document.querySelector("#issueUpdateModal input[name='companyCode']").value = data.ClientCustomerCode;
                    document.querySelector("#issueUpdateModal input[name='companyName']").value = data.ClientCustomerName;
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
        Object.keys(data).reverse()
            .forEach(function(key) {
            const row = document.createElement("tr");
            let frontLocation = document.location.origin.replace("admin","ds");
            let ticketInfo = data[key]['ticketInfo'];
            let clientTicketData = new Object();
            clientTicketData[key] = {
                '1' : { 'issuedCount' : 0, 'expiredCount' : 0,},
                '2' : { 'issuedCount' : 0, 'expiredCount' : 0,},
            };
            if(Object.keys(ticketInfo).length > 0) {
                for(let k in ticketInfo) {
                    if(ticketInfo[k]['ticketType'] === '1') {
                        clientTicketData[key]['1']['issuedCount'] += ticketInfo[k]['issuedCount'];
                        clientTicketData[key]['1']['expiredCount'] += ticketInfo[k]['expiredCount'];
                        //유료
                    } else {
                        //무료
                        clientTicketData[key]['2']['issuedCount'] += ticketInfo[k]['issuedCount'];
                        clientTicketData[key]['2']['expiredCount'] += ticketInfo[k]['expiredCount'];
                    }
                }
            }
            let values = {
                'clientCustomerCode': data[key]['clientCustomerCode'] ? data[key]['clientCustomerCode'] : '',
                'regDatetime': data[key]['regDatetime'] ? data[key]['regDatetime'] : '',
                'modDatetime': data[key]['modDatetime'] ? data[key]['modDatetime'] : data[key]['regDatetime'],
                'registType' : data[key]['registrationPath'] === '2' ? '자동' : '수동', //아직 미정
                'serviceCompanyName' : data[key]['serviceCompanyName'],
                'cCGroup': data[key]['cCGroup'] ? data[key]['cCGroup'] : '', //회사명
                'cCManager': data[key]['cCManager'] ? data[key]['cCManager'] : '',
                'cCTel': data[key]['cCTel'] ? data[key]['cCTel'] : '',
                'payCount' : clientTicketData[key]['1']['issuedCount']+clientTicketData[key]['1']['expiredCount'],
                'payIssuedCount' : clientTicketData[key]['1']['issuedCount'],
                'freeCount' : clientTicketData[key]['2']['issuedCount']+clientTicketData[key]['2']['expiredCount'],
                'freeIssuedCount' : clientTicketData[key]['2']['issuedCount'],
                'url': data[key]['clientCustomerCode'] ? `${frontLocation}/abc/?hCode=${data[key]['clientCustomerCode']}` : '',
                'isUpdate': '',
                'isActive': '',
            };
            for (let k in values) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode(values[k]);
                cell.appendChild(cellText);
                if(k === 'isActive') {
                    let cell2 = document.createElement("button");
                    cell2.className += 'btn btn-sm update-btn update-ticket ';
                    let btnTxt = '사용';
                    if(data[key]['isActive'] === 'N') {
                        btnTxt = '중지';
                        cell2.className += 'btn-danger';
                    } else {
                        cell2.className += 'btn-info';
                    }
                    let cellText2 = document.createTextNode(btnTxt);
                    cell2.setAttribute('data-key', 'isActive');
                    cell2.setAttribute('data-converse', 'Y');
                    cell2.setAttribute('data-value', data[key]['isActive']);
                    cell2.setAttribute('data-idx', key);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else if(k === 'isUpdate') {
                    let cell2 = document.createElement("button");
                    cell2.className += 'btn btn-sm update-client ';
                    let btnTxt = '수정';
                    let cellText2 = document.createTextNode(btnTxt);
                    cell2.setAttribute('data-key', 'isUpdate');
                    cell2.setAttribute('data-idx', key);
                    cell2.appendChild(cellText2);
                    cell2.style = 'background-color:#C383E1';
                    cell.appendChild(cell2);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        });
        tbl.setAttribute("border", "2");

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

        //수정 버튼 클릭시
        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                mainScript._purpose = 'searchCompany';
                let data = {'ClientControlIdx': this.getAttribute('data-value')};
                mainScript.request(data);
            });
        });
    },
    register: function (data) {
        switch(mainScript._purpose){
            case 'registConsultant':
                data.cCManager = document.getElementById("clientCustomerName").value;
                mainScript._methodType = 'POST';
                mainScript.request(data);
                break;
            default :
                break;
        }
        return false;
    },
};