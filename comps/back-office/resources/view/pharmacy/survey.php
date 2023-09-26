<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">설문응답</h3>
    </div>
    <hr class="mb-1">
    <div class="row">
        <!-- 검색영역 -->
        <div class="row justify-content-end">
            <div class="col-md-1" id="searchDiv">
                <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                    <option value="">검색컬럼 선택</option>
                    <option value="prm.CalcDate">검사일</option>
                    <option value="ed.RegDatetime">신청일</option>
                    <option value="mm.UsersIdx">회원ID</option>
                    <option value="m.Name">이름</option>
                    <option value="m.Phone">전화번호(01012345678)</option>
                    <option value="e.IsOut">설문이탈(Y,N)</option>
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
            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="12" data-name="얼리큐_설문응답" type="button">Excel</button>
        </div>
    </div>
    <div class="container-fluid table-responsive">
        <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="diseaseTable">
            <thead>
            <tr>
                <th>번호</th>
                <th>검사일</th>
                <th>설문일</th>
                <th>회원ID</th>
                <th>이름</th>
                <th>전화번호</th>
                <th>설문이탈</th>
                <th>답변1</th>
                <th>답변2</th>
                <th>답변3</th>
                <th>답변4</th>
                <th>옵션</th>
            </tr>
            </thead>
            <tbody id="adminTable">
            </tbody>
        </table>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </div>
    <div id="modifyModal" class="modal fade updateSurveyData" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <input type="hidden" id="orderIdx" name="orderIdx" value="">
                <input type="hidden" id="productIdx" name="productIdx" value="">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"><strong>설문 답변 입력</strong></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"  aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row m-auto p-1">
                        <label for="UsersIdx" class="col-form-label col-sm-4 ">회원ID</label>
                        <input readonly="readonly" type="text" id="UsersIdx" name="UsersIdx" class="form-control form-control-sm col" value="">
                    </div>
                    <div class="row m-auto p-1">
                        <label for="MembersName" class="col-form-label col-sm-4 ">회원명</label>
                        <input readonly="readonly" type="text" id="MembersName" name="MembersName" class="form-control form-control-sm col" value="">
                    </div>

                    <div class="row m-auto p-1">
                        <label for="consultAgree" class="col-form-label col-sm-4">
                            <span class="badge badge-sm bg-danger">필수</span>  문항1 답변
                        </label>
                        <input type="text" id="question1" name="question[0]" class="form-control form-control-sm col form-check-input required-value" value="">
                    </div>
                    <div class="row m-auto p-1">
                        <label for="consultAgree" class="col-form-label col-sm-4">
                            <span class="badge badge-sm bg-danger">필수</span>  문항2 답변
                        </label>
                        <input type="text" id="question2" name="question[1]" class="form-control form-control-sm col form-check-input required-value" value="">
                    </div>
                    <div class="row m-auto p-1">
                        <label for="consultAgree" class="col-form-label col-sm-4">
                            <span class="badge badge-sm bg-danger">필수</span>  문항3 답변
                        </label>
                        <input type="text" id="question3" name="question[2]" class="form-control form-control-sm col form-check-input required-value" value="">
                    </div>
                    <div class="row m-auto p-1">
                        <label for="consultAgree" class="col-form-label col-sm-4">
                            <span class="badge badge-sm bg-danger">필수</span>  문항4 답변
                        </label>
                        <input type="text" id="question4" name="question[3]" class="form-control form-control-sm col form-check-input required-value" value="">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="updateSurveyData">수정</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/pharmacy/survey.js"></script>