<div class="container-fluid">
    <div class="container-fluid" style="margin: 10px">
        <h3 class="text-center">xxxxBS 테스트정보</h3>
    </div>
    <hr class="mb-1">
    <div class="searchContainer" style="display: block">
        <div class="row mb-1">
            <div class="col-md-auto m-1" style="border-right: 1px solid rgba(0, 0, 0, .125)">
                <div class="input-group m-1">
                    <button class="btn btn-sm btn-primary addSearch" type="button">+</button>
                    <button class="btn btn-sm btn-secondary removeSearch" type="button">-</button>
                </div>
            </div>
            <div class="col-md-9 m-1" id="searchDiv">
                <div class="row">
                    <div class="col-sm-auto m-1" style="border-right: 1px solid rgba(0, 0, 0, .125)">
                        <select class="form-select form-select-sm searchColumn" onchange="showDiv(this)">
                            <option value="none">검색컬럼 선택</option>
                            <option value="MembersIdx">xxxxID</option>
                            <option value="Name">이름</option>
                            <option value="GCRegDate">등록일자</option>
                            <option value="GCRegNo">등록번호</option>
                            <option value="ALL_AGRE_YN">전체 동의여부</option>
                            <option value="NHisData">건강검진이력</option>
                            <option value="***Data">테스트 결과</option>
                        </select>
                    </div>
                    <div class="col-md-auto m-1">
                        <div class="form-group searchItem" id="none" style="display: block">
                            <select class="form-select form-select-sm">
                                <option value="none">검색컬럼 선택</option>
                            </select>
                        </div>
                        <div class="form-group searchItem" id="SearchItemBar" style="display: none">
                            <input type="text" id="searchValue" value=""
                                   class="form-control form-control-sm searchBox">
                        </div>
                        <div class="form-group searchItem" id="DateItemBar" style="display: none">
                            <div class="input-group">
                                <input type="date" id="minDate" class="form-control form-control-sm">
                                <input type="date" id="maxDate" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="form-group searchItem" id="YnItemBar" style="display: none">
                            <select class="form-select form-select-sm">
                                <option value="Y">Y</option>
                                <option value="N">N</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-auto ms-auto m-1" style="border-left: 1px solid rgba(0, 0, 0, .125)">
                <div class="input-group m-1">
                    <button class="btn btn-sm btn-primary btn-search" type="button">검색</button>
                </div>
            </div>
        </div>
        <hr class="m-1">
        <div class="row searchBar" style="display: none">
            <div class="col-md-auto">
                <div class="text-start" id="searchList">
                    <strong>검색조건: </strong>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="container-fluid table-responsive">
        <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="***Table">
            <thead>
            <tr>
                <th scope="col">xxxxID</th>
                <th scope="col">이름</th>
                <th scope="col">등록일자</th>
                <th scope="col">등록번호</th>
                <th scope="col">전체<br>동의여부</th>
                <th scope="col">건강검진이력</th>
                <th scope="col">테스트<br>결과</th>
                <th scope="col">더보기</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="***Modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="***ModalTitle"></h4>
                    <button type="button" class="btn-close closeModal" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="MembersId" name="MembersIdx" value="">
                    <div class="mb-3 row">
                        <p class="col-sm-5">최근 건강검진 연도</p>
                        <p class="col-sm-7" name="recentYear" id="viewRecentYear"></p>
                    </div>
                    <div class="mb-3 row">
                        <p class="col-sm-5">테스트 생성일자</p>
                        <p class="col-sm-7" name="reportDate" id="viewReportDate"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary closeModal" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
