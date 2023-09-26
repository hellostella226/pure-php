"use strict";
window.onload = function () {
    getProductGroup();

    document.getElementById('rownum')
        .addEventListener('change', function () {
            getProductGroup(`/abc/productGroup?api=Y&page=1&rowNum=${this.value}`);
        });

    document.getElementById('searchBtn')
        .addEventListener('click', function () {
            getProductGroup();
        });

    document.getElementById('searchValue')
        .addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('searchBtn').click();
            }
        });

    document.getElementById('registerProductGroup')
        .addEventListener('show.bs.modal', function () {
            document.getElementById('productGroupName').value = "";
            let productList = document.getElementById('productList');
            while (productList.firstChild) {
                productList.removeChild(productList.firstChild);
            }

            createModalTable();
        });

    document.getElementById('category')
        .addEventListener('change', function () {
            createModalTable();
        });

    document.getElementById('registerProductGroupBtn')
        .addEventListener('click', function () {
            let data = {};

            data.productGroupName = document.getElementById('productGroupName').value;
            if (data.productGroupName.trim() === "") {
                alert('상품그룹명이 입력되지 않았습니다.');
                return false;
            }
            data.productList = {};

            const productCnt = document.getElementById('productList').children.length;
            if (productCnt > 0) {
                for (let i = 0; i < productCnt; i++) {
                    data.productList[i] = document.getElementsByName('productCodeInput')[i].value;
                }
            } else {
                alert('상품이 선택되지 않았습니다. 상품을 선택하여주세요');
                return false;
            }
            if (
                confirm(`정말로 상품그룹 등록을 하시겠습니까?.` + `\n`
                    + `(총 ${productCnt}개 상품를 할당합니다.)`)
            ) {
                createProductGroup(data);
            }
        });

    document.getElementById('editProductGroup')
        .addEventListener('show.bs.modal', event => {
            document.getElementById('productGroupName_e').value = "";

            const button = event.relatedTarget;
            let productGroupIdx = button.getAttribute('data-productgroup');
            document.getElementById('productGroupIdx').value = productGroupIdx;
            document.getElementById('productGroupName_e').value = button.value;
        });

    document.getElementById('productGroupEditBtn')
        .addEventListener('click', function () {
            let productGroupIdx = document.getElementById('productGroupIdx').value;
            let params = {};
            params.productGroupName = document.getElementById('productGroupName_e').value;

            editProductGroup(productGroupIdx, params);
        });

    document.getElementById('productGroupDeleteBtn')
        .addEventListener('click', function () {
            let productGroupIdx = document.getElementById('productGroupIdx').value;

            getProductList('productGroupIdx', productGroupIdx, function (data) {
                if (data.length !== 0) {
                    alert('상속된 거래처 병원이 존재하여 삭제할 수 없습니다.');
                    return false;
                } else {
                    if (confirm('해당 상품그룹을 삭제하시겠습니까?')) {
                        deleteProductGroup(productGroupIdx);
                    }
                }
            });
        });
}

const getProductGroup = function (urlparam) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    const url = "/abc/productGroup?api=Y";

    let requestUrl;
    if (!urlparam) {
        let rownum = document.getElementById('rownum').value;
        requestUrl = `${url}&rowNum=${rownum}`;
        let column = document.getElementById('searchColumn').value;
        let value = document.getElementById('searchValue').value;
        if (column) {
            requestUrl += `&searchColumn=${column}`;
            requestUrl += `&searchValue=${value}`;
        }
    } else {
        requestUrl = urlparam;
    }

    xmlHttp.open(method, requestUrl);
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            if (response.code === 201) {
                generateTable(response.data, "productGroupTable");
                setInput(response.data);
                renderPagination(response.data.pagination);
                renderInput(response.data.category);
            }
        }
    }
    xmlHttp.send();
}

const generateTable = function (params, tableId) {
    const tbl = document.getElementById(tableId);

    if (document.getElementById('productGrouptbody')) {
        document.getElementById('productGrouptbody').remove();
    }
    const tblBody = document.createElement("tbody");
    tblBody.id = 'productGrouptbody';

    let data = params.list;
    let startNo = params.pagination.startNo;
    data.forEach(function (rowData) {
        startNo++;
        const tableKey = Object.keys(rowData);
        const row = document.createElement("tr")
        const indexCell = document.createElement("td");
        const indexCellText = document.createTextNode(startNo);
        indexCell.appendChild(indexCellText);
        row.appendChild(indexCell);

        tableKey.forEach(function (value) {
            let cell = document.createElement("td");
            let cellElement;
            switch (value) {
                case "RegDatetime":
                    cellElement = document.createTextNode(rowData[value].split(' ')[0]);
                    break;
                default:
                    cellElement = document.createTextNode(rowData[value]);
                    break;
            }
            cell.appendChild(cellElement);
            row.appendChild(cell);
        });

        const editCell = document.createElement("td");
        let editCellElement = document.createElement('button');
        editCellElement.className = "btn btn-sm btn-outline-danger";
        editCellElement.setAttribute('data-bs-toggle', "modal");
        editCellElement.setAttribute('data-bs-target', "#editProductGroup");
        editCellElement.setAttribute('data-productgroup', rowData['ProductGroupIdx']);
        editCellElement.value = rowData['ProductGroupName'];
        editCellElement.innerHTML = "수정";

        editCell.appendChild(editCellElement);
        row.appendChild(editCell);

        tblBody.appendChild(row);
    });

    tbl.appendChild(tblBody);
    tbl.setAttribute("border", "2");
}

const setInput = function (params) {
    const rownumSelectBox = document.getElementById('rownum');
    const searchColumnSelectBox = document.getElementById('searchColumn');
    const searchValueInput = document.getElementById('searchValue');

    let rownum = params.pagination.rowNum;
    let searchColumn = params.search.column;
    let searchValue = params.search.value;

    for (let i = 0; i < rownumSelectBox.options.length; i++) {
        if (rownumSelectBox[i].value === rownum) {
            rownumSelectBox[i].selected = true;
            break;
        }
    }

    for (let i = 0; i < searchColumnSelectBox.options.length; i++) {
        if (searchColumnSelectBox[i].value === searchColumn) {
            searchColumnSelectBox[i].selected = true;
            break;
        }
    }

    searchValueInput.value = searchValue;
}

const renderInput = function (params) {
    const category = document.getElementById('category');

    while (category.children.length > 1) {
        category.removeChild(category.lastChild);
    }

    params.forEach(function (value) {
        let option = document.createElement('option');
        option.value = value.ProductIdx;
        option.innerHTML = value.ProductName;

        category.appendChild(option);
    });
}

const renderPagination = function (params) {
    const pagination = document.getElementById('pagination');
    while (pagination.firstChild) {
        pagination.removeChild(pagination.firstChild);
    }

    let item_per_page = params.rowNum;
    let current_page = params.page;
    let total_records = params.totalCnt;
    let total_pages = Math.ceil(total_records / item_per_page);

    if (total_pages > 0 && total_pages != 1 && current_page <= total_pages) {
        let right_links = current_page + 3;
        let previous = current_page - 3;
        let next = current_page + 1;
        let first_link = true;

        if (current_page > 1) {
            let previous_link = (previous <= 0) ? 1 : previous;

            let pagination_first = document.createElement('li');
            pagination_first.className = "page-item first";
            pagination_first.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=1&rowNum=${item_per_page}" title="First">&laquo;</button>`);
            pagination.appendChild(pagination_first);

            let pagination_previous = document.createElement('li');
            pagination_previous.className = "page-item";
            pagination_previous.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=${previous_link}&rowNum=${item_per_page}" title="Previous">Previous</button>`);
            pagination.appendChild(pagination_previous);
            for (let i = current_page - 2; i < current_page; i++) {
                if (i > 0) {
                    let pagination_i = document.createElement('li');
                    pagination_i.className = "page-item";
                    pagination_i.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=${i}&rowNum=${item_per_page}">${i}</button>`);
                    pagination.appendChild(pagination_i);
                }
            }
            first_link = false;
        }

        let pagination_active = document.createElement('li');
        if (first_link) {
            pagination_active.className = "page-item first active";
            pagination_active.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn">${current_page}</button>`);
        } else if (current_page == total_pages) {
            pagination_active.className = "page-item last active";
            pagination_active.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn">${current_page}</button>`);
        } else {
            pagination_active.className = "page-item active";
            pagination_active.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn">${current_page}</button>`);
        }
        pagination.appendChild(pagination_active);

        let j = 0;
        for (j = current_page + 1; j < right_links; j++) {
            if (j <= total_pages) {
                let pagination_j = document.createElement('li');
                pagination_j.className = "page-item";
                pagination_j.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=${j}&rowNum=${item_per_page}">${j}</button>`);
                pagination.appendChild(pagination_j);
            }
        }
        if (current_page < total_pages) {
            let next_link = (j > total_pages) ? total_pages : j;

            let pagination_next = document.createElement('li');
            pagination_next.className = "page-item";
            pagination_next.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=${next_link}&rowNum=${item_per_page}" title="Next">Next</button>`);
            pagination.appendChild(pagination_next);

            let pagination_last = document.createElement('li');
            pagination_last.className = "page-item last";
            pagination_last.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/productGroup?api=Y&page=${total_pages}&rowNum=${item_per_page}" title="Last">&raquo;</button>`);
            pagination.appendChild(pagination_last);
        }
    }

    document.getElementsByName('pagebtn').forEach(function (value) {
        value.addEventListener('click', function () {
            getProductGroup(value.value);
        });
    });
}

const getProductList = function (paramName, paramValue, callback) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    let requestUrl = "/abc/productGroupContents";
    requestUrl += `?${paramName}=${paramValue}`;
    xmlHttp.open(method, requestUrl);
    xmlHttp.send();
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            let data = response.data;
            if (response.code === 201) {
                if (callback) callback(data);
            }
        }
    }
}

const generateModalTable = function (params, table) {
    const tbl = document.getElementById(table);
    if (tbl.lastChild.nodeName === 'TBODY') {
        tbl.removeChild(tbl.lastChild);
    }

    const tblBody = document.createElement("tbody");

    let j = 0;
    params.forEach(function (rowData) {
        const row = document.createElement("tr");

        let length = Object.keys(rowData).length;
        for (let i = 0; i < length; i++) {
            let cell = document.createElement("td");
            cell.className = "text-center";
            let cellText = document.createTextNode(rowData[Object.keys(rowData)[i]]);
            cell.appendChild(cellText);
            row.appendChild(cell);
        }

        const optionCell = document.createElement("td");
        const optionCheckBox = document.createElement('input');
        optionCheckBox.setAttribute('type', 'checkbox');
        optionCheckBox.className = 'form-check-input flex';
        optionCheckBox.id = `checkbox_${rowData['ProductIdx']}`;
        optionCheckBox.value = rowData['ProductIdx'];
        optionCheckBox.setAttribute('data-productname', rowData['ProductName']);
        optionCheckBox.addEventListener('change', function (event) {
            if (this.checked === true) {
                createBadge(this.value, this.getAttribute('data-productname'), "productList");
            } else {
                removeBadge(this.value, "productList");
            }
            if (document.getElementsByClassName('product-selected').length > 5){
                alert('선택 가능한 상품 개수를 모두 사용하였습니다. (5/5)');
                event.currentTarget.checked = false;
                removeBadge(this.value, "productList");
            }
        });

        const badge = document.getElementById(rowData['ProductIdx']);
        if (badge) {
            optionCheckBox.checked = true;
        }


        optionCell.appendChild(optionCheckBox);
        row.appendChild(optionCell);

        tblBody.appendChild(row);

        j++;
    });

    tbl.appendChild(tblBody);
    tbl.setAttribute("border", "1");
}

const createBadge = function (code, name, list) {
    const badgelist = document.getElementById(list);
    const badge = document.createElement("span");

    badge.className = "badge bg-primary p-1 product-selected";
    badge.setAttribute('data-code', code);
    badge.id = code;
    badge.innerHTML = name;

    const closebtn = document.createElement("button");

    closebtn.className = "btn btn-sm btn-close";
    closebtn.name = "closebg";
    closebtn.value = code;

    closebtn.addEventListener('click', function () {
        let productIdx = this.value;

        removeBadge(productIdx, list);
    });

    const productInput1 = document.createElement("input");

    productInput1.type = "hidden";
    productInput1.name = "productNameInput";
    productInput1.value = name;

    const productInput2 = document.createElement("input");

    productInput2.type = "hidden";
    productInput2.name = "productCodeInput";
    productInput2.value = code;

    badge.appendChild(closebtn);
    badge.appendChild(productInput1);
    badge.appendChild(productInput2);
    badgelist.append(badge);
}

const removeBadge = function (code, list) {
    const badgelist = document.getElementById(list);
    const badge = document.getElementById(code);

    badgelist.removeChild(badge);

    const checkbox = document.getElementById(`checkbox_${code}`);
    if (checkbox.checked === true) {
        checkbox.checked = false;
    }
}

const createModalTable = function () {
    let categoryIdx = document.getElementById('category').value;
    getProductList("categoryIdx", categoryIdx, function (data) {

        const tbl = document.getElementById('productTable');
        while (tbl.lastChild.nodeName !== 'THEAD') {
            tbl.removeChild(tbl.lastChild);
        }

        if (data.length === 0) {
            const tblBody = document.createElement("tbody");
            const row = document.createElement("tr");
            const indexCell = document.createElement("td");
            indexCell.colSpan = 4;
            indexCell.className = "text-center";
            const indexCellText = document.createTextNode("0건 조회");
            indexCell.appendChild(indexCellText);
            row.appendChild(indexCell);
            tblBody.appendChild(row);
            tbl.appendChild(tblBody);

            return false;
        }

        generateModalTable(data, "productTable");
    });
}

const createProductGroup = function (data) {
    const xmlHttp = new XMLHttpRequest();
    const method = "POST";
    const url = "/abc/productGroup";
    xmlHttp.open(method, url, true);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 202) {
                message += ` / 상품그룹코드: ${data.productGroupIdx}`;
                getProductGroup();
                document.getElementById('registerProductGroupCloseBtn').click();
            }
            alert(message);
        }
    }
    xmlHttp.send(`data=${JSON.stringify(data)}`);
}

const editProductGroup = function (productGroupIdx, params) {
    const xmlHttp = new XMLHttpRequest();
    const method = "PUT";
    const url = `/abc/productGroup/${productGroupIdx}`;
    xmlHttp.open(method, url, true);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.onload = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 203) {
                message += ` / 상품그룹코드: ${data.productGroupIdx}`;
                getProductGroup();
                document.getElementById('editProductGroupCloseBtn').click();
            }
            alert(message);
        }
    }
    xmlHttp.send(JSON.stringify(params));
}

const deleteProductGroup = function (productGroupIdx) {
    const xmlHttp = new XMLHttpRequest();
    const method = "DELETE";
    const url = `/abc/productGroup/${productGroupIdx}`;
    xmlHttp.open(method, url);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.send();
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 204) {
                message += ` / 상품그룹코드: ${data.productGroupIdx}`;
                getProductGroup();
                document.getElementById('editProductGroupCloseBtn').click();
            }
            alert(message);
        }
    }
}