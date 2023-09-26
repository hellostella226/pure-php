let mainScript = {
    _purpose: 'goods', //controller 요청 목적
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
                    alert('완료되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'search' :
                    case 'goods' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        adminScript.pagination(data.pagination);
                        break;
                    default : //searchGoods
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
            case 'goods' :
                // 선택 checkbox 하나씩만 선택가능
                document.querySelectorAll("input[name='data-select']").forEach(function (el) {
                    el.addEventListener('click', event => {
                        if (event.target.checked === true) {
                            const checkedPay = document.querySelectorAll("input[name='data-select']:checked");
                            if (checkedPay) {
                                checkedPay.forEach(function (el) {
                                    el.checked = false;
                                });
                            }
                            event.target.checked = true;
                        }
                    });
                });

                // 등록 Modal
                const regiGoods = document.getElementById("registerGoods");
                regiGood.addEventListener('click', function (e) {
                    document.querySelector("#Items input[name='ItemsType']").value = 'register';
                    document.querySelector("#Items #goodsModalTitle").innerHTML = "결제경로 신규 등록";
                    document.querySelector("#Items .regist-btn").innerHTML = "등록";

                    const modalEl = document.querySelector("#Items");
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                });
                // 수정 Modal
                const editGoods = document.getElementById('editGoods');
                editGood.addEventListener('click', function (e) {
                    const idxTemp = document.querySelector("input[name='data-select']:checked");
                    if (!idxTemp) {
                        alert('수정대상이 선택되지 않았습니다.');
                        return false;
                    }
                    let idx = idxTemp.getAttribute('data-value');

                    mainScript._purpose = 'searchGoodsEdit';
                    mainScript._methodType = 'get';
                    let data = {
                        'ItemsIdx': idx,
                    }
                    mainScript.request(data);
                });
                // 삭제 Modal
                const delGoods = document.getElementById('deleteGoods');
                delGood.addEventListener('click', function (e) {
                    const idxTemp = document.querySelector("input[name='data-select']:checked");
                    if (!idxTemp) {
                        alert('수정대상이 선택되지 않았습니다.');
                        return false;
                    }
                    let idx = idxTemp.getAttribute('data-value');

                    mainScript._purpose = 'searchGoodsDel';
                    mainScript._methodType = 'get';
                    let data = {
                        'ItemsIdx': idx,
                    }
                    mainScript.request(data);
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
                if (mainScript._purpose === 'searchGoodsEdit') { //굿즈 조회(수정)
                    // modal 대상
                    selector = '#Items';
                    document.querySelector("#Items input[name='ItemsIdx']").value = data.ItemsIdx;
                    document.querySelector("#Items input[name='ItemsType']").value = 'edit';
                    document.querySelector("#Items #serviceCompany").value = data.ServiceControlIdx;
                    document.querySelector("#Items #goodsName").value = data.goodsName;
                    document.querySelector("#Items input[name='salesPrice']").value = data.salesPrice;
                    document.querySelector("#Items #goodsModalTitle").innerHTML = "기존 결제 경로 수정";
                    document.querySelector("#Items .regist-btn").innerHTML = "수정";
                }
                if (mainScript._purpose === 'searchGoodsDel') { //굿즈 조회(삭제)
                    // modal 대상
                    selector = '#goodsDelete';
                    if (document.querySelector("#goodsInfo ul")) {
                        document.querySelector("#goodsInfo ul").remove();
                    }
                    const goodsInfo = document.getElementById("goodsInfo");
                    const ul = document.createElement("ul");
                    const li1 = document.createElement("li");
                    li1.innerText = `등록일자 :  ${data.regDatetime}`;
                    ul.appendChild(li1);
                    const li2 = document.createElement("li");
                    li2.innerText = `굿즈코드 :  ${data.ItemsIdx}`;
                    ul.appendChild(li2);
                    const li3 = document.createElement("li");
                    li3.innerText = `사용처 :  ${data.serviceCompanyName}`;
                    ul.appendChild(li3);
                    const li4 = document.createElement("li");
                    li4.innerText = `굿즈명 :  ${data.goodsName}`;
                    ul.appendChild(li4);
                    const li5 = document.createElement("li");
                    li5.innerText = `계약단가 :  ${data.salesPrice}`;
                    ul.appendChild(li5);

                    goodsInfo.appendChild(ul);

                    document.querySelector("#goodsDelete input[name='ItemsIdx']").value = data.ItemsIdx;
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
            const row = document.createElement("tr");

            let frontLocation = document.location.origin.replace("admin","ds");
            let values = {
                'selector': '',
                'regDatetime': data[key]['RegDatetime'],
                'ItemsIdx': data[key]['ItemsIdx'],
                'serviceCompanyName': data[key]['ServiceCompanyName'],
                'goodsName': data[key]['GoodsName'],
                'salesPrice': data[key]['SalesPrice'],
                'url': `${frontLocation}/pay/?gCode=${data[key]['ItemsIdx']}`,
            };

            for (let k in values) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode(values[k]);
                cell.appendChild(cellText);
                if (k === 'selector') {
                    cell.className += "text-center";
                    let cell2 = document.createElement("input");
                    cell2.type = "checkbox";
                    cell2.name = "data-select";
                    cell2.className += "form-check-input";
                    cell2.setAttribute('data-value', data[key]['ItemsIdx']);
                    cell.appendChild(cell2);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        }
        tbl.setAttribute("border", "2");
    },
    register : function (data) {
        if (mainScript._purpose === 'registGoods') {
            if (data.ItemsType === 'edit') {
                if (confirm('정말 수정하시겠습니까?')) {
                    mainScript._methodType = 'POST';
                    mainScript.request(data);
                }
            } else {
                if (confirm('정말 등록하시겠습니까?')) {
                    mainScript._methodType = 'POST';
                    mainScript.request(data);
                }
            }
        }

        if (mainScript._purpose === 'deleteGoods') {
            if (confirm('정말 삭제하시겠습니까?')) {
                mainScript._methodType = 'POST';
                mainScript.request(data);
            }
        }
    }
};