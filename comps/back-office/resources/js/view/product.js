"use strict";
window.onload = function () {
    getProduct();

    document.getElementById('rownum')
        .addEventListener('change', function () {
            getProduct(`/abc/product?api=Y&page=1&rowNum=${this.value}`);
        });

    document.getElementById('searchBtn')
        .addEventListener('click', function () {
            getProduct();
        });

    document.getElementById('searchValue')
        .addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('searchBtn').click();
            }
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
            } else {
                catalogList = "catalogList_e";
                catalogCode = document.getElementById('catalogCode_e').value;
                catalogName = document.getElementById('catalogName_e').value;
            }

            createBadge("", `${catalogCode}_${catalogName}`, catalogList);
        });
    });


    document.getElementById('registerProductBtn')
        .addEventListener('click', function () {
            let data = {};

            data.categoryIdx = document.getElementById('category').value;
            data.productName = document.getElementById('productName').value;
            data.subdivision = document.getElementById('subdivision').value;
            data.catalogList = {};

            const catalogCnt = document.getElementById('catalogList').children.length;
            if (catalogCnt > 0) {
                for (let i = 0; i < catalogCnt; i++) {
                    let catalog = {};
                    cata*.code = document.getElementsByName('catalogNameInput')[i].value.split('_')[0];
                    cata*.name = document.getElementsByName('catalogNameInput')[i].value.split('_')[1];
                    data.catalogList[i] = catalog;
                }
            }
            if (
                confirm(`정말로 상품 등록을 하시겠습니까?.` + `\n`
                    + `(총 ${catalogCnt}개 항목을 등록합니다.)`)
            ) {
                createProduct(data);
            }
        });

    document.getElementById('registerProduct')
        .addEventListener('hidden.bs.modal', function () {

            let categoryBox = document.getElementById('category');
            categoryBox[0].selected = true;

            document.getElementById('productName').value = "";
            document.getElementById('subdivision').value = "";
            document.getElementById('catalogCode').value = "";
            document.getElementById('catalogName').value = "";

            deleteCatalogList();
        });

    document.getElementsByName('removeAllCatalog').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (confirm("정말 모든 항목들을 제거하시겠습니까?")) {
                deleteCatalogList();
            }
        });
    });

    document.getElementById('catalogModal')
        .addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const catalogcnt = button.getAttribute('data-catalogcnt');
            if (catalogcnt === "-") {
                const tbl = document.getElementById('productCatalogTable');
                if (tbl.lastChild.nodeName === 'TBODY') {
                    tbl.removeChild(tbl.lastChild);
                }
                event.preventDefault();
                return false;
            }

            let productIdx = button.value;
            getProductData(productIdx, function (data) {
                if (!data) {
                    alert("데이터 조회 실패 오류");
                    return false;
                }
                generateModalTable(data.productCatalog, "productCatalogTable");
            });
        });

    document.getElementById('productDeleteBtn')
        .addEventListener('click', function () {
            let productIdx = document.getElementById('productIdx').value;
            if (confirm('상품 및 연관된 항목까지 모두 삭제됩니다.' + '\n' + ' 정말로 삭제하시겠습니까?')) {
                deleteProduct(productIdx);
            }
        });

    document.getElementById('productEditBtn')
        .addEventListener('click', function () {
            let data = {};
            let productIdx = document.getElementById('productIdx').value;
            data.productInfo = {};
            data.productInfo.categoryIdx = document.getElementById('category_e').value;
            data.productInfo.productName = document.getElementById('productName_e').value;
            data.productInfo.subdivision = document.getElementById('subdivision_e').value;

            data.catalogNewList = [];
            data.catalogOldList = [];

            const catalogCnt = document.getElementById('catalogList_e').children.length;
            if (catalogCnt > 0) {
                for (let i = 0; i < catalogCnt; i++) {
                    let catalogIdx = document.getElementsByName('catalogCodeInput')[i].value;
                    if (catalogIdx) {
                        data.catalogOldList.push(catalogIdx);
                    } else {
                        let catalog = {};
                        cata*.code = document.getElementsByName('catalogNameInput')[i].value.split('_')[0];
                        cata*.name = document.getElementsByName('catalogNameInput')[i].value.split('_')[1];
                        data.catalogNewList.push(catalog);
                    }
                }
            }

            if (
                confirm(`모든 수정내용(상품, 항목 모두)들이 수정됩니다.` + `\n`
                + `정말로 수정하시겠습니까?` + `\n`
                + `(총 ${catalogCnt}개 항목을 등록합니다.)`)
            ) {
                editProduct(productIdx, data);
            }
        });
}

const deleteCatalogList = function () {
    let catalogList = document.getElementById('catalogList');
    while (catalogList.firstChild) {
        catalogList.removeChild(catalogList.firstChild);
    }

    let catalogList_e = document.getElementById('catalogList_e');
    while (catalogList_e.firstChild) {
        catalogList_e.removeChild(catalogList_e.firstChild);
    }
}

const generateTable = function (params, tableId) {
    const tbl = document.getElementById(tableId);

    if (document.getElementById('producttbody')) {
        document.getElementById('producttbody').remove();
    }
    const tblBody = document.createElement("tbody");
    tblBody.id = 'producttbody';

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
                case "Gender":
                    let gender;
                    if (rowData[value] == 1){
                        gender = "남성";
                    } else if (rowData[value] == 2){
                        gender = "여성";
                    } else {
                        gender = "";
                    }
                    cellElement = document.createTextNode(gender);
                    break;
                case "CatalogCnt":
                    cellElement = document.createElement('button');
                    cellElement.className = "btn btn-sm";
                    cellElement.setAttribute('data-bs-toggle', "modal");
                    cellElement.setAttribute('data-bs-target', "#catalogModal");
                    cellElement.setAttribute('data-catalogcnt', rowData[value]);
                    cellElement.value = rowData['ProductIdx'];
                    cellElement.name = "categoryList";
                    cellElement.insertAdjacentHTML("beforeend", `<span class="badge bg-info" style="font-size: 12px">${rowData[value]}</span>`);
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
        editCellElement.name = "editBtn";
        editCellElement.value = rowData['ProductIdx'];
        editCellElement.innerHTML = "수정";
        editCellElement.addEventListener('click', event => {
            event.preventDefault();
            deleteCatalogList();

            let productIdx = editCellElement.value;

            document.getElementById('productIdx').value = productIdx;
            document.getElementById('productEditBtn').value = productIdx;
            document.getElementById('productDeleteBtn').value = productIdx;

            getProductData(productIdx, function (data) {
                const modalEl = document.querySelector('#editProduct');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                if (data) {
                    let productData = data.productInfo;
                    let productCatalogData = data.productCatalog;

                    productCatalogData.forEach(function (rowData) {
                        createBadge(rowData['ProductCatalogIdx'], `${rowData['RefCode']}_${rowData['CatalogName']}`, "catalogList_e");
                    })

                    document.getElementById('category_e').value = productData.ParentProductIdx;
                    document.getElementById('productName_e').value = productData.ProductName;
                    document.getElementById('subdivision_e').value = (productData.Gender) ? productData.Gender : "";
                    document.getElementById('catalogCode_e').value = "";
                    document.getElementById('catalogName_e').value = "";

                    if (productData.ProductGroupList.length > 0){
                        alert('상품그룹에 소속되어 있는 상품으로 수정이 불가능합니다.');
                        modal.hide();
                    } else {
                        modal.show();
                    }
                }
            });
        });

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
    document.getElementsByName('category').forEach(function (element) {
        while (element.children.length > 1){
            element.removeChild(element.lastChild);
        }

        params.forEach(function (value) {
            let option = document.createElement('option');
            option.value = value.ProductIdx;
            option.innerHTML = value.ProductName;

            element.appendChild(option);
        })
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
            pagination_first.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=1&rowNum=${item_per_page}" title="First">&laquo;</button>`);
            pagination.appendChild(pagination_first);

            let pagination_previous = document.createElement('li');
            pagination_previous.className = "page-item";
            pagination_previous.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=${previous_link}&rowNum=${item_per_page}" title="Previous">Previous</button>`);
            pagination.appendChild(pagination_previous);
            for (let i = current_page - 2; i < current_page; i++) {
                if (i > 0) {
                    let pagination_i = document.createElement('li');
                    pagination_i.className = "page-item";
                    pagination_i.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=${i}&rowNum=${item_per_page}">${i}</button>`);
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
                pagination_j.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=${j}&rowNum=${item_per_page}">${j}</button>`);
                pagination.appendChild(pagination_j);
            }
        }
        if (current_page < total_pages) {
            let next_link = (j > total_pages) ? total_pages : j;

            let pagination_next = document.createElement('li');
            pagination_next.className = "page-item";
            pagination_next.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=${next_link}&rowNum=${item_per_page}" title="Next">Next</button>`);
            pagination.appendChild(pagination_next);

            let pagination_last = document.createElement('li');
            pagination_last.className = "page-item last";
            pagination_last.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/product?api=Y&page=${total_pages}&rowNum=${item_per_page}" title="Last">&raquo;</button>`);
            pagination.appendChild(pagination_last);
        }
    }

    document.getElementsByName('pagebtn').forEach(function (value, key, parent) {
        value.addEventListener('click', function () {
            getProduct(value.value);
        });
    });
}


const getProduct = function (urlparam) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    const url = "/abc/product?api=Y";

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
                generateTable(response.data, "productTable");
                setInput(response.data);
                renderPagination(response.data.pagination);
                renderInput(response.data.category);
            }
        }
    }
    xmlHttp.send();
}

const createBadge = function (code, name, list) {
    const badgelist = document.getElementById(list);
    let badge = document.createElement("span");

    badge.className = "badge bg-primary p-1";
    badge.setAttribute('data-code', code);
    badge.id = name;
    badge.innerHTML = name;

    let closebtn = document.createElement("button");

    closebtn.className = "btn btn-sm btn-close";
    closebtn.name = "closebg";
    closebtn.value = name;

    closebtn.addEventListener('click', function () {
        let catalogCode = this.value;

        removeBadge(catalogCode, list);
    });

    let catalogInput1 = document.createElement("input");

    catalogInput1.type = "hidden";
    catalogInput1.name = "catalogNameInput";
    catalogInput1.value = name;

    let catalogInput2 = document.createElement("input");

    catalogInput2.type = "hidden";
    catalogInput2.name = "catalogCodeInput";
    catalogInput2.value = code;

    badge.appendChild(closebtn);
    badge.appendChild(catalogInput1);
    badge.appendChild(catalogInput2);
    badgelist.append(badge);
}

const removeBadge = function (code, list) {
    let badgelist = document.getElementById(list);
    let badge = document.getElementById(code);

    badgelist.removeChild(badge);
}

const generateModalTable = function (params, table) {
    const tbl = document.getElementById(table);
    while (tbl.lastChild.nodeName !== 'THEAD') {
        tbl.removeChild(tbl.lastChild);
    }

    const tblBody = document.createElement("tbody");

    let i = 0;
    params.forEach(function (rowData) {
        const row = document.createElement("tr");

        const indexCell = document.createElement("td");
        const indexCellText = document.createTextNode(i + 1);
        indexCell.appendChild(indexCellText);
        row.appendChild(indexCell);

        let length = Object.keys(rowData).length;
        for (let j = 0; j < length; j++) {
            let cell = document.createElement("td");
            let cellText = document.createTextNode(rowData[Object.keys(rowData)[j]]);
            cell.appendChild(cellText);
            row.appendChild(cell);
        }

        tblBody.appendChild(row);

        i++;
    });

    tbl.appendChild(tblBody);
    tbl.setAttribute("border", "1");
}

const getProductData = function (productIdx, callback) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    const url = `/abc/productContents?productIdx=${productIdx}`;
    xmlHttp.open(method, url);
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

const createProduct = function (data) {
    const xmlHttp = new XMLHttpRequest();
    const method = "POST";
    const url = "/abc/product";
    xmlHttp.open(method, url, true);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 202) {
                message += ` / 상품코드: ${data.productIdx}`;
                getProduct();
                document.getElementById('registerProductCloseBtn').click();
            }
            alert(message);
        }
    }
    xmlHttp.send(`data=${JSON.stringify(data)}`);
}

const deleteProduct = function (productIdx) {
    const xmlHttp = new XMLHttpRequest();
    const method = "DELETE";
    const url = `/abc/product/${productIdx}`;
    xmlHttp.open(method, url);
    xmlHttp.send();
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            let data = response.data;
            let message = response.msg;
            if (response.code === 204) {
                message += ` / 상품코드: ${data.productIdx}`;
                getProduct();
                document.getElementById('editProductCloseBtn').click();
            }
            alert(message);
        }
    }
}

const editProduct = function (productIdx, params) {
    const xmlHttp = new XMLHttpRequest();
    const method = "PUT";
    const url = `/abc/product/${productIdx}`;
    xmlHttp.open(method, url, true);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.onload = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 203) {
                message += ` / 상품코드: ${data.productIdx}`;
                getProduct();
                document.getElementById('editProductCloseBtn').click();
            }
            alert(message);
        }
    }
    xmlHttp.send(JSON.stringify(params));
}
