let mainScript = {
    _purpose: 'sms',
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
                    alert('전송되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'sms' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    default : // searchSms
                        mainScript.setModal(response);
                        break;
                }
                for (let key in data) {
                    if (key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }
            } else if (response.code === '20400') {
                if (response.desc === 'sendSms' && response.message) {
                    alert(response.message);
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
    register: function (data) {
        let idxCnt = data.idxList.split(',').length;
        if (confirm(`${idxCnt}건을 선택하셨습니다. 발송을 진행하시겠습니까?`)) {
            mainScript._purpose = 'sendSms';
            mainScript._methodType = 'POST';
            mainScript.request(data);
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'sms' :
                // 전체 체크 선택 및 해제
                document.getElementsByName('data-select-all').forEach(function (checkbox) {
                    checkbox.addEventListener('click', function () {
                        const checkboxs = document.getElementsByName('data-select');
                        checkboxs.forEach(function (box) {
                           if (box !== checkbox) {
                               box.checked = checkbox.checked;
                           }
                        });
                    });
                });

                //수정 버튼 클릭시
                document.getElementsByName('data-view').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        mainScript._purpose = 'searchSms';
                        mainScript._methodType = 'get';
                        let data = {
                            'UsersIdx': this.getAttribute('data-mem-idx'),
                            'orderIdx': this.getAttribute('data-order-idx'),
                        };
                        mainScript.request(data);
                    });
                });

                // 비즈엠보내기 버튼 클릭시
                document.getElementsByName('data-sms').forEach(function (btn) {
                    if (!btn.getAttribute("data-click")) {
                        btn.setAttribute("data-click", true);
                        btn.addEventListener('click', function (e) {
                            const idxTemp = document.querySelectorAll("input[name='data-select']:checked");
                            if (idxTemp.length === 0) {
                                alert('알림톡 발송대상 데이터가 선택되지 않았습니다.');
                                return false;
                            }
                            let idx = [];
                            idxTemp.forEach(function (checkbox) {
                                idx.push(checkbox.getAttribute('data-value'));
                            });
                            const idxList = document.getElementById('idxList');
                            idxList.value = idx;

                            selector = "#sendSmsModal";
                            const modalEl = document.querySelector(selector);
                            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                            modal.show();
                        });
                    }
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
                if (mainScript._purpose === 'searchSms') {
                    selector = "#viewModal";
                    document.getElementById('registerBizMDate').innerText = data.registerBizMDate;
                    document.getElementById('diseaseBizMDate').innerText = data.diseaseBizMDate;
                    document.getElementById('geneticBizMDate').innerText = data.geneticBizMDate;
                    document.getElementById('consultBizMDate').innerText = data.consultBizMDate;
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
        Object.keys(data).reverse()
            .forEach(function (key) {
                num++;
                const row = document.createElement("tr");
                const indexCell = document.createElement("td");
                indexCell.className += "text-center";
                const indexInput = document.createElement("input");
                indexInput.className = 'form-check-input';
                indexInput.name = 'data-select';
                indexInput.setAttribute('type', 'checkbox');
                indexInput.setAttribute('data-value', key);
                indexCell.appendChild(indexInput);
                row.appendChild(indexCell);

                const indexCell1 = document.createElement("td");
                const indexCellText1 = document.createTextNode(num);
                indexCell1.appendChild(indexCellText1);
                row.appendChild(indexCell1);

                //테스트 계정인 경우 별색
                if (data[key]['TestMembers'] !== null) {
                    row.className += 'test-Members';
                }
                let values = {
                    'regDatetime': data[key]['RegDatetime'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'name': data[key]['Name'],
                    'registerBizM': data[key]['RegisterBizM'] ? 'Y' : '-',
                    'diseaseBizM': data[key]['DiseaseBizM'] ? 'Y' : '-',
                    'geneticBizM': data[key]['GeneticBizM'] ? 'Y' : '-',
                    'consultBizM': data[key]['ConsultBizM'] ? 'Y' : '-',
                    'options': key,
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    if (k === 'options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('더보기');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.name = 'data-view';
                        cell2.setAttribute('data-order-idx', key);
                        cell2.setAttribute('data-mem-idx', data[key]['UsersIdx']);
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

        tbl.setAttribute("border", "2");
    },
};
