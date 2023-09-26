"use strict";
window.onload = function () {
    getInsuranceItem();

    document.getElementById('rownum')
        .addEventListener('change', function () {
            getInsuranceItem(`/abc/insuranceItem?api=Y&page=1&rowNum=${this.value}`);
        });

    document.getElementById('searchBtn')
        .addEventListener('click', function () {
            getInsuranceItem();
        });

    document.getElementById('searchValue')
        .addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                document.getElementById('searchBtn').click();
            }
        });

    document.querySelector('#registerInsuranceItemBtn')
        .addEventListener('click', function () {

            let registerType = document.getElementById('registerType').value;
            let ibCompanyIdx = document.getElementById('ibCompanyIdx').value ?? 0;

            const registerFileInput = document.getElementById('insuranceListInput');
            let registerFile = registerFileInput.files[0];

            if (!registerFile) {
                alert("업로드할 파일을 선택하세요.");
                return false;
            }
            if (!validFileSize(registerFile)) {
                alert("업로드 파일크기 500KB 보다 큽니다.");
                return false;
            }
            if (!validFileTypes(registerFile)) {
                alert("처리할 수 있는 파일 형식이 아닙니다.");
                return false;
            }

            let registerFileFormData = new FormData();
            registerFileFormData.append("registerInsuranceList", registerFile);
            registerFileFormData.append("registerType", registerType);
            registerFileFormData.append("ibCompanyIdx", ibCompanyIdx);

            createInsuranceItems(registerFileFormData);
        });

    document.getElementById('editInsuranceItem')
        .addEventListener('show.bs.modal', event => {

            const button = event.relatedTarget;
            const insuranceIdx = button.value;

            getInsuranceData(insuranceIdx, function (data) {
                if (data) {
                    document.getElementById('ibCompany').value = data.ServiceCompanyName;
                    document.getElementById('insuranceIdx').value = data.InsureanceIdx;
                    document.getElementById('insuranceCode').value = data.InsuranceCode;
                    document.getElementById('insuranceName').value = data.InsuranceName;
                    document.getElementById('itemIdx').value = data.InsuranceItemManageIdx ?? 0;
                    document.getElementById('itemCode').value = data.ItemCode ?? "";
                    document.getElementById('itemName').value = data.ItemName ?? "";
                }
            });
        });

    document.getElementById('editInsuranceItem')
        .addEventListener('hidden.bs.modal', function () {
            document.getElementById('ibCompany').value = "";
            document.getElementById('insuranceIdx').value = "";
            document.getElementById('insuranceCode').value = "";
            document.getElementById('insuranceName').value = "";
            document.getElementById('itemIdx').value = "";
            document.getElementById('itemCode').value = "";
            document.getElementById('itemName').value = "";
        });

    document.getElementById('insuranceItemEditBtn')
        .addEventListener('click', function () {

            let data = {};
            data.insuranceIdx = document.getElementById('insuranceIdx').value;
            data.insuranceCode = document.getElementById('insuranceCode').value;
            data.insuranceName = document.getElementById('insuranceName').value;
            data.itemIdx = document.getElementById('itemIdx').value;
            data.itemCode = document.getElementById('itemCode').value;
            data.itemName = document.getElementById('itemName').value;

            let insuranceItemIdx = (data.itemIdx !== "0") ? data.itemIdx : data.insuranceIdx;
            editInsuranceItem(insuranceItemIdx, data);
        });

    document.getElementById('insuranceItemDeleteBtn')
        .addEventListener('click', function () {
            let data = {};
            data.insuranceIdx = document.getElementById('insuranceIdx').value;
            data.itemIdx = document.getElementById('itemIdx').value;

            if (data.itemIdx === "0") {
                if (confirm('**사를 정말 삭제하시겠습니까?')) {
                    deleteInsuranceItem(data.insuranceIdx);
                }
            } else {
                if (confirm('**상품을 정말 삭제하시겠습니까?')) {
                    deleteInsuranceItem(data.itemIdx);
                }
            }
        });

    document.getElementById('registerInsuranceItem')
        .addEventListener('hidden.bs.modal', function () {
            document.getElementById('registerType').value = "";
            document.getElementById('ibCompanyIdx').value = "";
            document.getElementById('insuranceListInput').value = "";
        });

    document.getElementById('registerType')
        .addEventListener('change', function () {
            const ibCompanyLabel = document.getElementById('ibCompanyLabel');
            const ibCompanySelect = document.getElementById('ibCompanyIdx');

            if (this.value === 'insurance') {
                ibCompanyLabel.style.display = "block";
                ibCompanySelect.style.display = "block";
            } else {
                ibCompanyLabel.style.display = "none";
                ibCompanySelect.style.display = "none";
            }
        });
}

const generateTable = function (params, tableId) {
    const tbl = document.getElementById(tableId);

    if (document.getElementById('insuranceItemtbody')) {
        document.getElementById('insuranceItemtbody').remove();
    }
    const tblBody = document.createElement("tbody");
    tblBody.id = 'insuranceItemtbody';

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

        tableKey.filter(element => element !== "InsuranceItemManageIdx").forEach(function (value) {
            let cell = document.createElement("td");
            let cellElement;
            if (rowData[value] !== null) {
                cellElement = document.createTextNode(rowData[value]);
            } else {
                cellElement = document.createTextNode("");
            }
            cell.appendChild(cellElement);
            row.appendChild(cell);
        });

        const editCell = document.createElement("td");
        let editCellElement = document.createElement('button');
        editCellElement.className = "btn btn-sm btn-outline-danger";
        editCellElement.setAttribute('data-bs-toggle', "modal");
        editCellElement.setAttribute('data-bs-target', "#editInsuranceItem");
        editCellElement.name = "editBtn";
        editCellElement.value = (rowData['InsuranceItemManageIdx']) ? rowData['InsuranceItemManageIdx'] : rowData['InsureanceIdx'];
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
            pagination_first.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=1&rowNum=${item_per_page}" title="First">&laquo;</button>`);
            pagination.appendChild(pagination_first);

            let pagination_previous = document.createElement('li');
            pagination_previous.className = "page-item";
            pagination_previous.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=${previous_link}&rowNum=${item_per_page}" title="Previous">Previous</button>`);
            pagination.appendChild(pagination_previous);
            for (let i = current_page - 2; i < current_page; i++) {
                if (i > 0) {
                    let pagination_i = document.createElement('li');
                    pagination_i.className = "page-item";
                    pagination_i.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=${i}&rowNum=${item_per_page}">${i}</button>`);
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
                pagination_j.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=${j}&rowNum=${item_per_page}">${j}</button>`);
                pagination.appendChild(pagination_j);
            }
        }
        if (current_page < total_pages) {
            let next_link = (j > total_pages) ? total_pages : j;

            let pagination_next = document.createElement('li');
            pagination_next.className = "page-item";
            pagination_next.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=${next_link}&rowNum=${item_per_page}" title="Next">Next</button>`);
            pagination.appendChild(pagination_next);

            let pagination_last = document.createElement('li');
            pagination_last.className = "page-item last";
            pagination_last.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="/abc/insuranceItem?api=Y&page=${total_pages}&rowNum=${item_per_page}" title="Last">&raquo;</button>`);
            pagination.appendChild(pagination_last);
        }
    }

    document.getElementsByName('pagebtn').forEach(function (value) {
        value.addEventListener('click', function () {
            getInsuranceItem(value.value);
        });
    });
}

const renderInput = function (params) {
    const ibCompanySelectBox = document.getElementById('ibCompanyIdx');

    while (ibCompanySelectBox.children.length > 1) {
        ibCompanySelectBox.removeChild(ibCompanySelectBox.lastChild);
    }

    params.forEach(function (value) {
        let option = document.createElement('option');
        option.value = value.ServiceControlIdx;
        option.innerHTML = value.ServiceCompanyName;

        ibCompanySelectBox.appendChild(option);
    });
}

const getInsuranceItem = function (urlparam) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    const url = "/abc/insuranceItem?api=Y";

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
                generateTable(response.data, "insuranceItemTable");
                setInput(response.data);
                renderPagination(response.data.pagination);
                renderInput(response.data.ibCompany);
            }
        }
    }
    xmlHttp.send();
}

const getInsuranceData = function (insuranceIdx, callback) {
    const xmlHttp = new XMLHttpRequest();
    const method = "GET";
    const url = `/abc/insuranceItemContents?insuranceIdx=${insuranceIdx}`;
    xmlHttp.open(method, url);
    xmlHttp.send();
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            let data = response.data;
            if (response.code === 201) {
                if (callback) callback(data);
            } else {
                alert('**상품 정보 조회 실패. 개발팀에 문의하여 주십시오.');
                return false;
            }
        }
    }
}

const createInsuranceItems = function (formData) {
    const xmlHttp = new XMLHttpRequest();
    const method = "POST";
    const url = "/abc/insuranceItem";
    xmlHttp.open(method, url, true);
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            let data = response.data;
            let message = response.msg;
            if (response.code === 202) {
                message += ` / 성공: ${data.success} / 실패: ${data.fail}`;
                getInsuranceItem();
            }
            alert(message);
            document.getElementById('registerInsuranceItemCloseBtn').click();
        }
    }
    xmlHttp.send(formData);
}

const deleteInsuranceItem = function (insuranceIdx) {
    const xmlHttp = new XMLHttpRequest();
    const method = "DELETE";
    const url = `/abc/insuranceItem/${insuranceIdx}`;
    xmlHttp.open(method, url);
    xmlHttp.send();
    xmlHttp.onreadystatechange = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = JSON.parse(this.response);
            let data = response.data;
            let message = response.msg;
            if (response.code === 204) {
                message += ` / **코드: ${data.insuranceIdx}`;
                getInsuranceItem();
                document.getElementById('editInsuranceItemCloseBtn').click();
            }
            alert(message);
        }
    }
}

const editInsuranceItem = function (insuranceIdx, params) {
    const xmlHttp = new XMLHttpRequest();
    const method = "PUT";
    const url = `/abc/insuranceItem/${insuranceIdx}`;
    xmlHttp.open(method, url, true);
    xmlHttp.responseType = 'json';
    xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlHttp.onload = function () {
        if (this.status === 200 && this.readyState === this.DONE) {
            let response = this.response;
            let data = response.data;
            let message = response.msg;
            if (response.code === 203) {
                message += ` / **코드: ${data.insuranceIdx}`;
                getInsuranceItem();
                document.getElementById('editInsuranceItemCloseBtn').click();
            }
            alert(message);
        }
    }
    xmlHttp.send(JSON.stringify(params));
}