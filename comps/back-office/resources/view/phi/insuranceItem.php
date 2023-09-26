<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">**상품관리</h3>
    </div>
    <div class="form-group">
        <div class="searchContainer" style="display: block">
            <!-- 검색영역 -->
            <div class="row justify-content-end">
                <div class="col-md-1" id="searchDiv">
                    <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                        <option value="">검색컬럼 선택</option>
                        <option value="scm.ServiceCompanyName">거래처</option>
                        <option value="pi.InsureanceIdx">**사식별코드</option>
                        <option value="pi.ItemCode">**사코드</option>
                        <option value="pi.ItemName">**사명</option>
                        <option value="i.ItemCode">상품코드</option>
                        <option value="i.ItemName">상품명</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <label for="searchValue"></label>
                        <input type="text" class="form-control form-control-sm col" name="searchValue" id="searchValue"
                               value="">
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
                    <th scope="col">거래처</th>
                    <th scope="col">**사 식별코드</th>
                    <th scope="col">**사코드</th>
                    <th scope="col">**사명</th>
                    <th scope="col">상품코드</th>
                    <th scope="col">상품명</th>
                    <th scope="col">옵션</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination">

            </ul>
            <div class="row">
                <div class="col-auto">
                    <div class="m-1 text-lg-start">
                        <a class="link-info" href="https://g******daouoffice.com/app/board/26267/post/423377"
                           target="_blank"><strong>엑셀 업로드 가이드</strong></a><br>
                        <a class="link-info" href="https://img.g******com/b***-*abc/template/**사등록.xlsx" target="_blank"><strong>**사 엑셀 업로드 샘플 다운로드</strong></a><br>
                        <a class="link-info" href="https://img.g******com/b***-*abc/template/**상품등록.xlsx" target="_blank"><strong>**상품 엑셀 업로드 샘플 다운로드</strong></a>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#insuranceItemInsertModal">등록
                    </button>
                </div>
            </div>
        </div>
        <div id="insuranceItemInsertModal" class="modal fade uploadInsuranceItem" tabindex="-1" role="dialog"
             aria-hidden="true">k
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>**상품 등록하기 <span class="badge bg-danger">모든 항목 필수 입력</span></strong>
                        </h1>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <label for="registerType" class="col-form-label col-sm-3 ">등록 종류</label>
                            <select id="registerType" class="form-select form-select-sm col" name="registerType">
                                <option value="" selected>선택안함</option>
                                <option value="insurance">**사</option>
                                <option value="item">**상품</option>
                            </select>
                        </div>
                        <div class="row m-auto p-1">
                            <label for="ServiceControlIdx" id="serviceCompanyLabel" class="col-form-label col-sm-3" style="display: none;">거래처</label>
                            <select id="serviceCompany" class="form-select form-select-sm col" name="ServiceControlIdx" style="display: none;">
                                <option value="" selected>선택안함</option>
                            </select>
                        </div>
                        <hr>
                        <div class="row m-auto p-1">
                            <label for="insuranceItemFile">엑셀파일 선택 .(csv, xlsx, xls)</label>
                            <input type="file" id="insuranceItemFile" name="insuranceItemFile" class="form-control"
                                   value=""
                                   accept="text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary excel-btn" data-target="uploadInsuranceItem">등록
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div id="insuranceItemUpdate" class="modal fade updateInsuranceItem" tabindex="-1" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>**상품 수정</strong></h1>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="parentInsureanceIdx" name="parentInsureanceIdx" value="">
                        <input type="hidden" id="InsureanceIdx" name="InsureanceIdx" value="">
                        <div class="mb-3 row">
                            <label for="serviceCompanyName" class="col-form-label col-5">
                                거래처
                            </label>
                            <input type="text" id="serviceCompanyName" name="serviceCompanyName"
                                   class="form-control-plaintext form-control-sm col" value="" readonly="readonly">
                        </div>
                        <div class="mb-3 row">
                            <label for="parentItemCode" class="col-form-label col-5 required-value">
                                <span class="badge bg-danger">필수</span> **사 코드
                            </label>
                            <input type="text" id="parentItemCode" name="parentItemCode"
                                   class="form-control form-control-sm col"
                                   value="">
                        </div>
                        <div class="mb-3 row">
                            <label for="parentItemName" class="col-form-label col-5 required-value">
                                <span class="badge bg-danger">필수</span> **사명
                            </label>
                            <input type="text" id="parentItemName" name="parentItemName"
                                   class="form-control form-control-sm col"
                                   value="">
                        </div>
                        <div class="mb-3 row">
                            <label for="itemCode" class="col-form-label col-5">상품 코드</label>
                            <input type="text" id="itemCode" name="itemCode" class="form-control form-control-sm col"
                                   value="">
                        </div>
                        <div class="mb-3 row">
                            <label for="itemName" class="col-form-label col-5">상품명</label>
                            <input type="text" id="itemName" name="itemName" class="form-control form-control-sm col"
                                   value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary regist-btn" data-target="updateInsuranceItem">수정
                        </button>
                        <button type="button" class="btn btn-secondary removeBtn" data-target="deleteInsuranceItem">
                            삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/abc/insuranceItem.js"></script>