let mainScript = {
    _purpose: 'telephone',
    _methodType: 'get',
    _search: {
        'keyword': '',
        'value': '',
        'entry': 50, //출력 리밋
        'page': 1, //현재 페이지
        'column': '',
        'sort': '',
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
                    alert('등록되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'telephone' :
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
            for (let key in data) {
                const option = document.createElement('option');
                option.text = data[key]['text'];
                option.value = data[key]['value'];
                target.appendChild(option);
            }
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'telephone' :
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                let data = response.data;
                const productIdx = data.getAttribute('data-prod-idx');
                const orderIdx = data.getAttribute('data-order-idx');
                const UsersIdx = data.getAttribute('data-mem-idx');
                const MembersName = data.getAttribute('data-mem-name');
                const consultAgree = data.getAttribute('data-cons-agree') === 'Y';
                const appointmentDay = data.getAttribute('data-app-day');
                const appointmentHour = data.getAttribute('data-app-hour');
                document.getElementById('productIdx').value = productIdx;
                document.getElementById('orderIdx').value = orderIdx;
                document.getElementById('UsersIdx').value = UsersIdx;
                document.getElementById('MembersName').value = MembersName;
                document.getElementById('consultAgree').value = data.getAttribute('data-cons-agree');
                document.getElementById('consultAgree').checked = consultAgree;
                document.getElementById('appointmentDay').value = appointmentDay;
                document.getElementById('appointmentHour').value = appointmentHour;
                selector = '.updateConsultingData';
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

                //테스트 계정인 경우 별색
                if (data[key]['TestMembers'] !== null) {
                    row.className += 'test-Members';
                }
                let appointmentDay;
                switch (data[key]['AppointmentDay']) {
                    case '1':
                        appointmentDay = '평일';
                        break;
                    case '6':
                        appointmentDay = '주말';
                        break;
                    case '8':
                        appointmentDay = '항상가능';
                        break;
                    default:
                        appointmentDay = '';
                        break;
                }
                let appointmentHour;
                switch (data[key]['AppointmentHour']) {
                    case '10':
                    case '11':
                    case '12':
                    case '13':
                    case '14':
                    case '15':
                    case '16':
                    case '17':
                        appointmentHour = `${data[key]['AppointmentHour']}시`;
                        break;
                    case '18':
                        appointmentHour = `${data[key]['AppointmentHour']}시 이후`;
                        break;
                    default:
                        appointmentHour = '';
                        break;
                }
                let values = {
                    'regDatetime': data[key]['RegDatetime'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'name': data[key]['Name'],
                    'phone': data[key]['Phone'],
                    'statusCode': data[key]['StatusCode'] !== '20000' ? 'Y' : '-',
                    'agreeYN': data[key]['ALL_AGRE_YN'] ?? 'N',
                    'appointmentDay': appointmentDay,
                    'appointmentHour': appointmentHour,
                    'options': '',
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    if (k === 'options') {
                        if (values['statusCode'] === 'Y') {
                            let cell2 = document.createElement("button");
                            let cellText2 = document.createTextNode('수정');
                            cell2.className += 'btn btn-sm btn-info';
                            cell2.name = 'data-modify';
                            cell2.setAttribute('data-prod-idx', 4);
                            cell2.setAttribute('data-order-idx', data[key]['OrderIdx']);
                            cell2.setAttribute('data-mem-idx', data[key]['UsersIdx']);
                            cell2.setAttribute('data-mem-name', data[key]['Name']);
                            cell2.setAttribute('data-cons-agree', data[key]['ALL_AGRE_YN']);
                            cell2.setAttribute('data-app-day', data[key]['AppointmentDay'] ?? '');
                            cell2.setAttribute('data-app-hour', data[key]['AppointmentHour'] ?? '');
                            cell2.appendChild(cellText2);
                            cell.appendChild(cell2);
                        }
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
                mainScript.setModal(response);
            });
        });
        tbl.setAttribute("border", "2");
    },
};
