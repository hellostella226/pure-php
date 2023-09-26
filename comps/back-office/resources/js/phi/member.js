let mainScript = {
    _purpose: 'Members',
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
        if (typeof document.location.href.split('/')[4] !== 'undefined' && document.location.href.split('/')[4] !== '') {
            this._purpose = document.location.href.split('/')[4];
        }
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
                    case 'Members' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        break;
                }
            }
        }
    },
    setEventListener: function () {
        const getAddress = function (target, targetValue = '') {
            let data = "";
            let sendData = '';
            if (target === "city") {
                data = document.getElementById("state").value;
                sendData = mainScript.dataset({'cd': data});
            }

            let addressPromise = new Promise((resolve, reject) => {
                sendRequest("POST", "/abc/process/address.php", sendData, (response) => {
                    createAddressData(response, target, targetValue);
                }, function () {
                    reject();
                });
            });
            addressPromise.catch(() => {
                sendRequest("POST", "/abc/process/address.php", sendData, (response) => {
                    createAddressData(response, target, targetValue);
                });
            });
        }
        const createAddressData = (response, target, targetValue) => {
            let address = response.data;
            if (target === "state") {
                document.getElementById(target).innerHTML = "<option value=''>시/도 선택</option>";
            } else {
                document.getElementById(target).innerHTML = "<option value=''>구/군 선택</option>";
            }
            address.forEach(function (value, key) {
                let selected = "";
                if (value.name === targetValue) {
                    selected = "selected";
                }
                document.getElementById(target).innerHTML += `<option value="${value.cd}" ${selected}>${value.name}</option>`;
            });
        }
        getAddress('state');
        document.getElementById("state").addEventListener("change", e => {
            getAddress('city');
        });

        document.getElementsByName('data-modify').forEach(function (btn) {
            btn.addEventListener('click', function () {
                let tr = btn.closest('tr');
                document.getElementById('orderIdx').value = btn.getAttribute('data-order');
                document.getElementById('MembersIdx').value = btn.getAttribute('data-value');
                document.getElementById('UsersIdx').value = tr.childNodes[4].textContent;
                document.getElementById('inputName').value = tr.childNodes[5].textContent;
                document.getElementById('inputEmail').value = tr.childNodes[10].textContent;
                let state = tr.childNodes[11].textContent.split(' ')[0].trim();
                document.getElementById('state').childNodes.forEach(e => {
                    if (e.innerText === state) {
                        e.selected = true;
                    }
                });
                getAddress('city', tr.childNodes[11].textContent.replace(state, '').trim());

                const modalEl = document.querySelector('#MembersEditModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });
        });

        //모달 닫기
        const closeBtn = document.querySelector('.closeModal');
        closeBtn.addEventListener('click', function () {
            document.getElementById('city').value= "";
            const modalEl = document.querySelector('#MembersEditModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.hide();
        })
    },
    rendering: function (data, num) {
        deleteElement('adminTable');
        let year = new Date().getFullYear();
        const tbl = document.getElementById('adminTable');
        Object.keys(data).forEach(function(key) {
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
                let userDate = new Date(data[key]['Birth1'] + '-' + data[key]['Birth2'].substring(0, 2) + '-' + data[key]['Birth2'].substring(2, 4));
                let age = year - parseInt(data[key]['Birth1']);
                let d = new Date();
                if (userDate.getMonth() === d.getMonth()) {
                    if (userDate.getDate() > d.getDate()) {
                        age--;
                    }
                } else if (userDate.getMonth() > d.getMonth()) {
                    age--;
                }
                const state = data[key]['State'] ? data[key]['State'] : '';
                const city = data[key]['City'] ? data[key]['City'] : '';
                const fullCity = data[key]['FullCity'] ? data[key]['FullCity'] : '';
                let values = {
                    'RegDatetime': data[key]['RegDatetime'],
                    'Category': data[key]['Category'] === 'H' ? '병원' : '약국',
                    'ClientCustomerName': data[key]['ClientCustomerName'],
                    'UsersIdx': data[key]['UsersIdx'],
                    'Name': data[key]['Name'],
                    'Birth': data[key]['Birth1'] + data[key]['Birth2'],
                    'Age': age,
                    'Gender': data[key]['Gender'],
                    'Phone': data[key]['Phone'],
                    'Email': data[key]['Email'],
                    'State': `${state} ${city} ${fullCity}`.trim(),
                    'OrderIdx' : data[key]['OrderIdx'],
                    'Options': ''
                };
                for (let k in values) {
                    if (k === 'OrderIdx') continue;
                    let cell = document.createElement("td");
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                    if (k === 'Options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('수정');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.setAttribute('data-value', data[key]['MembersIdx']);
                        cell2.setAttribute('data-order', data[key]['OrderIdx']);
                        cell2.setAttribute('name', 'data-modify');
                        cell2.appendChild(cellText2);
                        cell.appendChild(cell2);
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            });
        tbl.setAttribute("border", "2");
    },
    register : function (data) {
        let state = document.getElementById("state");
        let city = document.getElementById("city");
        data.state = state.options[state.selectedIndex].innerText;
        data.city = city.options[city.selectedIndex].innerText;
        mainScript._methodType = 'POST';
        mainScript.request(data);
    }
};
