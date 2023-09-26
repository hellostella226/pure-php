let mainScript = {
    _purpose: 'genetic', //controller 요청 목적
    _methodType: 'get', //method 타입
    _search: { //pagination 관련 값
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
                    case 'genetic' :
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
        Object.keys(data).reverse()
            .forEach(function(key) {
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
                    'ClientCustomerName': data[key]['ClientCustomerName'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'Name': data[key]['Name'],
                    'GCRegDate': data[key]['GCRegDate'] ?? '',
                    'GCRegNo': data[key]['GCRegNo'] ?? '',
                    'ResponseType': (data[key]['ResponseType'] == '1') ? "이메일" : ((data[key]['ResponseType'] == '2') ? "직접출력" : ''),
                    'IsSend': data[key]['IsSend'] === '1' ? 'Y' : 'N',
                    'Options': '',
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                    if (k === 'Options') {
                        if (data[key]['AgreementPaperDir']) {
                            let cell2 = document.createElement("button");
                            let cellText2 = document.createTextNode('다운로드');
                            cell2.className += 'btn btn-sm btn-info';
                            cell2.setAttribute('name', 'data-download');
                            cell2.setAttribute('data-order', data[key]['OrderIdx']);
                            cell2.appendChild(cellText2);
                            cell.appendChild(cell2);
                        }
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            });
        tbl.setAttribute("border", "2");
        // 카탈로그 항목 클릭시
        document.getElementsByName('data-download').forEach(function (btn) {
            btn.addEventListener('click', function () {
                mainScript._purpose = 'geneticAgreement';
                let data = {'orderIdx': this.getAttribute('data-order')};

                let count = document.getElementsByName('downloadAgreement').length;
                if (count > 0) {
                    let newForm = document.getElementsByName('downloadAgreement')[0];
                    newForm.submit();
                } else {
                    let newForm = document.createElement('form');
                    newForm.name = 'downloadAgreement';
                    newForm.method = 'POST';
                    newForm.action = document.location.href;
                    newForm.target = '_blank';

                    let input1 = document.createElement('input');
                    let input2 = document.createElement('input');

                    input1.setAttribute("type", "hidden");
                    input1.setAttribute("name", "purpose");
                    input1.setAttribute("value", mainScript._purpose);

                    input2.setAttribute("type", "hidden");
                    input2.setAttribute("name", "orderIdx");
                    input2.setAttribute("value", data['orderIdx']);

                    newForm.appendChild(input1);
                    newForm.appendChild(input2);

                    document.body.appendChild(newForm);
                    newForm.submit();
                }
            });
        });
    },
};
