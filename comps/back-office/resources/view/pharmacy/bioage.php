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
                    <option value="mm.UsersIdx">회원ID</option>
                    <option value="m.Name">이름</option>
                    <option value="prm.NhisPreviewListIdx"> 생성완료</option>
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
            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="10" data-name="얼리큐_질환검사" type="button">Excel</button>
        </div>
    </div>
    <div class="container-fluid table-responsive">
        <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%" id="diseaseTable">
            <thead>
            <tr>
                <th scope="col" class="no-sort">번호</th>
                <th scope="col" data-column="prm.CalcDate"><button class="sort-btn">검사일<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="ccm.ClientCustomerName"><button class="sort-btn" >사용처명<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="mm.UsersIdx"><button class="sort-btn" >회원ID<span aria-hidden="true"></span></button></th>
                <th scope="col" data-column="m.Name"><button class="sort-btn" >이름<span aria-hidden="true"></span></button></th>
                <th scope="col" class="no-sort"> 생성완료</th>
                <th scope="col" class="no-sort">주의-경고</th>
                <th scope="col" class="no-sort">위험-고위험</th>
                <th scope="col" class="no-sort"> view URL</th>
                <th scope="col" class="no-sort"></th>
            </tr>
            </thead>
            <tbody id="adminTable">
            </tbody>
        </table>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </div>
</div>
<script src="/b***-*abc/resources/js/pharmacy/bioage.js"></script>