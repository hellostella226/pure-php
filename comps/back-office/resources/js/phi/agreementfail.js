let mainScript = {
    _purpose: 'agreementFail', //controller 요청 목적
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
        let data;
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
                    case 'agreementFail' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.setEventListener(mainScript._purpose);
                        mainScript.rendering(response.data.data, response.data.pagination.start);
                        adminScript.pagination(response.data.pagination);
                        break;
                    default :
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
                    'ClientControlIdx': data[key]['ClientControlIdx'],
                    'ClientCustomerName': data[key]['ClientCustomerName'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'Name': data[key]['Name'],
                    'RegDatetime': data[key]['RegDatetime'],
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            });
        tbl.setAttribute("border", "2");
    },
};
