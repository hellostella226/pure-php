<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">xxx검사신청</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="o.RegDatetime">신청일자(YYYY-mm-dd)</option>
                        <option value="ccm.ClientCustomerName">병원명</option>
                        <option value="gcmi.UsersIdx">회원ID</option>
                        <option value="m.Name">이름</option>
                        <option value="gcmi.GCRegDate">랩지접수일</option>
                        <option value="gcmi.GCRegNo">랩지등록번호</option>
                        <option value="ccm.ResponseType">방법</option>
                        <option value="gcmi.IsSend">전달완료여부</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <label for="searchValue"></label>
                        <input type="text" class="form-control form-control-sm col" id="searchValue" value="">
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
            <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                <thead>
                <tr>
                    <th scope="col">번호</th>
                    <th scope="col">신청일</th>
                    <th scope="col">병원명</th>
                    <th scope="col">회원ID</th>
                    <th scope="col">이름</th>
                    <th scope="col">랩지접수일</th>
                    <th scope="col">랩지등록번호</th>
                    <th scope="col"> 방법</th>
                    <th scope="col">전달완료여부</th>
                    <th scope="col">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/abc/genetic.js"></script>