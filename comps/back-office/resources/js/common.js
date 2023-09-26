const sendRequest = function (type, url, data, success, error = "", complete = "") {
    let result = "";
    return new Promise((resolve, reject) => {
        const httpRequest = new XMLHttpRequest();
        httpRequest.open(type, url, true);
        httpRequest.responseType = 'json';
        httpRequest.send(data);

        // ajax response
        httpRequest.onreadystatechange = (e) => {
            if (httpRequest.readyState === 4) {
                result = httpRequest.response;
                (parseInt(httpRequest.status) === 200) ? resolve() : reject();
            }
        }
    }).then(() => {
        if (typeof success == "function") {
            if (typeof complete !== "function") {
                return success(result);
            }
            success(result);
        }
    }).catch(() => {
        if (typeof error == "function") {
            if (typeof complete !== "function") {
                return error(result);
            }
            error(result);
        }
    }).finally(() => {
        if (typeof complete == "function") {
            return complete(result);
        }
    });
}

// 관리자단 공통 스크립트
let adminScript = {
    _purpose: 'menu',
    _methodType: 'get',
    _entry: 50,
    init: function () {
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
    locate: function (data) {
        let targetForm = document.getElementsByName('newForm');
        if (targetForm.length > 0) {
            targetForm[0].parentNode.removeChild(targetForm[0]);
        }
        targetForm = document.createElement('form');
        targetForm.name = 'newForm';
        targetForm.target = '_blank';
        targetForm.method = mainScript._methodType;
        targetForm.action = document.location.href;
        for (let key in data) {
            const input = document.createElement('input');
            input.setAttribute("type", "hidden");
            input.setAttribute("name", key);
            input.setAttribute("value", data[key]);
            targetForm.appendChild(input);
        }
        document.body.appendChild(targetForm);
        targetForm.submit();
    },
    request: function () {
        let data = null;
        let url = document.location.href;
        if (this._methodType === 'get') {
            data = [...this.dataset().entries()];
            data = data
                .map(x => `${encodeURIComponent(x[0])}=${encodeURIComponent(x[1])}`)
                .join('&');

            const operator = url.indexOf('?') > 0 ? '&' : '?';
            url += operator + data;
        } else {
            data = this.dataset();
        }
        sendRequest(this._methodType, url, data, '', '', this.callback);
    },
    callback: function (response) {
        if (response) {
            if (response.code === '20200') {
                // 메뉴 세팅
                adminScript.makeMenu(response.data);
                // 각 폴더별 스크립트 initialize
                if (typeof mainScript !== 'undefined') {
                    mainScript.init();
                }
            }
        }
        adminScript.setEventListener();
    },
    setEventListener: function () {
        let purpose = mainScript._purpose;

        // 엑셀 다운로드 버튼 :: data-target에 request할 조건 별칭 입력
        const downBtnExcel = document.querySelector('.excel-down-btn');
        if(downBtnExcel) {
            downBtnExcel.addEventListener('click', el => {
                switch(downBtnExcel.getAttribute('data-list')){
                    case "front":
                        // 사업부에서 관리자 페이지에 노출된 데이터만 다운로드되면 된다 전달받아 프론트엔드에서 처리 가능할것으로 판단하여 별도 처리
                        // <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="18, 19" data-name="얼리큐_사용처정보" type="button">Excel</button>
                        // 와 같은 형태로 지정 data-id=테이블 데이터 노출되는 tbody, hidden의 경우 엑셀다운로드가 필요없는 컬럼 지정, data-name 의 경우 파일명
                        // data-id,  data-name 필수 , data-hidden 선택
                        let originTable = document.getElementById(downBtnExcel.getAttribute('data-id')).parentNode;
                        let targetTable = originTable.cloneNode(true);
                        if (downBtnExcel.getAttribute('data-hidden') !== null) {
                            let removeArray = downBtnExcel.getAttribute('data-hidden').split(",");
                            if (removeArray.length > 0) {
                                let removeTargetArray = [];
                                removeArray.forEach((e) => {
                                    removeTargetArray.push(`th:nth-child(${e}), td:nth-child(${e})`);
                                });
                                let removeTarget = targetTable.querySelectorAll(removeTargetArray.join(","));
                                removeTarget.forEach((e) => {
                                    e.remove();
                                });
                            }
                        }
                        let wb = XLSX.utils.table_to_book(targetTable, {sheet: "sheet1", raw: true});
                        XLSX.writeFile(wb, (`${downBtnExcel.getAttribute('data-name')}.xlsx`));
                        break;
                    default:
                        mainScript._purpose = 'excelFileDown';
                        let form = document.createElement('form');
                        form.target = '_blank';
                        form.method = 'POST';
                        form.action = location;
                        let input = document.createElement('input');
                        input.setAttribute("type", "hidden");
                        input.setAttribute("name", 'target');
                        input.setAttribute("value", downBtnExcel.getAttribute('data-target'));
                        form.appendChild(input);
                        input = document.createElement('input');
                        input.setAttribute("type", "hidden");
                        input.setAttribute("name", 'purpose');
                        input.setAttribute("value", mainScript._purpose);
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                        break;
                }
            });
        }

        // sample 양식 다운로드 버튼
        const sampleDownBtn = document.getElementById('sampleDownBtn');
        if(sampleDownBtn) {
            sampleDownBtn.addEventListener('click', e=> {
                window.open('https://d.g******com/b***-*abc/resources/files/'+sampleDownBtn.getAttribute('data-filename'));
            });
        }

        //검색은 기준이 모두 다르므로, 하위 이벤트 스크립트를 이용
        const entryBtn = document.querySelector('#searchEntry');
        if (entryBtn) {
            entryBtn.addEventListener('click', function (e) {
                const entry = parseInt(this.value.split(' entries')[0]);
                if (mainScript._search.entry !== entry) {
                    mainScript._search.entry = entry;
                    mainScript._search.page = 1;
                    mainScript._purpose = purpose;
                    mainScript.request();
                }
            });
        }

        //검색값 입력 후 enter event로 검색
        const searchInput = document.getElementById('searchValue');
        if (searchInput) {
            searchInput.addEventListener('keyup', function (e) {
                if (e.key === 'Enter') {
                    document.getElementById('searchBtn').click();
                }
            });
        }

        // 모달 초기화 버튼
        const modalInit = document.querySelector('.modal-init-btn');
        if (modalInit) {
            modalInit.addEventListener('click', e => {
                const modalId = modalInit.getAttribute('data-bs-target').replace('#', '');
                const target = document.getElementById(modalId);
                target.querySelectorAll('input, select').forEach(function (el, i) {
                    if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    } else if(el.type !== 'radio') {
                        el.value = null;
                    }
                });
                adminScript.deleteCatalogList();
            });
        }
        // 등록 버튼
        const registBtn = document.querySelectorAll('.regist-btn');
        registBtn.forEach(el => {
            el.addEventListener('click', e => {
                let data = {};
                const selector = e.target.getAttribute('data-target');
                const modal = document.querySelector('.' + selector);
                try {
                    modal.querySelectorAll('input, select').forEach(function (el) {
                        const cl = el.classList;
                        if (cl.contains('required-value')) {
                            if (el.type === 'checkbox') {
                                //동일 name 체크박스가 선택되어있는지 체크
                                if (!modal.querySelectorAll(`input[name='${el.name}']:checked`).length) throw new Error("필수값 미입력");
                            } else {
                                if (!el.value) throw new Error("필수값 미입력");
                            }
                        }
                        if (el.value) {
                            if (el.type === 'checkbox' || el.type === 'radio') {
                                if (el.checked) data[el.name] = el.value;
                            } else {
                                data[el.name] = el.value;
                            }
                        }
                    });
                } catch (e) {
                    alert("필수값을 입력해주세요.");
                    return;
                }
                //각 페이지별 등록시 체크하여야하는 데이터 값이 다르므로, 하위스크립트에서 Validation 체크
                if (typeof mainScript.registValidation !== 'undefined') {
                    let [validation, msg = ''] = mainScript.registValidation(data);
                    if (!validation) {
                        if (msg !== '') alert(msg);
                        return;
                    }
                }

                //상품등록 / 수정과 같은 데이터 특이케이스건 위한 예외처리 및 함수
                if (typeof mainScript.register !== 'undefined') {
                    mainScript._purpose = selector;
                    mainScript.register(data);
                } else {
                    if (confirm('정말 등록하시겠습니까?')) {
                        mainScript._purpose = selector;
                        mainScript._methodType = 'POST';
                        mainScript.request(data);
                    }
                }
            });
        });

        // 검색 버튼
        const searchBtn = document.getElementById('searchBtn');
        if (searchBtn) {
            searchBtn.addEventListener('click', e => {
                const keyword = document.getElementById('searchColumn').value;
                const value = document.getElementById('searchValue').value;
                let startDate = '';
                let endDate = '';
                if(document.getElementById('startDate')) {

                    startDate = document.getElementById('startDate').value;
                    endDate = document.getElementById('endDate').value;

                    if((startDate !== '' && endDate === '') || (startDate === '' && endDate !== '')) {
                        alert('검색기간이 잘못입력되었습니다.\n다시 입력해주세요.');
                        return;
                    }

                    function dateDifference(date1, date2) {
                        const oneDay = 24 * 60 * 60 * 1000; // Number of milliseconds in a day
                        const firstDate = new Date(date1);
                        const secondDate = new Date(date2);
                        const diffDays = Math.round(Math.abs((firstDate - secondDate) / oneDay));
                        return diffDays;
                    }

                    let diff = dateDifference(startDate, endDate);
                    if(diff > 90) {
                        alert('검색 가능한 기간은 최대 90일입니다');
                        return;
                    }
                }
                if (!keyword && !startDate) {
                    alert('검색 키워드를 선택하세요');
                    return false;
                }
                if (!value && !startDate) {
                    alert('검색할 값을 입력하세요');
                    return false;
                }
                mainScript._search.keyword = keyword;
                mainScript._search.value = value;
                mainScript._search.startDate = startDate;
                mainScript._search.endDate = endDate;
                mainScript._purpose = purpose;
                mainScript._methodType = 'get';
                mainScript.request();
            })
        }

        //input 내 숫자만 입력하여야하는 경우 숫자만 입력받을수 있도록 클래스 key up 이벤트 처리
        const numbericInput = document.querySelectorAll('.number');
        if (numbericInput.length !== 0) {
            numbericInput.forEach(el => {
                el.addEventListener('keyup', e => {
                    el.value = el.value.replace(/[^0-9]/g, "");
                });
            });
        }

    },
    deleteCatalogList: function () {
        if (document.getElementById('delIdx')) {
            document.getElementById('delIdx').value = "";
        }
        if (document.getElementById('catalogName')) {
            document.getElementById('catalogName').value = "";
        }
        if (document.getElementById('catalogCode')) {
            document.getElementById('catalogCode').value = "";
        }

        if (document.getElementById('catalogList')) {
            let catalogList = document.getElementById('catalogList');
            while (catalogList.firstChild) {
                catalogList.removeChild(catalogList.firstChild);
            }
        }
    },
    makeMenu: function (menu) {
        const nav = document.querySelector('.admin-sub-menu');
        const group = document.querySelector('.admin-group-menu');
        let p1 = 'offer***';
        let p2 = 'goods';
        let defaultLocation = document.location.href.split('/');
        if (defaultLocation[3]) {
            p1 = defaultLocation[3];
        }
        if (defaultLocation[4]) {
            p2 = defaultLocation[4].split('?sub=')[0] ? defaultLocation[4].split('?sub=')[0] : defaultLocation[4];
        }
        let subIdx = '';
        //group code별 메뉴 설정
        for(let key in menu) {
            if (key === 'navi') {
                for (let k in menu['productGroup']) {
                    //menu에 세팅이 되어있지않은 경우 미노출되도록 처리
                    if(typeof menu['navi'][menu['productGroup'][k]['ProductGroupCode']] === 'undefined'){
                        continue;
                    }
                    const li = document.createElement("li");
                    li.classList.add('nav-item');
                    const a = document.createElement("a");
                    a.classList.add('nav-link');

                    a.setAttribute('data-destination',menu['navi'][menu['productGroup'][k]['ProductGroupCode']][0]['id']);
                    let location = '/'+menu['productGroup'][k]['ProductGroupCode']+'/'+menu['navi'][menu['productGroup'][k]['ProductGroupCode']][0]['id'];
                    let menuText = menu['productGroup'][k]['ProductGroupName'];
                    // 상단 nav-item의 location 가공
                    if (Object.keys(menu['navi'][menu['productGroup'][k]['ProductGroupCode']][0]).includes('sub')) {
                        subId = Object.keys(menu['navi'][menu['productGroup'][k]['ProductGroupCode']][0]['sub'])[0];
                        location += '?sub='+subId;
                    }
                    a.setAttribute('href', location);
                    const text = document.createTextNode(menuText);
                    a.appendChild(text);
                    if (p1 === menu['productGroup'][k]['ProductGroupCode']) {
                        a.setAttribute('style', 'color:black');
                    }
                    li.appendChild(a);
                    group.appendChild(li);
                }
            }
            if(key === 'navi') {
                for(let key in menu['navi']) {
                    //service가 일치할 경우 navi 하위 세팅
                    if(p1 === key) {
                        for(let k in menu['navi'][key]) {
                            const li = document.createElement("li");
                            li.classList.add('nav-item');
                            const a = document.createElement("a");
                            a.classList.add('nav-link');
                            let location = '/'+key+'/'+ menu['navi'][key][k]['id'];
                            let menuText = menu['navi'][key][k]['name'];

                            if (Object.keys(menu['navi'][key][k]).includes('sub')) {
                                for(let _k in menu['navi'][key][k]['sub']) {
                                    //해당 하위navi에 할당된 sub navi만 출력
                                    if(menu['navi'][key][k]['id'] === p2) {
                                        location = '/' + p1 + '/' + p2 + '?sub=' + menu['navi'][key][k]['sub'][_k]['id'];
                                        const subNav = document.querySelector(".sub-menu-left");
                                        const ol = document.createElement("ol");
                                        ol.className = 'bg-light';
                                        const li_2 = document.createElement("button");
                                        li_2.className = 'btn btn-light manage-btn';
                                        if (defaultLocation[4].split('?sub=')[1] === menu['navi'][key][k]['sub'][_k]['id']) {
                                            li_2.classList.add('active');
                                        }
                                        li_2.setAttribute('data-manage', menu['navi'][key][k]['sub'][_k]['id']);
                                        const text_2 = document.createTextNode(menu['navi'][key][k]['sub'][_k]['name']);
                                        li_2.appendChild(text_2);
                                        ol.appendChild(li_2);
                                        subNav.appendChild(ol);
                                    } else {

                                        location = '/'+ p1 + '/' + menu['navi'][key][k]['id'] + '?sub=' + Object.keys(menu['navi'][key][k]['sub'])[0];
                                    }
                                }

                            }
                            a.setAttribute('href', location);
                            const text = document.createTextNode(menuText);
                            a.appendChild(text);
                            if (p2 === menu['navi'][key][k]['id']) {
                                a.setAttribute('style', 'color:black');
                            }
                            li.appendChild(a);
                            nav.appendChild(li);
                        }
                    }
                }
            }
        }

        // 하위 navi에 할당된 좌측 sub menu
        let manageBtn = document.querySelectorAll('.manage-btn');
        if(manageBtn) {
            manageBtn.forEach(btn=>{
                btn.addEventListener('click',el=>{
                    const target = el.target.getAttribute('data-manage');
                    location.href = '/'+p1+'/'+p2+'?sub='+target;
                });
            })
        }
    },
    pagination: function (params) {
        const pagination = document.getElementById('pagination');
        while (pagination.firstChild) {
            pagination.removeChild(pagination.firstChild);
        }
        let item_per_page = mainScript._search.entry;
        let current_page = mainScript._search.page;
        let total_records = params.totalCnt;
        let total_pages = Math.ceil(total_records / item_per_page);

        if (total_pages > 0 && total_pages != 1 && current_page <= total_pages) {
            let right_links = current_page + 5;
            let previous = current_page - 5;
            let next = current_page + 1;
            let first_link = true;

            if (current_page > 1) {
                let previous_link = (previous <= 0) ? 1 : previous;

                let pagination_first = document.createElement('li');
                pagination_first.className = "page-item first";
                pagination_first.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="1" title="First">&laquo;</button>`);
                pagination.appendChild(pagination_first);

                let pagination_previous = document.createElement('li');
                pagination_previous.className = "page-item";
                pagination_previous.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="${previous_link}" title="Previous">Previous</button>`);
                pagination.appendChild(pagination_previous);
                for (let i = current_page - 2; i < current_page; i++) {
                    if (i > 0) {
                        let pagination_i = document.createElement('li');
                        pagination_i.className = "page-item";
                        pagination_i.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="${i}">${i}</button>`);
                        pagination.appendChild(pagination_i);
                    }
                }
                first_link = false;
            }
            let pagination_active = document.createElement('li');
            if (first_link) {
                pagination_active.className = "page-item first active";
                pagination_active.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="1">${current_page}</button>`);
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
                    pagination_j.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="${j}">${j}</button>`);
                    pagination.appendChild(pagination_j);
                }
            }
            if (current_page < total_pages) {
                let next_link = (j > total_pages) ? total_pages : j;

                let pagination_next = document.createElement('li');
                pagination_next.className = "page-item";
                pagination_next.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="${next_link}" title="Next">Next</button>`);
                pagination.appendChild(pagination_next);

                let pagination_last = document.createElement('li');
                pagination_last.className = "page-item last";
                pagination_last.insertAdjacentHTML("beforeend", `<button class="page-link" name="pagebtn" value="${total_pages}" title="Last">&raquo;</button>`);
                pagination.appendChild(pagination_last);
            }
        }
        document.getElementsByName('pagebtn').forEach(function (value, key, parent) {
            let purpose = mainScript._purpose;
            value.addEventListener('click', function () {
                const intVal = parseInt(this.value);
                if(isNaN(intVal)) {
                    return false;
                }
                if (parseInt(mainScript._search.page) !== intVal) {
                    mainScript._purpose = purpose;
                    mainScript._search.page = intVal;
                    mainScript.request();
                }
            });
        });
    },
    createBadge: function (code, name, list) {
        const badgelist = document.getElementById(list);
        let badge = document.createElement("span");

        badge.className = "badge bg-primary p-1";
        badge.setAttribute('data-code', code);
        badge.id = name;
        badge.innerHTML = name;

        let closebtn = document.createElement("button");

        closebtn.className = "btn btn-sm btn-close del-idx";
        closebtn.name = "closebg";
        closebtn.value = name;
        closebtn.setAttribute('data-code', code);

        closebtn.addEventListener('click', function () {
            let catalogCode = this.value;
            adminScript.removeBadge(catalogCode, list);
            //상품 내 체크박스 동기화
            let productCode = this.getAttribute('data-code');
            if (document.getElementById('childProduct')) {
                document.querySelectorAll('.childProductIdx').forEach(function (el) {
                    if (el.value === productCode) {
                        el.checked = false;
                    }
                });
            }
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
    },
    removeBadge: function (code, list) {
        let badgelist = document.getElementById(list);
        let badge = document.getElementById(code);

        badgelist.removeChild(badge);
    },
    execDaumPostcode: function () {
        new daum.Postcode({
            oncomplete: function (data) {
                // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

                // 각 주소의 노출 규칙에 따라 주소를 조합한다.
                // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
                let addr = ''; // 주소 변수
                let sido = ''; // 주소 변수
                let sigungu = ''; // 주소 변수
                let roadname = ''; // 주소 변수
                let bname = ''; // 주소 변수
                let extraAddr = ''; // 참고항목 변수

                //사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
                if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                    addr = data.roadAddress;
                    sido = data.sido;
                    sigungu = data.sigungu;
                    roadname = data.roadname;
                } else { // 사용자가 지번 주소를 선택했을 경우(J)
                    addr = data.jibunAddress;
                    sido = data.sido;
                    sigungu = data.sigungu;
                    bname = data.bname;
                }

                // 사용자가 선택한 주소가 도로명 타입일때 참고항목을 조합한다.
                if (data.userSelectedType === 'R') {
                    // 법정동명이 있을 경우 추가한다. (법정리는 제외)
                    // 법정동의 경우 마지막 문자가 "동/로/가"로 끝난다.
                    if (data.bname !== '' && /[동|로|가]$/g.test(data.bname)) {
                        extraAddr += data.bname;
                    }
                    // 건물명이 있고, 공동주택일 경우 추가한다.
                    if (data.buildingName !== '' && data.apartment === 'Y') {
                        extraAddr += (extraAddr !== '' ? `, ${data.buildingName}` : data.buildingName);
                    }
                    // 표시할 참고항목이 있을 경우, 괄호까지 추가한 최종 문자열을 만든다.
                    if (extraAddr !== '') {
                        extraAddr = ` (${extraAddr})`;
                    }

                    extraAddr = addr.replace(sido, "").replace(sigungu, "").replace(roadname, "").trim() + extraAddr;
                    // 조합된 참고항목을 해당 필드에 넣는다.
                    document.getElementById("addressDetail").value = extraAddr.trim();
                    document.getElementById("roadname").value = roadname;
                } else {
                    extraAddr = addr.replace(sido, "").replace(sigungu, "").replace(bname, "").trim();

                    document.getElementById("addressDetail").value = extraAddr.trim();
                    document.getElementById("roadname").value = bname;
                }

                // 우편번호와 주소 정보를 해당 필드에 넣는다.
                document.getElementById('postcode').value = data.zonecode;
                document.getElementById("address").value = `${sido} ${sigungu}`;
                if (data.userSelectedType === 'R') {
                    document.getElementById("address").value += ` ${roadname}`;
                } else {
                    document.getElementById("address").value += ` ${bname}`;
                }
                document.getElementById("sido").value = sido;
                document.getElementById("sigungu").value = sigungu;
                // 커서를 상세주소 필드로 이동한다.
                document.getElementById("addressDetail").focus();
            },
            shorthand: false,
            hideMapBtn: true,
            hideEngBtn: true,
        }).open();
    },
    downloadSpreadSheet : function (data, location) {
        let form = document.createElement('form');
        form.target = '_blank';
        //get요청시 uri 길이제한
        form.method = 'POST';
        form.action = location;
        let input = document.createElement('input');
        input.setAttribute("type", "hidden");
        input.setAttribute("name", 'data');
        input.setAttribute("value", data);
        form.appendChild(input);
        input = document.createElement('input');
        input.setAttribute("type", "hidden");
        input.setAttribute("name", 'purpose');
        input.setAttribute("value", mainScript._purpose);
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
};

function deleteElement(id) {
    var list = document.getElementById(id);
    var listChild = list.children;
    for (var i = 0; i < listChild.length; i++) {
        list.removeChild(listChild[i])
        i--;
    }
}

window.onload = function () {
    adminScript.init();

    // Initialize sortable table buttons
    let sortableTables = document.querySelectorAll('table.sortable');
    for (let i = 0; i < sortableTables.length; i++) {
        new SortableTable(sortableTables[i]);
    }
}

class SortableTable {
    constructor(tableNode) {
        this.tableNode = tableNode;

        this.columnHeaders = tableNode.querySelectorAll('thead th');

        this.sortColumns = [];

        for (let i = 0; i < this.columnHeaders.length; i++) {
            let ch = this.columnHeaders[i];
            let buttonNode = ch.querySelector('button');
            if (buttonNode) {
                this.sortColumns.push(i);
                buttonNode.setAttribute('data-column-index', i);
                buttonNode.addEventListener('click', this.handleClick.bind(this));
            }
        }

        this.optionCheckbox = document.querySelector(
            'input[type="checkbox"][value="show-unsorted-icon"]'
        );

        if (this.optionCheckbox) {
            this.optionCheckbox.addEventListener(
                'change',
                this.handleOptionChange.bind(this)
            );
            if (this.optionCheckbox.checked) {
                this.tableNode.classList.add('show-unsorted-icon');
            }
        }
    }

    setColumnHeaderSort(columnIndex) {
        if (typeof columnIndex === 'string') {
            columnIndex = parseInt(columnIndex);
        }

        for (let i = 0; i < this.columnHeaders.length; i++) {
            let ch = this.columnHeaders[i];
            let buttonNode = ch.querySelector('button');
            if (i === columnIndex) {
                let purpose = mainScript._purpose;
                let value = ch.getAttribute('aria-sort');
                let column = ch.getAttribute('data-column');
                if (value === 'descending') {
                    ch.setAttribute('aria-sort', 'ascending');
                    mainScript._search.column = column;
                    mainScript._search.sort = 'desc';
                    mainScript._purpose = purpose;
                    mainScript._methodType = 'get';
                    mainScript.request();
                } else {
                    ch.setAttribute('aria-sort', 'descending');
                    mainScript._search.column = column;
                    mainScript._search.sort = 'asc';
                    mainScript._purpose = purpose;
                    mainScript._methodType = 'get';
                    mainScript.request();
                }
            } else {
                if (ch.hasAttribute('aria-sort') && buttonNode) {
                    ch.removeAttribute('aria-sort');
                }
            }
        }
    }

    /* EVENT HANDLERS */
    handleClick(event) {
        let tgt = event.currentTarget;
        this.setColumnHeaderSort(tgt.getAttribute('data-column-index'));
    }

    handleOptionChange(event) {
        let tgt = event.currentTarget;

        if (tgt.checked) {
            this.tableNode.classList.add('show-unsorted-icon');
        } else {
            this.tableNode.classList.remove('show-unsorted-icon');
        }
    }
}