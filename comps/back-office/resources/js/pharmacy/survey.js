let mainScript = {
    _purpose: 'survey', //controller 요청 목적
    _methodType: 'get', //method 타입
    _search: { //pagination 관련 값
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
                    alert('등록되었습니다.');
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'survey' :
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
                    if (key.search('::') !== -1) {
                        mainScript.setForm(key, data[key]);
                    }
                }

            } else {
                alert(response.message);
                location.reload();
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
            for (let key in data) {
                if (document.querySelector(`.survey_${key}`)) {
                    continue;
                }
                const option = document.createElement('option');
                option.className += `survey_${key}`;
                option.text = data[key]['text'];
                option.value = data[key]['value'];
                target.appendChild(option);
            }
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'survey' :
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (response) {
            if (response.code === '20200') {
                document.getElementById('question1').value = '';
                document.getElementById('question2').value = '';
                document.getElementById('question3').value = '';
                document.getElementById('question4').value = '';
                let data = response.data;
                const productIdx = data.getAttribute('data-prod-idx');
                const orderIdx = data.getAttribute('data-order-idx');
                const UsersIdx = data.getAttribute('data-mem-idx');
                const MembersName = data.getAttribute('data-mem-name');
                document.getElementById('productIdx').value = productIdx;
                document.getElementById('orderIdx').value = orderIdx;
                document.getElementById('UsersIdx').value = UsersIdx;
                document.getElementById('MembersName').value = MembersName;
                selector = '.updateSurveyData';
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
        Object.keys(data).reverse().forEach(function (key) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);

            const outStatus = data[key]['OutStatus'];
            let eventDate = '';
            if (outStatus === 'N') { //이탈자의 경우 빈칸으로 표시, 기획안 26p
                eventDate = data[key]['EventDate'];
            }
            const productIdx = data[key]['ProductIdx'];

            let values = {
                'calcDate': data[key]['CalcDate'],
                'eventDate': eventDate,
                'UsersIdx': data[key]['UsersIdx'],
                'name': data[key]['Name'],
                'phone': data[key]['Phone'],
                'outStatus': outStatus,
                'content1': '',
                'content2': '',
                'content3': '',
                'content4': '',
                'option': key,
            };
            // 컨텐츠 입력
            if (Object.values(data[key]['Survey'])) {
                values['content1'] = data[key]['Survey']['1'] ?? '';
                values['content2'] = data[key]['Survey']['2'] ?? '';
                values['content3'] = data[key]['Survey']['3'] ?? '';
                values['content4'] = data[key]['Survey']['4'] ?? '';
            }

            for (let k in values) {
                let cell = document.createElement("td");
                if (k === 'option') {
                    if (!eventDate) { //예약 완료자의 경우 수정 불가함
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('수정');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.name = 'data-modify';
                        cell2.setAttribute('data-prod-idx', productIdx);
                        cell2.setAttribute('data-order-idx', key);
                        cell2.setAttribute('data-mem-idx', data[key]['UsersIdx']);
                        cell2.setAttribute('data-mem-name', data[key]['Name']);
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