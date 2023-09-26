<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">** 거래처</h3>
            </div>
            <hr class="mb-1">
            <div class="row">
                <!-- 검색영역 -->
                <div class="row justify-content-end">
                    <div class="col-md-1" id="searchDiv">
                        <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                            <option value="">검색컬럼 선택</option>
                            <option value="ASMH.RegDateTime">등록일자</option>
                            <option value="SCM.ServiceCompanyName">**거래처명</option>
                            <option value="ASMH.TransferMethodCode">전송방식</option>
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
                    <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="7" data-name="얼리큐_**거래처" type="button">Excel</button>
                </div>
            </div>
            <div class="container-fluid table-responsive">
                <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="diseaseTable">
                    <thead>
                    <tr>
                        <th>번호</th>
                        <th>등록일자</th>
                        <th>**거래처명</th>
                        <th>전송방식</th>
                        <th>총 제공량</th>
                        <th>주간 제공량</th>
                        <th>옵션</th>
                    </tr>
                    </thead>
                    <tbody id="adminTable">
                    </tbody>
                </table>
                <ul class="pagination justify-content-center" id="pagination"></ul>
            </div>
            <div class="row">
                <div class="col-md-6">

                </div>
                <div class="col-md-6">

                    <div class="input-group justify-content-end">
                        <button type="button" class="btn btn-secondary modal-init-btn" data-bs-toggle="modal"
                                data-bs-target="#insuranceUpdate" id="register">등록
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="insuranceUpdate" class="modal fade insuranceUpdate" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="allocationModalTitle"></h4>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <input type="hidden" id="ServeAllocationHistoryIdx" name="ServeAllocationHistoryIdx" value="">
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> ** 거래처명</p>
                                <p class="col-sm-8" id="companyList">
                                    <select class="form-select form-select-sm required-value" id="ServiceControl" name="ServiceControl">
                                        <option value="">**사명 선택</option>
                                    </select>
                                </p>
                            </div>
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> 전송방식</p>
                                <p class="col-sm-8">
                                    <select class="form-select form-select-sm required-value" id="transferMethod" name="transferMethod">
                                        <option value="">전송방식 선택</option>
                                        <option value="1">API 전송</option>
                                        <option value="2">수동 전송</option>
                                    </select>
                                </p>
                            </div>
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> 파일럿 여부</p>
                                <p class="col-sm-8">
                                    <select class="form-select form-select-sm required-value" id="isPilot" name="isPilot">
                                        <option value="">파일럿 여부 선택</option>
                                        <option value="1">Y</option>
                                        <option value="0">N</option>
                                    </select>
                                </p>
                            </div>
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> 할당방법</p>
                                <p class="col-sm-8">
                                    <select class="form-select form-select-sm required-value" id="isManual" name="isManual">
                                        <option value="">할당방법 선택</option>
                                        <option value="1">수동할당</option>
                                        <option value="0">자동할당</option>
                                    </select>
                                </p>
                            </div>
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> 총 제공량</p>
                                <p class="col-sm-8">
                                    <input type="text" id="totalServeLimit" name="totalServeLimit" class="form-control form-control-sm required-value" value="">
                                </p>
                            </div>
                            <div class="mb-3 row">
                                <p class="col-sm-4"><span class="badge bg-danger">필수</span> 주간 제공량</p>
                                <p class="col-sm-8">
                                    <input type="text" id="weekServeLimit" name="weekServeLimit" class="form-control form-control-sm required-value" value="">
                                </p>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary regist-btn" data-target="insuranceUpdate">등록</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    @media (min-width: 992px) {
        .left-side {
            padding-right: 0px;
        }
        .sub-menu-left {
            padding-left : 0px; padding-right : 0px;
        }
        .sub-menu-left button {
            width: 100%;
        }
        .col-lg-1 {
            width: 150px !important;
        }
        .col-lg-11 {
            width: calc(100% - 155px) !important;
        }
    }
</style>
<script src="/b***-*abc/resources/js/pharmacy/insurance.js"></script>