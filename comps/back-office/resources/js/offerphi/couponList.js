let mainScript = {
    _purpose: 'couponList', //controller 요청 목적
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
                    if(response.msg) {
                        alert(response.msg);
                    }
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'couponList' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.setEventListener(mainScript._purpose);
                        mainScript.rendering(data.data, data.pagination.start);
                        adminScript.pagination(data.pagination);
                        break;
                    default : //catalogList,searchProductGroupName,searchProductItem
                        // mainScript.setModal(response);
                        break;
                }
                for (let key in data) {
                    if (key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }

            } else {
                if (response.message) {
                    alert(response.message);
                }
                return;
                if (mainScript._methodType === 'POST') {
                    location.reload();
                }
                return;
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
            case 'couponList' :
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if(mainScript._purpose === '') {
                    selector = '#';
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
//        console.log(data.size);

        Object.keys(data).reverse().forEach(function(key) {
            num++;
            let checkNum = 1;
            if(Object.keys(data).length > 0) {
              checkNum = Object.keys(data).length-num+1;
            }
            const row = document.createElement("tr");

            let expiredName = '';
            switch (data[key]['expiredType']) {
                case '1' :
                    expiredName = '사용완료';
                    break;
                case '2' :
                    expiredName = '만료';
                    break;
                case '3' :
                    expiredName = '환불';
                    break;
                case '4' :
                    expiredName = '-';
                    break;
            }
            let values = {
                'num': checkNum,
                'couponCode': data[key]['couponCode'],
                'couponName': data[key]['couponName'],
                'clientCustomerName': data[key]['clientCustomerName'],
                'serviceCompanyName': data[key]['serviceCompanyName'],
                'issuedDatetime': (data[key]['issuedDatetime'] !== '') ? data[key]['issuedDatetime'] : '',
                'expiredDatetime': (data[key]['expiredDatetime'] !== '') ? data[key]['expiredDatetime'] : '',
                'expiredName': expiredName,
/*                'options': '',*/
            };

            for (let k in values) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode(values[k]);
                cell.appendChild(cellText);
/*                if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('수정');
                    cell2.className += 'btn btn-sm btn-success';
                    cell2.setAttribute('name', 'data-modify');
                    cell2.setAttribute('data-value', data[key]['expiredCouponIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                }*/
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        });
        tbl.setAttribute("border", "2");
    },
    register: function (data) {
        switch(mainScript._purpose){
            default :
                console.log('처리 logic 없음');
                break;
        }
        return false;
    },
};