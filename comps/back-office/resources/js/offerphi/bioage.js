let mainScript = {
    _purpose : 'bioage', //controller 요청 목적
    _methodType : 'get', //method 타입
    _search : { //pagination 관련 값
        'keyword' : '',
        'value' : '',
        'entry' : 50, //출력 리밋
        'page' : 1, //현재 페이지
        'column': '',
        'sort': '',
    },
    init: function () {
        this._purpose = document.location.href.split('/')[4];
        this.request();
    },
    dataset : function(data = []) {
        var formData = new FormData;
        formData.append('purpose', this._purpose);
        if(data) {
            for(let key in data) {
                formData.append(key, data[key]);
            }
        }
        return formData;
    },
    request: function(f = this._search) {
        let data = null;
        let url = document.location.href;
        if(this._methodType === 'get') {
            data = [...this.dataset(f).entries()];
            data = data
                .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
                .join('&');

            const operator = url.indexOf('?') > 0 ? '&' : '?';
            url += operator + data;
        } else {
            data = this.dataset(f);
        }
        sendRequest(this._methodType, url, data ,'','',this.callback);
    },
    callback: function(response) {
        if(response) {
            if(response.code === '20200') {
                if(mainScript._methodType === 'POST') {
                    alert('등록되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'bioage' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.rendering(data.data, data.pagination.start);
                        adminScript.pagination(data.pagination);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                    default :
                        mainScript.setModal(response);
                        break;
                }
                for (let key in data) {
                    if(key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }

            }
        }
    },
    paging: function(num) {
        this._search.page = num;
        this.request();
    },
    setForm : function(key, data) {
        const type = key.split('::')[0];
        const id = key.split('::')[1];
        // 셀렉트박스 세팅
        if(type === 'select') {
            const target = document.getElementById(id);
            for(let key in data) {
                const option = document.createElement('option');
                option.text = data[key]['text'];
                option.value = data[key]['value'];
                target.appendChild(option);
            }
        }
    },
    setEventListener: function(purpose) {

        switch (purpose) {
            case 'bioage' :
                break;
            default :
                break;
        }
    },
    setModal : function(response) {
        if(response) {
            if(response.code === '20200') {
                let data = response.data;
                if(mainScript._purpose === '') { //거래처 조회
                    // modal 대상
                    selector = '#';
                    /*document.getElementById('client').value = data.ParentClientCustomerIdx;*/

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
        Object.keys(data).forEach(function(key) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);
            let values = {
                'clientCustomerCode': data[key]['ClientCustomerCode'],
                'parentClientCustomerName': data[key]['ParentClientCustomerName'],
                'clientCustomerName' : data[key]['ClientCustomerName'],
                'cCTel' : data[key]['CCTel'],
                'calcDate': data[key]['CalcDate'],
                'MembersName': data[key]['Name'],
                'phone': data[key]['Phone'],
                'isResultPage' : data[key]['NhisPreviewListIdx'] ? 'Y' : 'N',
                'downUrl': '',
            };
            for(let k in values) {
                let cell = document.createElement("td");
                if(k === 'downUrl') {
                    //qrUrl
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('다운로드');
                    cell2.className+= 'btn btn-sm btn-info';
                    cell2.name = 'data-download';
                    cell2.setAttribute('data-uuid',data[key]['Uuid']);
                    cell2.setAttribute('data-order',data[key]['OrderIdx']);
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

        //  다운로드 버튼
        let downBtn = document.getElementsByName('data-download');
        if(downBtn) {
            downBtn.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mainScript._methodType = 'POST';
                    let data = {
                        'purpose': 'get***Report',
                        'orderIdx' : this.getAttribute('data-order'),
                        'uuid' : this.getAttribute('data-uuid'),
                    };
                    adminScript.locate(data);
                });
            });
        }
    },
};