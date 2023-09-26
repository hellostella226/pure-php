let mainScript = {
    _purpose: 'payment', //controller 요청 목적
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
                    alert('완료되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'payment' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        adminScript.pagination(data.pagination);
                        break;
                    default : //searchPayment
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
            case 'payment' :
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

                // 결제취소 Modal
                const payRefundBtn = document.getElementById('payRefundBtn');
                payRefundBtn.addEventListener('click', function (e) {
                    const idxTemp = document.querySelector("input[name='data-select']:checked");
                    if (!idxTemp) {
                        alert('결제취소 대상이 선택되지 않았습니다.');
                        return false;
                    }
                    let idx = idxTemp.getAttribute('data-value');

                    document.querySelector("#payRefund #refundType2").removeAttribute("checked");
                    document.querySelector("#payRefund #refundType1").setAttribute("checked", true);

                    mainScript._purpose = 'searchPayment';
                    mainScript._methodType = 'get';
                    let data = {
                        'payOrderIdx': idx,
                    }
                    mainScript.request(data);
                });

                // 취소구분 자동설정
                const refundTypeRadioReadonly = document.querySelectorAll("input.readonly");
                refundTypeRadioReadonly.forEach(function (btn) {
                    btn.setAttribute('onclick', 'return false');
                });
                // 결제취소 modal 이벤트 설정
                const orderQuantityBtn = document.querySelector("#payRefund input[name='orderQuantity']");
                orderQuantityBtn.addEventListener('change', function (e) {
                    let refundQuantity = parseInt(e.target.value);

                    // 취소구분 radio 자동설정
                    const remainingQuantity = parseInt(document.querySelector("#payRefund #remainOrderQuantity").innerHTML);
                    const paidOrderQuantity = parseInt(document.querySelector("#payRefund #orderQuantity").value);
                    if (remainingQuantity >= paidOrderQuantity) {
                        if (refundQuantity < remainingQuantity) {
                            document.querySelector("#payRefund #refundType1").removeAttribute("checked");
                            document.querySelector("#payRefund #refundType2").setAttribute("checked", true);
                        } else {
                            document.querySelector("#payRefund #refundType2").removeAttribute("checked");
                            document.querySelector("#payRefund #refundType1").setAttribute("checked", true);
                        }
                    }

                    // 결제취소금액 자동계산
                    const salesPrice = parseInt(document.querySelector("#payRefund input[name='salesPrice']").value);

                    // 1 - 할인율 ; 2 - 할인가
                    const discountMethod = document.querySelector("#payRefund input[name='discountMethod']").value;
                    const discount = parseFloat(document.querySelector("#payRefund input[name='discount']").value) ?? 0;

                    let refundAmount = refundQuantity * salesPrice;
                    let remainingAmount = (remainingQuantity * salesPrice) - refundAmount;
                    if (discountMethod === '1') {
                        refundAmount = refundQuantity * salesPrice * discount;
                        remainingAmount = (remainingQuantity * salesPrice * discount) - refundAmount;
                    } else if (discountMethod === '2') {
                        refundAmount = refundQuantity * salesPrice;
                        remainingAmount = (remainingQuantity * salesPrice) - refundAmount - discount;
                    }
                    document.querySelector("#payRefund input[name='orderAmount']").value = refundAmount;
                    document.querySelector("#payRefund input[name='remainingAmount']").value = remainingAmount < 0 ? 0 : remainingAmount;
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
                if (mainScript._purpose === 'searchPayment') { //굿즈 조회(수정)
                    // modal 대상
                    selector = '#payRefund';
                    document.querySelector("#payRefund input[name='payOrderIdx']").value = data.payOrderIdx;
                    document.querySelector("#payRefund #orderQuantity").value = data.PaysQuantity;
                    document.querySelector("#payRefund input[name='salesPrice']").value = data.salesPrice;
                    document.querySelector("#payRefund input[name='couponCode']").value = data.couponCode;
                    document.querySelector("#payRefund input[name='discountMethod']").value = data.discountMethod;
                    let discount;
                    let remainingAmount = parseInt(data.remainOrderQuantity) * parseInt(data.salesPrice);
                    if (data.discountMethod === '1') {
                        discount = 1 - (parseInt(data.discountRate) / 100);
                        remainingAmount = parseInt(data.remainOrderQuantity) * parseInt(data.salesPrice) * discount
                    } else if (data.discountMethod === '2') {
                        discount = parseInt(data.discountAmount);
                        remainingAmount = parseInt(data.remainOrderQuantity) * parseInt(data.salesPrice) - discount
                    }
                    document.querySelector("#payRefund input[name='discount']").value = discount;
                    document.querySelector("#payRefund input[name='totalDiscountAmount']").value = data.totalDiscountAmount;
                    document.querySelector("#payRefund input[name='approvedOrderAmount']").value = data.approvedOrderAmount;
                    document.querySelector("#payRefund input[name='kcpTno']").value = data.kcpTno;
                    document.querySelector("#payRefund input[name='payType']").value = data.payType;
                    document.querySelector("#payRefund #payOrderCode").innerHTML = data.payOrderCode;
                    document.querySelector("#payRefund #payMethod").innerHTML = data.payMethod;
                    document.querySelector("#payRefund #goodsName").innerHTML = data.goodsName;
                    document.querySelector("#payRefund #approvedOrderAmount").innerHTML = data.approvedOrderAmount;
                    document.querySelector("#payRefund #serviceCompanyName").innerHTML = data.serviceCompanyName;
                    document.querySelector("#payRefund #companyName").innerHTML = data.companyName;
                    document.querySelector("#payRefund #buyerName").innerHTML = data.buyerName;
                    document.querySelector("#payRefund #approvedDatetime").innerHTML = data.approvedDatetime;
                    document.querySelector("#payRefund #remainOrderQuantity").innerHTML = data.remainOrderQuantity;
                    document.querySelector("#payRefund input[name='orderQuantity']").value = data.remainOrderQuantity;
                    document.querySelector("#payRefund input[name='orderQuantity']").setAttribute("max", data.remainOrderQuantity);
                    document.querySelector("#payRefund input[name='orderAmount']").value = remainingAmount;
                    document.querySelector("#payRefund input[name='remainingAmount']").value = 0;

                    if (data.remainOrderQuantity < data.PaysQuantity) {
                        document.querySelector("#payRefund #refundType1").removeAttribute("checked");
                        document.querySelector("#payRefund #refundType2").setAttribute("checked", true);
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
        for (let key in data) {
            num++;
            const row = document.createElement("tr");
            let values = {
                'selector': '',
                'num': num,
                'payOrderIdx': data[key]['PayOrderIdx'],
                'relatedPayOrderIdx': data[key]['RelatedPayOrderIdx'],
                'payOrderCode': data[key]['PayOrderCode'],
                'payMethod': data[key]['PayMethod'],
                'ItemsIdx': data[key]['ItemsIdx'],
                'goodsName': data[key]['GoodsName'],
                'orderAmount': data[key]['OrderAmount'],
                'serviceCompanyName': data[key]['ServiceCompanyName'],
                'companyName': data[key]['CompanyName'],
                'buyerName': data[key]['BuyerName'],
                'buyerPhone': data[key]['BuyerPhone'],
                'approvedDatetime': data[key]['ApprovedDatetime'],
                'orderStatus': data[key]['OrderStatus'],
                'orderType': data[key]['OrderType'],

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
                    cell2.setAttribute('data-value', data[key]['PayOrderIdx']);

                    cell.appendChild(cell2);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        }
        tbl.setAttribute("border", "2");
    }
};