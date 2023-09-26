<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">상품 그룹 관리</h3>
            </div>
            <div class="form-group">
                <div class="searchContainer" style="display: block">
                    <!-- 검색영역 -->
                    <div class="row justify-content-end">
                        <div class="col-md-1" id="searchDiv">
                            <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                                <option value="">검색컬럼 선택</option>
                                <option value="pgm.ProductGroupIdx">그룹코드</option>
                                <option value="pg.ProductGroupName">상품그룹명</option>
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
                    <div class="col-sm-11 mt-3" style="text-align:right;">
                        <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="10" data-name="상품그룹관리" type="button">Excel</button>
                    </div>
                </div>
                <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="productGroupTable">
                    <thead>
                    <tr>
                        <th scope="col">번호</th>
                        <th scope="col">등록일자</th>
                        <th scope="col">그룹코드</th>
                        <th scope="col">상품그룹명</th>
                        <th scope="col">상품명1</th>
                        <th scope="col">상품명2</th>
                        <th scope="col">상품명3</th>
                        <th scope="col">상품명4</th>
                        <th scope="col">상품명5</th>
                        <th scope="col">옵션</th>
                    </tr>
                    </thead>
                    <tbody id="adminTable"></tbody>
                </table>
            </div>
            <div class="row">
                <nav class="nav col flex justify-content-center">
                    <ul class="pagination" id="pagination">

                    </ul>
                </nav>
            </div>
            <button class="btn btn-secondary float-end" data-bs-toggle="modal" data-bs-target="#registerProductGroup">
                상품그룹등록
            </button>
        </div>
        <div id="registerProductGroup" class="modal fade itemGroupInsert" tabindex="-1" data-bs-backdrop="static" role="dialog"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>상품그룹등록</strong></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                id="registerProductGroupCloseBtn"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <label for="productGroupName" class="col-form-label col-sm-4">
                                <span class="badge bg-danger">필수</span>  상품그룹명
                            </label>
                            <input type="text" id="productGroupName" name="productGroupName" class="form-control form-control-sm col" value="">
                        </div>
                        <div class="row m-auto p-1">
                            <label for="productList" class="col-form-label col-sm-4">
                                <span class="badge bg-danger">필수</span>  상품선택
                            </label>
                            <div class="card bg-light col" style="height: 7rem">
                                <div class="card-body p-1" id="productList" style="overflow: auto">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row m-auto ">
                            <div class="row m-auto p-1">
                                <label for="category" class="col-form-label col-sm-3 ">카테고리</label>
                                <select id="category" name="category" class="form-select form-select-sm col">
                                    <option value="0" selected>카테고리 선택</option>
                                </select>
                            </div>
                            <div class="row m-auto p-1 table-responsive">
                                <table class="table table-sm table-hover "
                                       id="productTable">
                                    <thead>
                                    <tr>
                                        <th scope="col">검사코드</th>
                                        <th scope="col">카테고리명</th>
                                        <th scope="col">상품명</th>
                                        <th scope="col">선택</th>
                                    </tr>
                                    </thead>
                                    <tbody id="childProduct"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary regist-btn" data-target="itemGroupInsert" data-value="group">상품그룹등록</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="editProductGroup" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>상품그룹 수정관리</strong></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                                id="editProductGroupCloseBtn"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="productGroupIdx" value="">
                        <div class="row m-auto p-1">
                            <label for="productGroupName_e" name="productGroupName_e" class="col-form-label col-sm-3">상품그룹명</label>
                            <input type="text" id="productGroupName_e" name="productGroupName_e" class="form-control form-control-sm col" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="productGroupEditBtn">수정</button>
                        <button type="button" class="btn btn-secondary" id="productGroupDeleteBtn">삭제</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/pharmacy/product.js"></script>