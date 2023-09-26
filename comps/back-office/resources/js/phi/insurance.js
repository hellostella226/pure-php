let mainScript = {
    _purpose : 'insurance', //controller 요청 목적
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
                    case 'insurance' :
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
        //셀렉트박스 세팅
        if(type === 'select') {
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
    setEventListener: function(purpose) {
        switch (purpose) {
            case 'supplement' :
                break;
            default :
                break;
        }
    },
    setModal : function(response) {
        if(response) {
            if(response.code === '20200') {
                let data = response.data;
                document.getElementById('ServiceControl').value = data.ServiceControlIdx;
                document.getElementById('ServeAllocationHistoryIdx').value = data.ServeAllocationHistoryIdx;
                document.getElementById('transferMethod').value = data.TransferMethodCode;
                document.getElementById('isPilot').value = (data.AllocationServeType === 'pilot') ? 1 : 0;
                document.getElementById('isManual').value = data.IsManual
                document.getElementById('totalServeLimit').value = data.TotalServeLimit;
                document.getElementById('weekServeLimit').value = data.WeekServeLimit;

                selector = '.insuranceUpdate';
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

        Object.keys(data).forEach(function(key) {
            num++;
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            const indexCellText = document.createTextNode(num);
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);

            let values = {
                'regDate' : data[key]['RegDatetime'],
                'serviceCompanyName' : data[key]['ServiceCompanyName'],
                'transferMethodCode': (data[key]['TransferMethodCode'] === '1') ? "API" : "수동",
                'totalServeLimit' : data[key]['TotalServeLimit'],
                'WeekServeLimit': data[key]['WeekServeLimit'],
                'options' : ""
            };

            for(let k in values) {
                let cell = document.createElement("td");
                let cellText = document.createTextNode(values[k]);
                if (k === 'options') {
                    let cell2 = document.createElement("button");
                    let cellText2 = document.createTextNode('수정');
                    cell2.className += 'btn btn-sm btn-success';
                    cell2.setAttribute('name', 'data-modify');
                    cell2.setAttribute('data-value', data[key]['ServeAllocationHistoryIdx']);
                    cell2.appendChild(cellText2);
                    cell.appendChild(cell2);
                }
                cell.appendChild(cellText);
                row.appendChild(cell);
            }
            tbl.appendChild(row);
        });

        tbl.setAttribute("border", "2");


        //수정 버튼 클릭시
        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                mainScript._purpose = 'searchInsurance';
                let data = {'ServeAllocationHistoryIdx': this.getAttribute('data-value')};
                mainScript.request(data);
            });
        });


    },
};