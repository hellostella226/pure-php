let mainScript = {
    _purpose: 'registCoupon', //controller 요청 목적
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
                    if(response.message) {
                        alert(response.message);
                    }
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'searchTicketsData':
                        mainScript.setModal(response);
                        break;
                    case 'searchConsultantId' :
                        if(!data.ClientControlIdx) {
                            alert('검색한 조건과 일치하는 상담사가 존재하지 않습니다.');
                            document.getElementById('consultantIdx').value = null;
                            document.getElementById('consultantId').value = '';
                        } else {
                            alert('적용 상담사명: ' + data.ClientCustomerName);
                            document.getElementById('consultantIdx').value = data.ClientControlIdx;
                        }
                        break;
                    case 'registCoupon' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        mainScript.setEventListener(mainScript._purpose);
                        mainScript.rendering(data.data, data.pagination.start);
                        adminScript.pagination(data.pagination);
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
            case 'registCoupon' :
                break;
            default :
                break;
        }

        // 초기화 버튼 클릭시 라디오버튼 정렬
        const initBtn = document.querySelector('.modal-init-btn');
        if(initBtn) {
            initBtn.addEventListener('click', e=> {
                let couponType = document.getElementsByName('couponType');
                let i = 0;
                couponType.forEach(e => {
                    if(i === 0) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                    i ++;
                });

                let discountMethod = document.getElementsByName('discountMethod');
                i = 0;
                discountMethod.forEach(e => {
                    if(i === 0) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                    i ++;
                });

                let couponStatus = document.getElementsByName('couponStatus');
                i = 0;
                couponStatus.forEach(e => {
                    if(i === 0) {
                        e.checked = true;
                    } else {
                        e.checked = false;
                    }
                    i ++;
                });

                document.getElementById('consultantIdx').value = null;

            });
        }


        // 모달 내에서 ajax 검색
        const innerSearchBtn = document.getElementById('innerSearchBtn');
        if(innerSearchBtn) {
            // ajax 검색부터 할 차례!!
            innerSearchBtn.addEventListener('click', e => {
                e.preventDefault();
                const parentClientControlIdx = document.getElementById('serviceCompany').value;
                const clientCustomerCode = document.getElementById('consultantId').value;
                if (!parentClientControlIdx) {
                    alert('상담사의 소속사를 선택하세요');
                    return false;
                }
                if (!clientCustomerCode) {
                    alert('검색할 상담사 ID를 입력하세요');
                    return false;
                }

                mainScript._purpose = 'searchConsultantId';
                mainScript._methodType = 'get';

                let data = {
                    parentClientControlIdx: parentClientControlIdx,
                    clientCustomerCode: clientCustomerCode,
                };

                mainScript.request(data);
            })
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                if(mainScript._purpose === 'searchTicketsData') {
                    selector = '#couponRegist';

                    document.getElementById('TicketsIdx').value = data.TicketsIdx;

                    let couponType = document.getElementsByName('couponType');
                    for (let radio of couponType) {
                        if (radio.value === data.couponType) {
                            radio.checked = true;
                        }
                    }

                    document.getElementById('couponName').value = data.couponName;

                    let discountMethod = document.getElementsByName('discountMethod');
                    for (let radio of discountMethod) {
                        if (radio.value === data.discountMethod) {
                            if (data.discountMethod === '1') {
                                document.getElementById('amount').value = data.discountRate;
                            } else {
                                document.getElementById('amount').value = data.discountAmount;
                            }
                            radio.checked = true;
                        }
                    }

                    document.getElementById('serviceCompany').value = data.ServiceControlIdx;
                    document.getElementById('consultantId').value = data.clientCustomerCode;
                    document.getElementById('consultantIdx').value = data.ClientControlIdx;
                    document.getElementById('useStartDate').value = data.useStartDate.replaceAll('-','');
                    document.getElementById('useEndDate').value = data.useEndDate.replaceAll('-','');

                    let couponStatus = document.getElementsByName('couponStatus');
                    for (let radio of couponStatus) {
                        if (radio.value === data.couponStatus) {
                            radio.checked = true;
                        }
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
        Object.keys(data).reverse().forEach(function(key) {
            num++;
            let checkNum = 1;
            if(Object.keys(data).length > 0) {
                checkNum = Object.keys(data).length-num+1;
            }
            const row = document.createElement("tr");
            let values = {
                'num': checkNum,
                'regDatetime': data[key]['regDatetime'],
                'modDatetime': (data[key]['modDatetime'] !== '') ? data[key]['modDatetime'] : '',
                'couponCode': data[key]['couponCode'],
                'couponName': data[key]['couponName'],
                'serviceCompanyName': data[key]['serviceCompanyName'],
                'couponStatus': (data[key]['couponStatus'] === '1')? '사용' : '미사용',
                'options': '',
            };

            for (let k in values) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode(values[k]);
                cell.appendChild(cellText);
                if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('수정');
                    cell2.className += 'btn btn-sm btn-success';
                    cell2.setAttribute('name', 'data-modify');
                    cell2.setAttribute('data-value', data[key]['TicketsIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                }
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        });
        tbl.setAttribute("border", "2");

        //수정 버튼 클릭시
        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                mainScript._methodType = 'get';
                mainScript._purpose = 'searchTicketsData';
                let data = {'TicketsIdx': this.getAttribute('data-value')};
                mainScript.request(data);
            });
        });

    },
    register: function (data) {
        switch(mainScript._purpose){
            case "couponRegist":
                let msg = '쿠폰을 발행하시겠습니까?';
                if(document.getElementById('TicketsIdx').value !== '') {
                    msg = '쿠폰 정보를 수정하시겠습니까?\n쿠폰을 지급받은 유저가 있는 경우,\n해당 유저에게 지급된 쿠폰 정보도 변경됩니다.';
                }
                if (confirm(msg)) {
                    mainScript._methodType = 'POST';
                    mainScript.request(data);
                }
                break;
            default :
                console.log('처리 logic 없음');
                break;
        }
        return false;
    },
};