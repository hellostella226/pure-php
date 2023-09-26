<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">xxx 검사 동의서 미발송 리스트</h3>
    </div>
    <div class="form-group">
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
                    <th scope="col">병원ID</th>
                    <th scope="col">병원명</th>
                    <th scope="col">회원ID</th>
                    <th scope="col">이름</th>
                    <th scope="col">오류일</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination" id="pagination">

            </ul>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/abc/agreementFail.js"></script>