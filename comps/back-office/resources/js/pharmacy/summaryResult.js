let mainScript = {
    _purpose : 'summaryResult', //controller 요청 목적
    _methodType : 'get', //method 타입
    _search : { //pagination 관련 값
        'keyword' : '',
        'value' : '',
        'entry' : 50, //출력 리밋
        'page' : 1, //현재 페이지
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
                    case 'summaryResult' :
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

            } else {
                alert(response.message);
                location.reload();
                return false;
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
            case 'summaryResult' :
                break;
            default :
                break;
        }
    },
    setModal : function(response) {
        if(response) {
            if(response.code === '20200') {
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
        if(!data) {
            return;
        }
        Object.keys(data).reverse().forEach(function(key) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);

            let consultantType = data[key]['ConsultantType'];
            if(consultantType === 'R') {
                consultantType = '설명듣기';
            } else if (consultantType === 'L') {
                consultantType = '나중에';
            } else if (consultantType === 'N') {
                consultantType = '미응답';
            } else {
                consultantType = '미접속';
            }
            const appointmentHour = data[key]['AppointmentHour'] === '18' ?
                data[key]['AppointmentHour']+'시이후' : (data[key]['AppointmentHour']? data[key]['AppointmentHour']+'시' : '');
            let values = {
                'calcDate' : data[key]['CalcDate'],
                'clientCustomerName' : data[key]['ClientCustomerName'],
                'UsersIdx' : data[key]['UsersIdx'],
                'name' : data[key]['Name'],
                'phone' : data[key]['Phone'],
                'inflow' : data[key]['EventDate'] ? 'Y' : 'N',
                'eventDate' : data[key]['EventDate'],
                'consultantType' : consultantType,
                'appointmentDate' : data[key]['AppointmentDate'],
                'AppointmentHour' : appointmentHour,
            };

            for(let k in values) {
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