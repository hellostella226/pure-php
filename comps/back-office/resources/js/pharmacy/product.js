let mainScript = {
    _purpose: 'product', //controller 요청 목적
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
        let data = null;
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
        if (response) {
            if (response.code === '20200') {
                if (mainScript._methodType === 'POST') {
                    location.reload();
                    return;
                }
                const data = response.data;
                switch (mainScript._purpose) {
                    case 'item' :
                    case 'group' :
                        const table = document.getElementById('adminTable');
                        while (table.firstChild) {
                            table.removeChild(table.firstChild);
                        }
                        adminScript.pagination(data.pagination);
                        mainScript.rendering(data.data, data.pagination.start);
                        mainScript.setEventListener(mainScript._purpose);
                        //상품등록 모달 내 카테고리 셀렉트박스 설정(공통)
                        mainScript._purpose = 'groupList';
                        mainScript.request();
                        break;
                    case 'groupList' :
                        mainScript.setForm(response);
                        break;
                    default : //catalogList,searchProductGroupName,searchProductItem
                        mainScript.setModal(response);
                        break;
                }
            } else {
                if (response.message) {
                    alert(response.message);
                }
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
    setForm: function (response) {
        const category = document.querySelector('#category');
        let data = response.data;
        if(category.length === 1) {
            for (let key in data) {
                const optionSelect = document.createElement('option');
                optionSelect.text = data[key]['ProductName'];
                optionSelect.value = data[key]['ProductIdx'];
                category.appendChild(optionSelect);
            }
        }
        document.querySelector('#category')[0].selected = true;
        if (document.getElementById('childProduct')) {
            //상품그룹
            let j = 0;
            const tbl = document.getElementById('childProduct');
            tbl.innerHTML = '';
            for (let key in data) {
                for (let i = 0; i < data[key]['ChildProductName'].length; i++) {
                    const row = document.createElement("tr");
                    row.setAttribute('data-code', data[key]['ProductIdx']);
                    let values = {
                        'ChildProductIdx': data[key]['ChildProductIdx'][i],
                        'ProductName': data[key]['ProductName'],
                        'ChildProductName': data[key]['ChildProductName'][i],
                        'Options': '',
                    };
                    for (let k in values) {
                        let cell = document.createElement("td");
                        let cellText = document.createTextNode(values[k]);
                        cell.appendChild(cellText);
                        row.appendChild(cell);
                        if (k === 'Options') {
                            let cell2 = document.createElement("input");
                            cell2.className = 'childProductIdx';
                            cell2.name = 'childProductIdx[' + j + ']';
                            cell2.type = 'checkbox';
                            cell2.value = data[key]['ChildProductIdx'][i];
                            cell.appendChild(cell2);
                        }
                    }
                    j++;
                    tbl.appendChild(row);
                }
            }

            //체크박스 선택 이벤트
            let allCheckBox = document.querySelectorAll('.childProductIdx');
            allCheckBox.forEach((checkbox) => {
                checkbox.addEventListener('change', (e) => {
                    if (e.target.checked === true) {
                        adminScript.createBadge(e.target.value, e.target.closest('td').previousSibling.innerText, "productList");
                    } else {
                        adminScript.removeBadge(e.target.closest('td').previousSibling.innerText, "productList");
                    }
                })
            });

            //카테고리 셀렉트박스에 따른 테이블 디스플레이 변경
            category.addEventListener('input', function () {
                let selectedVal = this.value;
                // tr의 data-code와 일치하는애들을 show해주고 나머지 none 하면 됨. 여기서부터 할 차례
                document.getElementById('childProduct').childNodes.forEach(e => {
                    if (selectedVal === e.getAttribute('data-code')) {
                        e.style.display = '';
                    } else {
                        if (selectedVal !== '0') {
                            e.style.display = 'none';
                        } else {
                            e.style.display = '';
                        }
                    }
                });
            })

            // 상품그룹 등록버튼
            document.getElementById('registerProductGroupCloseBtn').addEventListener('click', e => {
                let productList = document.getElementById('productList');
                while (productList.firstChild) {
                    productList.removeChild(productList.firstChild);
                }
                let childProduct = document.querySelectorAll('.childProductIdx');
                childProduct.forEach(el => {
                    el.checked = false;
                })
            });
        }
    },
    setEventListener: function (purpose) {
        switch (purpose) {
            case 'searchProductGroupName' :
                //상품그룹 수정 EVENT
                if(!document.getElementById('productGroupEditBtn').getAttribute("data-click")) {
                    document.getElementById('productGroupEditBtn').setAttribute("data-click", true);
                    document.getElementById('productGroupEditBtn').addEventListener('click', e => {
                        let data = {};
                        data.productGroupIdx = document.getElementById('productGroupIdx').value;
                        data.productGroupName = document.getElementById('productGroupName_e').value;
                        mainScript._purpose = 'groupName';
                        mainScript._methodType = 'POST';
                        mainScript.request(data);
                    });
                }
                //상품그룹 삭제 EVENT
                if(!document.getElementById('productGroupDeleteBtn').getAttribute("data-click")) {
                    document.getElementById('productGroupDeleteBtn').setAttribute("data-click", true);
                    document.getElementById('productGroupDeleteBtn').addEventListener('click', e => {
                        let data = {};
                        data.productGroupIdx = document.getElementById('productGroupIdx').value;
                        mainScript._purpose = 'disableItemGroup';
                        mainScript._methodType = 'POST';
                        mainScript.request(data);
                    });
                }
                break;
            case 'searchProductItem' :
                document.querySelectorAll('.del-idx').forEach(function (element, i) {
                    element.addEventListener('click', function () {
                        let delIdx = parseInt(element.closest('span').getAttribute('data-code'));
                        let delIdxValue = document.getElementById('delIdx').value;
                        document.getElementById("delIdx").value = (!delIdxValue) ? delIdx : `${delIdxValue},${delIdx}`;
                    });
                });
                break;
            case 'item' :
                document.getElementsByName('removeAllCatalog').forEach(function (btn) {
                    if (!btn.getAttribute("data-click")) {
                        btn.setAttribute("data-click", true);
                        btn.addEventListener('click', function () {
                            if (confirm("정말 모든 항목들을 제거하시겠습니까?")) {
                                adminScript.deleteCatalogList();
                            } else {
                                return;
                            }
                        });
                    }
                });

                document.getElementsByName('addCatalog').forEach(function (element) {
                    if (!element.getAttribute("data-click")) {
                        element.setAttribute("data-click", true);
                        element.addEventListener('click', function () {

                            let catalogList;
                            let catalogCode;
                            let catalogName;
                            if (document.getElementById('catalogCode').value) {
                                catalogList = "catalogList";
                                catalogCode = document.getElementById('catalogCode').value;
                                catalogName = document.getElementById('catalogName').value;
                            }
                            adminScript.createBadge("", `${catalogCode}_${catalogName}`, catalogList);
                        });
                    }
                });
                if (!document.getElementById('removeBtn').getAttribute("data-click")) {
                    document.getElementById('removeBtn').setAttribute("data-click", true);
                    document.getElementById('removeBtn').addEventListener('click', function () {

                        let mode = this.getAttribute('data-value');
                        let data = {};
                        if (mode === 'item') {
                            data.productIdx = document.getElementById('productIdx').value;

                            if (confirm(`삭제 하시겠습니까?`)) {
                                mainScript._purpose = 'disableProduct';
                            } else {
                                return;
                            }
                        } else if (mode === 'group') {

                        }
                        mainScript._methodType = 'POST';
                        mainScript.request(data);
                        //모달 닫기
                        let modalEl = this.closest('div.modal');
                        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modal.hide();
                    });
                }
                break;
            case 'group' :
                document.getElementsByName('data-modify').forEach(function (btn) {
                    btn.addEventListener('click', function (e) {
                        mainScript._purpose = 'searchProductGroupName';
                        let data = {'productGroupIdx': this.getAttribute('data-value')};
                        mainScript.request(data);
                    });
                });
                document.getElementsByName('addCatalog').forEach(function (element) {
                    element.addEventListener('click', function () {

                        let catalogList;
                        let catalogCode;
                        let catalogName;
                        if (document.getElementById('catalogCode').value) {
                            catalogList = "catalogList";
                            catalogCode = document.getElementById('catalogCode').value;
                            catalogName = document.getElementById('catalogName').value;
                        }
                        adminScript.createBadge("", `${catalogCode}_${catalogName}`, catalogList);
                    });
                });
                break;
            default :
                break;
        }
    },
    setModal: function (response) {
        if (typeof response.code !== 'undefined') {
            if (response.code === '20200') {
                let data = response.data;
                if (mainScript._purpose === 'searchProductGroupName') { //상품명 조회
                    // modal 대상
                    selector = '#editProductGroup';
                    // modal input 세팅
                    document.getElementById('productGroupName_e').value = data.ProductGroupName;
                    document.getElementById('productGroupIdx').value = data.ProductGroupIdx;

                    // 이벤트 세팅
                    mainScript.setEventListener(mainScript._purpose);
                } else if (mainScript._purpose === 'searchProductItem') {
                    // modal 대상
                    selector = '#registerProduct';
                    // modal input 세팅
                    document.querySelector('#productIdx').value = data.ProductIdx;
                    document.querySelector('#productName').value = data.ProductName;
                    //카테고리 셀렉트박스 세팅
                    const category = document.getElementById('category');
                    for (let i = 0; i < category.children.length; i++) {
                        if (category.children[i].value === data.ParentProductIdx) {
                            category.children[i].setAttribute('selected', '')
                        }
                    }
                    const subdivision = document.getElementById('subdivision');
                    for (let i = 0; i < subdivision.children.length; i++) {
                        if (subdivision.children[i].value === data.Gender) {
                            subdivision.children[i].setAttribute('selected', '')
                        }
                    }
                    for (let key in data.CatalogName) {
                        adminScript.createBadge(data.ProductCatalogIdx[key], `${data.RefCode[key]}_${data.CatalogName[key]}`, 'catalogList');
                    }
                    //이벤트 세팅
                    mainScript.setEventListener(mainScript._purpose);
                } else {
                    selector = '#product-catalog';
                    deleteElement('productCatalogTable');
                    var tbl = document.querySelector('#productCatalogTable');
                    for (let key in data) {
                        const row = document.createElement("tr");
                        const indexCell = document.createElement("td");
                        const indexCellText = document.createTextNode(parseInt(key) + 1);
                        indexCell.appendChild(indexCellText);
                        row.appendChild(indexCell);
                        let values = {
                            'ProductCatalogIdx': data[key]['ProductCatalogIdx'],
                            'RefCode': data[key]['RefCode'],
                            'CatalogName': data[key]['CatalogName'],
                        };
                        for (let k in values) {
                            let cell = document.createElement("td");
                            let cellText = document.createTextNode(values[k]);
                            cell.appendChild(cellText);
                            row.appendChild(cell);
                        }
                        tbl.appendChild(row);
                    }
                    tbl.setAttribute("border", "2");
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
        if (this._purpose === 'item') {
            const tbl = document.getElementById('adminTable');
            for (let key in data) {
                num++;
                const row = document.createElement("tr");
                const indexCell = document.createElement("td");
                const indexCellText = document.createTextNode(num);
                indexCell.appendChild(indexCellText);
                row.appendChild(indexCell);
                let values = {
                    'RegDatetime': data[key]['RegDatetime'],
                    'ProductIdx': data[key]['ProductIdx'],
                    'CategoryName': data[key]['CategoryName'],
                    'ProductName': data[key]['ProductName'],
                    'Gender': data[key]['Gender'] === '1' ? '남' : (data[key]['Gender'] === '2') ? '여' : '',
                    'CatalogNum': data[key]['CatalogNum'],
                    'Options': '',
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    if (k === 'CatalogNum' && values[k] !== '-') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode(values[k]);
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.setAttribute('name', 'data-view');
                        cell2.setAttribute('data-value', data[key]['ProductIdx']);
                        cell2.appendChild(cellText2);
                        cell.appendChild(cell2);
                    } else {
                        let cellText = document.createTextNode(values[k]);
                        cell.appendChild(cellText);
                    }
                    if (k === 'Options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('수정');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.setAttribute('name', 'data-modify');
                        cell2.setAttribute('data-value', data[key]['ProductIdx']);
                        cell2.appendChild(cellText2);
                        cell.appendChild(cell2);
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            }
            tbl.setAttribute("border", "2");
            // 카탈로그 항목 클릭시
            document.getElementsByName('data-view').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    mainScript._purpose = 'catalogList';
                    let data = {'productIdx': this.getAttribute('data-value')};
                    mainScript.request(data);
                });
            });

            //수정 버튼 클릭시
            document.getElementsByName('data-modify').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    //상품삭제 버튼 표기
                    document.getElementById('removeBtn').setAttribute('style', 'display: blank');

                    //Modal 닫을때 상품삭제 버튼 숨기기
                    document.getElementById("registerProduct").addEventListener('hidden.bs.modal', e => {
                        //상품삭제 버튼 표기
                        document.getElementById('removeBtn').setAttribute('style', 'display: none');
                    });
                    //카탈로그 항목 초기화
                    adminScript.deleteCatalogList();
                    mainScript._purpose = 'searchProductItem';
                    let data = {'productIdx': this.getAttribute('data-value')};
                    mainScript.request(data);
                });
            });

        } else if (mainScript._purpose === 'group') {
            //상품그룹
            const tbl = document.getElementById('adminTable');
            for (let key in data) {
                num++;
                const row = document.createElement("tr");
                const indexCell = document.createElement("td");
                const indexCellText = document.createTextNode(num);
                indexCell.appendChild(indexCellText);
                row.appendChild(indexCell);
                let values = {
                    'RegDatetime': data[key]['RegDatetime'],
                    'ProductGroupIdx': data[key]['ProductGroupIdx'],
                    'ProductGroupName': data[key]['ProductGroupName'],
                    'ProductName1': data[key]['ProductName'][0],
                    'ProductName2': data[key]['ProductName'][1] ?? '',
                    'ProductName3': data[key]['ProductName'][2] ?? '',
                    'ProductName4': data[key]['ProductName'][3] ?? '',
                    'ProductName5': data[key]['ProductName'][4] ?? '',
                    'Options': '',
                };
                for (let k in values) {
                    let cell = document.createElement("td");
                    let cellText = document.createTextNode(values[k]);
                    cell.appendChild(cellText);
                    if (k === 'Options') {
                        let cell2 = document.createElement("button");
                        let cellText2 = document.createTextNode('수정');
                        cell2.className += 'btn btn-sm btn-info';
                        cell2.setAttribute('name', 'data-modify');
                        cell2.setAttribute('data-value', data[key]['ProductGroupIdx']);
                        cell2.appendChild(cellText2);
                        cell.appendChild(cell2);
                    }
                    row.appendChild(cell);
                }
                tbl.appendChild(row);
            }
            tbl.setAttribute("border", "2");
            mainScript.setEventListener(this._purpose);
        }
    },
    register: function (data) {
        if (mainScript._purpose === 'itemGroupInsert') {
            //등록시 대응
            data.productGroupName = document.getElementById('productGroupName').value;
            data.childProductIdx = [];
            document.querySelectorAll('.childProductIdx:checked').forEach((el) => {
                data.childProductIdx.push(parseInt(el.value));
            });
            data.childProductIdx = JSON.stringify(data.childProductIdx);
            if (confirm('등록 하시겠습니까?')) {
                mainScript._purpose = 'itemGroupInsert';
                mainScript._methodType = 'POST';
                mainScript.request(data);
                //모달 닫기
                let modalEl = document.getElementById('registerProductGroup');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            }
        } else if (mainScript._purpose === 'registerProduct') {
            //로직 개선 체크
            data.productIdx = document.getElementById('productIdx').value;
            data.categoryIdx = document.getElementById('category').value;
            data.subdivision = document.getElementById('subdivision').value;
            data.catalogList = {};
            data.delCatalogArr = {};

            const catalogCnt = document.getElementById('catalogList').children.length;
            if (catalogCnt > 0) {
                for (let i = 0; i < catalogCnt; i++) {
                    let catalog = {};
                    cata*.idx = document.getElementsByName('catalogCodeInput')[i].value;
                    cata*.code = document.getElementsByName('catalogNameInput')[i].value.split('_')[0];
                    cata*.name = document.getElementsByName('catalogNameInput')[i].value.split('_')[1];
                    data.catalogList[i] = catalog;
                }
                data.catalogList = JSON.stringify(data.catalogList);
            }
            //삭제될 카탈로그 식별자 추가
            if (document.getElementById('delIdx').value) {
                data.delCatalogArr = JSON.stringify(document.getElementById('delIdx').value.split(",").map(Number));
            }

            if (confirm(`등록 하시겠습니까?.` + `\n`
                + `(총 ${catalogCnt}개 항목을 등록합니다.)`)) {
                mainScript._purpose = 'product';
                mainScript._methodType = 'POST';
                mainScript.request(data);
                //모달 닫기
                let modalEl = document.getElementById('registerProduct');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
            }
        }
    }
};