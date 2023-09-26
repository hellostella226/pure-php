<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">질환검사</h3>
    </div>
    <hr class="mb-1">
    <div class="row">
        <!-- 검색영역 -->
        <div class="row justify-content-end">
            <div class="col-md-1" id="searchDiv">
                <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                    <option value="">검색컬럼 선택</option>
                    <option value="prm.CalcDate">검사일</option>
                    <option value="m.Name">검사자 이름</option>
                    <option value="m.Phone">검사자 전화번호</option>
                    <option value="pccm.ClientCustomerName">회사명</option>
                    <option value="ccm.ClientCustomerName">사용자 이름</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="row">
                    <label for="searchValue"></label>
                    <input type="text" class="form-control form-control-sm col" name="searchValue" id="searchValue" value="">
                    <button class="btn btn-sm btn-info col-md-3" id="searchBtn">검색</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-sm-1">
            <select class="form-select form-select-sm" id="searchEntry">
                <option>50 entries</option>
                <option>100 entries</option>
                <option>150 entries</option>
                <option>200 entries</option>
                <option>250 entries</option>
            </select>
        </div>
        <div class="col-sm-11 mt-3" style="text-align:right;">
            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="10" data-name="얼리닥터_검사자조회" type="button">Excel</button>
        </div>
    </div>
    <div class="container-fluid table-responsive">
        <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%" id="diseaseTable">
            <thead>
            <tr>
                <th scope="col" class="no-sort">번호</th>
                <th scope="col" class="no-sort">사용자ID</th>
                <th scope="col" class="no-sort">회사명</th>
                <th scope="col" class="no-sort">사용자 이름</th>
                <th scope="col" class="no-sort">사용자 전화번호</th>
                <th scope="col" data-column="prm.CalcDate"><button class="sort-btn">검사일시<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="m.Name"><button class="sort-btn" >검사자 이름<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="m.Phone"><button class="sort-btn" >검사자 전화번호<span aria-hidden="true"></span></button></th>
                <th scope="col" class="no-sort"> 생성완료</th>
                <th scope="col" class="no-sort"></th>
            </tr>
            </thead>
            <tbody id="adminTable">
            </tbody>
        </table>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </div>
</div>
<script src="/b***-*abc/resources/js/offerabc/bioage.js"></script>