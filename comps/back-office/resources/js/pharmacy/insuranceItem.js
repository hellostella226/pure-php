let mainScript = {
    _purpose: 'insuranceItem',
    _methodType: 'get',
    _search: {
        'keyword': '',
        'value': '',
        'entry': 50, //출력 리밋
        'page': 1, //현재 페이지
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
                    alert('전송되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'insuranceItem' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
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
            case 'insuranceItem' :
                document.querySelector('.removeBtn').addEventListener('click', function () {
                    let data = {};
                    data.InsureanceIdx = this.getAttribute('data-value');
                    mainScript._purpose = this.getAttribute('data-target');
                    if (!confirm('정말 삭제하시겠습니까?')) {
                        return;
                    }
                    mainScript._methodType = 'POST';
                    mainScript.request(data);
                    //모달 닫기
                    let modalEl = this.closest('div.modal');
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                });
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if (mainScript._purpose === 'insuranceItemUpdate') {
                    selector = `#${mainScript._purpose}`;
                    const serviceCompanyName = data.getAttribute('data-sc-name');
                    const parentInsureanceIdx = data.getAttribute('data-insurance-idx');
                    const InsureanceIdx = data.getAttribute('data-item-idx');
                    const parentItemCode = data.getAttribute('data-insurance-code');
                    const parentItemName = data.getAttribute('data-insurance-name');
                    const itemCode = data.getAttribute('data-item-code');
                    const itemName = data.getAttribute('data-item-name');
                    document.getElementById('serviceCompanyName').value = serviceCompanyName;
                    document.getElementById('parentInsureanceIdx').value = parentInsureanceIdx;
                    document.getElementById('InsureanceIdx').value = InsureanceIdx;
                    document.getElementById('parentItemCode').value = parentItemCode;
                    document.getElementById('parentItemName').value = parentItemName;
                    document.getElementById('itemCode').value = itemCode;
                    document.getElementById('itemName').value = itemName;

                    const removeBtn = document.querySelector('.removeBtn');
                    if (InsureanceIdx) {
                        removeBtn.setAttribute('data-value', InsureanceIdx);
                    } else {
                        removeBtn.setAttribute('data-value', parentInsureanceIdx);
                    }
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
        Object.keys(data).forEach(function (key) {
                num++;
                const row = document.createElement("tr");
                const indexCell = document.createElement("td");
                const indexCellText = document.createTextNode(num);
                indexCell.appendChild(indexCellText);
                row.appendChild(indexCell);

                let values = {
                    'serviceCompanyName': data[key]['ServiceCompanyName'],
                    'parentInsureanceIdx': data[key]['ParentInsureanceIdx'],
                    'parentItemCode': data[key]['ParentItemCode'],
                    'parentItemName': data[key]['ParentItemName'],
                    'itemCode': data[key]['ItemCode'] ?? '',
                    'itemName': data[key]['ItemName'] ?? '',
                    'options': key,
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    if (k === 'options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('수정');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.name = 'data-modify';
                        cell2.setAttribute('data-sc-name', data[key]['ServiceCompanyName']);
                        cell2.setAttribute('data-insurance-idx', data[key]['ParentInsureanceIdx']);
                        cell2.setAttribute('data-item-idx', data[key]['InsureanceIdx'] ?? '');
                        cell2.setAttribute('data-insurance-code', data[key]['ParentItemCode']);
                        cell2.setAttribute('data-insurance-name', data[key]['ParentItemName']);
                        cell2.setAttribute('data-item-code', data[key]['ItemCode'] ?? '');
                        cell2.setAttribute('data-item-name', data[key]['ItemName'] ?? '');
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
        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                let response = {};
                response.code = '20200';
                response.data = btn;
                mainScript._purpose = 'insuranceItemUpdate'
                mainScript.setModal(response);
            });
        });

        // 엑셀 등록버튼 클릭시
        let excelBtn = document.querySelectorAll('.excel-btn');
        if(excelBtn) {
            excelBtn.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    let data = {};
                    const selector = btn.getAttribute('data-target');
                    const form = document.querySelector('.'+selector);
                    form.querySelectorAll('input,select').forEach(function(el,i) {
                        if(el.value) {
                            if(el.type === 'checkbox') {
                                if(el.checked) {
                                    data[el.name] = el.value;
                                }
                            } else if(el.type === 'file') {
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
            });
        }

        document.getElementById('registerType')
            .addEventListener('change', function () {
                const serviceCompanyLabel = document.getElementById('serviceCompanyLabel');
                const serviceCompany = document.getElementById('serviceCompany');

                if (this.value === 'insurance') {
                    serviceCompanyLabel.style.display = "block";
                    serviceCompany.style.display = "block";
                } else {
                    serviceCompanyLabel.style.display = "none";
                    serviceCompany.style.display = "none";
                }
            });

        tbl.setAttribute("border", "2");
    },
};
