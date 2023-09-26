let mainScript = {
    _purpose: 'company', //controller 요청 목적
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
                    case 'search' :
                    case 'company' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    default : //catalogList,searchProductGroupName,searchProductItem
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
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if (mainScript._purpose === 'searchCompany') { //거래처 조회
                    // modal 대상
                    selector = '#companyUpdate';
                    document.getElementById('client').value = data.ParentClientCustomerIdx;
                    document.getElementById('category').value = data.Category;
                    document.querySelector('#ClientControlIdx').value = data.ClientControlIdx;
                    document.querySelector('#companyCode').value = data.ClientCustomerCode;
                    document.querySelector('#companyName').value = data.ClientCustomerName;
                    document.querySelector('#manager').value = data.CCManager;
                    document.querySelector('#phone').value = data.CCTel;
                    document.querySelector('#postcode').value = data.PostCode;
                    document.querySelector('#address').value = data.City + ' ' + data.FullCity + ' ' + data.State;
                    document.querySelector('#addressDetail').value = data.AddressDetail;
                    document.querySelector('#productGroup').value = data.ProductGroupIdx;
                    document.getElementById("companyCode").readOnly = true;
                    document.getElementById('client').disabled = true;
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
        for (let key in data) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);
            let frontLocation = document.location.origin.replace("admin","ds");
            let values = {
                'regDatetime': data[key]['RegDatetime'],
                'parentClientCustomerName': data[key]['ParentClientCustomerName'],
                'category': data[key]['Category'] === 'H' ? '병원' : '약국',
                'clientCustomerCode': data[key]['ClientCustomerCode'],
                'clientCustomerName': data[key]['ClientCustomerName'],
                'cCManager': data[key]['CCManager'],
                'cCTel': data[key]['CCTel'],
                'city': `${data[key]['State']} ${data[key]['City']} ${data[key]['FullCity']} ${data[key]['AddressDetail']}`,
                'url': data[key]['ClientCustomerCode'] ? `${frontLocation}/abc/?hCode=${data[key]['ClientCustomerCode']}` : '',
                'responseType': data[key]['ResponseType'] === '1' ? '이메일' : '직접출력',
                'specimenType': data[key]['SpecimenType'],
                'productGroupCode': data[key]['ProductGroupCode'],
                'productGroupName': data[key]['ProductGroupName'],
                'qrUrl': data[key]['QRurl'],
                'options': '',
            };
            for (let k in values) {
                let cell = document.createElement("td");
                if(k === 'qrUrl') {
                    //qrUrl
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('다운로드');
                    cell2.className+= 'btn btn-sm btn-info qr-btn';
                    cell2.setAttribute('data-value',data[key]['ClientControlIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                } else {
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                }
                if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('수정');
                    cell2.className += 'btn btn-sm btn-success';
                    cell2.setAttribute('name', 'data-modify');
                    cell2.setAttribute('data-value', data[key]['ClientControlIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        }
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
                        return;
                    });
                }
            });
        }

        // qr코드 클릭시
        let qrBtn = document.querySelectorAll('.qr-btn');
        if(qrBtn) {
            qrBtn.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mainScript._methodType = 'POST';
                    let data = {
                        'purpose': 'qrDown',
                        'ClientControlIdx' : this.getAttribute('data-value')
                    };
                    adminScript.locate(data);
                });
            });
        }

        // 카탈로그 항목 클릭시
        document.getElementsByName('data-view').forEach(function (btn) {
            btn.addEventListener('click', function () {
                mainScript._purpose = 'catalogList';
                let data = {'productIdx': this.getAttribute('data-value')};
                mainScript.request(data);
            });
        });

        //수정 버튼 클릭시
        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                mainScript._purpose = 'searchCompany';
                mainScript._methodType = 'get';
                let data = {'ClientControlIdx': this.getAttribute('data-value')};
                mainScript.request(data);
            });
        });

        //등록시 readonly 제거
        document.getElementById("register").addEventListener('click', function () {
            document.getElementById("companyCode").readOnly = false;
            document.getElementById('client').disabled = false;
        });
    },
};