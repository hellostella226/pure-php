let mainScript = {
    _purpose: 'disease', //controller 요청 목적
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
        if (typeof document.location.href.split('/')[4] !== 'undefined' && document.location.href.split('/')[4] !== '') {
            this._purpose = document.location.href.split('/')[4];
        }
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
    call: function (target, search) {
        let data = null;
        let url = document.location.href;
        data = [...this.dataset(search).entries()];
        data = data
            .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
            .join('&');

        const operator = url.indexOf('?') > 0 ? '&' : '?';
        url += operator + data;

        if (target === 'modal') {
            sendRequest(this._methodType, url, data, '', '', this.setModal);
        } else if (target === 'selectBox') {
            sendRequest(this._methodType, url, data, '', '', this.setForm);
        }
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
        if (typeof response.code !== 'undefined') {
            if (response.code === '20200') {
                if (mainScript._methodType === 'POST') {
                    alert('등록되었습니다.');
                    location.reload();
                    return;
                }
                switch (mainScript._purpose) {
                    case 'disease' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.setEventListener(mainScript._purpose);
                        mainScript.rendering(response.data.data, response.data.pagination.start);
                        adminScript.pagination(response.data.pagination);
                        break;
                    default : //catalogList,searchProductGroupName,searchProductItem
                        mainScript.setModal(response);
                        break;
                }
            }
        }
    },
    paging: function (num) {
        this._search.page = num;
        this.request();
    },
    setForm: function (response) {

    },
    setEventListener: function (purpose) {

    },
    setModal: function (response) {

    },
    rendering: function (data, num) {
        deleteElement('adminTable');
        let year = new Date().getFullYear();
        const tbl = document.getElementById('adminTable');
        Object.keys(data).forEach(function(key) {
                num++;
                const row = document.createElement("tr");
                const indexCell = document.createElement("td");
                const indexCellText = document.createTextNode(num);
                indexCell.appendChild(indexCellText);
                row.appendChild(indexCell);

                //테스트 계정인 경우 별색
                if (data[key]['TestMembers'] !== null) {
                    row.className += 'test-Members';
                }
                let values = {
                    'RegDatetime': data[key]['RegDatetime'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'Name': data[key]['Name'],
                    'NhisPreviewListIdx': data[key]['NhisPreviewListIdx'] ? 'Y' : 'N',
                    'Options': '',
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                    if (k === 'Options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('다운로드');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.setAttribute('name', 'data-download');
                        cell2.setAttribute('data-uuid', data[key]['Uuid']);
                        cell2.setAttribute('data-order', data[key]['OrderIdx']);
                        cell2.appendChild(cellText2);
                        cell.appendChild(cell2);
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            });
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