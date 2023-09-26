<div class="container-fluid">
    <div class="container-fluid" style="margin: 10px">
        <h3 class="text-center">**상품관리</h3>
    </div>
    <hr class="mb-3">
    <div class="searchContainer" style="display: block">
        <!-- 검색영역 -->
        <div class="row mb-1">
            <div class="col-md-1" id="searchDiv">
                <select id="searchColumn" class="form-select form-select-sm">
                    <option value="">검색컬럼 선택</option>
                    <option value="ibCompany">거래처</option>
                    <option value="insuranceIdx">**사식별코드</option>
                    <option value="insuranceCode">**사코드</option>
                    <option value="insuranceName">**사명</option>
                    <option value="itemCode">상품코드</option>
                    <option value="itemName">상품명</option>
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
    <hr class="mb-1">
    <div class="container-fluid table-responsive">
        <div class="row row-cols-auto">
            <div class="col col-auto" style="padding-top: 5.5px; padding-right: 1.0px">
                <label>Show</label>
            </div>
            <div class="col col-auto">
                <select class="form-select form-select-sm" id="rownum">
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="300">300</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="1500">1500</option>
                    <option value="2000">2000</option>
                    <option value="2500">2500</option>
                    <option value="3000">3000</option>
                </select>
            </div>
        </div>
        <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="insuranceItemTable">
            <thead>
            <tr>
                <th scope="col">번호</th>
                <th scope="col">거래처</th>
                <th scope="col">**사 식별코드</th>
                <th scope="col">**사코드</th>
                <th scope="col">**사명</th>
                <th scope="col">상품코드</th>
                <th scope="col">상품명</th>
                <th scope="col">옵션</th>
            </tr>
            </thead>

        </table>
    </div>
    <div class="row">
        <nav class="nav col flex justify-content-center">
            <ul class="pagination" id="pagination">

            </ul>
        </nav>
    </div>
    <div class="m-1 text-lg-end">
        <a class="link-info" href="https://g******daouoffice.com/app/board/26267/post/423377" target="_blank"><strong>엑셀 업로드 가이드</strong></a>
    </div>
    <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#registerInsuranceItem">등록</button>
</div>
<div id="registerInsuranceItem" class="modal fade" tabindex="-1" data-bs-backdrop="static" role="dialog"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><strong>**상품 등록하기  <span class="badge bg-danger">모든 항목 필수 입력</span></strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="registerInsuranceItemCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="row m-auto p-1">
                    <label for="registerType" class="col-form-label col-sm-3 ">등록 종류</label>
                    <select id="registerType" class="form-select form-select-sm col">
                        <option value="" selected>선택안함</option>
                        <option value="insurance">**사</option>
                        <option value="item">**상품</option>
                    </select>
                </div>
                <div class="row m-auto p-1">
                    <label for="ibCompanyIdx" id="ibCompanyLabel" class="col-form-label col-sm-3" style="display: none">거래처</label>
                    <select id="ibCompanyIdx" class="form-select form-select-sm col" style="display: none">
                        <option value="" selected>선택안함</option>
                    </select>
                </div>
                <hr>
                <div class="row m-auto p-1">
                    <label for="insuranceListInput">엑셀파일 선택 .(csv, xlsx, xls)</label>
                    <input type="file" id="insuranceListInput" class="form-control" value=""
                           accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="registerInsuranceItemBtn">등록</button>
            </div>
        </div>
    </div>
</div>
<div id="editInsuranceItem" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><strong>**상품 수정</strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="editInsuranceItemCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="row m-auto p-1">
                    <label for="ibCompany" class="col-form-label col-sm-5">
                        <span class="badge bg-danger">필수</span>  거래처
                    </label>
                    <input type="text" id="ibCompany" name="ibCompany"
                           class="form-control-plaintext form-control-sm col" value="" readonly="">
                </div>
                <input type="hidden" id="insuranceIdx" name="insuranceIdx" value="">
                <div class="row m-auto p-1">
                    <label for="insuranceCode" class="col-form-label col-sm-5">
                        <span class="badge bg-danger">필수</span>  **사 코드
                    </label>
                    <input type="text" id="insuranceCode" name="insuranceCode" class="form-control form-control-sm col"
                           value="">
                </div>
                <div class="row m-auto p-1">
                    <label for="insuranceName" class="col-form-label col-sm-5">
                        <span class="badge bg-danger">필수</span>  **사명
                    </label>
                    <input type="text" id="insuranceName" name="insuranceName" class="form-control form-control-sm col"
                           value="">
                </div>
                <input type="hidden" id="itemIdx" name="itemIdx" value="">
                <div class="row m-auto p-1">
                    <label for="itemCode" class="col-form-label col-sm-5">상품 코드</label>
                    <input type="text" id="itemCode" name="itemCode" class="form-control form-control-sm col" value="">
                </div>
                <div class="row m-auto p-1">
                    <label for="itemName" class="col-form-label col-sm-5">상품명</label>
                    <input type="text" id="itemName" name="itemName" class="form-control form-control-sm col" value="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="insuranceItemEditBtn">수정</button>
                <button type="button" class="btn btn-secondary" id="insuranceItemDeleteBtn">삭제</button>
            </div>
        </div>
    </div>
</div>
