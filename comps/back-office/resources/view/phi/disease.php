<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">질환검사</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="prm.RegDatetime">검사일(YYYY-mm-dd)</option>
                        <option value="prm.UsersIdx">회원ID</option>
                        <option value="m.Name">이름</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <label for="searchValue"></label>
                        <input type="text" class="form-control form-control-sm col" id="searchValue" name="searchValue" value="">
                        <button class="btn btn-sm btn-info col-md-3" id="searchBtn">검색</button>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="container-fluid table-responsive">
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
            </div>
            <table class="table table-hover table-bordered text-nowrap sortable" style="width:100%">
                <thead>
                <tr>
                    <th scope="col" class="no-sort">번호</th>
                    <th scope="col" data-column="prm.RegDatetime"><button class="sort-btn">검사일<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="prm.UsersIdx"><button class="sort-btn" >회원ID<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="m.Name"><button class="sort-btn" >이름<span aria-hidden="true"></span></button></th>
                    <th scope="col"></th>
                    <th scope="col">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/abc/disease.js"></script>