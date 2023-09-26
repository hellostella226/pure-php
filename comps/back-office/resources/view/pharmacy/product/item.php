<div class="row">
    <div class="col-lg-1 sub-menu-left left-side" style="background-color: #f8f9fa">
    </div>
    <div class="col-lg-11">
        <div class="container-fluid">
            <div class="" style="margin: 10px">
                <h3 class="text-left">상품 관리</h3>
            </div>
            <div class="form-group">
                <div class="searchContainer" style="display: block">
                    <!-- 검색영역 -->
                    <div class="row justify-content-end">
                        <div class="col-md-1" id="searchDiv">
                            <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                                <option value="">검색컬럼 선택</option>
                                <option value="p.ProductIdx">상품코드</option>
                                <option value="pp.ProductName">카테고리명</option>
                                <option value="p.ProductName">상품명</option>
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
                            <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="8" data-name="상품품목관리" type="button">Excel</button>
                        </div>
                    </div>
                    <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                        <thead>
                        <tr>
                            <th scope="col">번호</th>
                            <th scope="col">등록일자</th>
                            <th scope="col">상품코드</th>
                            <th scope="col">카테고리명</th>
                            <th scope="col">상품명</th>
                            <th scope="col">세부구분명</th>
                            <th scope="col">항목수</th>
                            <th scope="col">옵션</th>
                        </tr>
                        </thead>
                        <tbody id="adminTable"></tbody>
                    </table>
                </div>
                <ul class="pagination" id="pagination">

                </ul>
            </div>
            <button class="btn btn-primary float-end modal-init-btn" data-bs-toggle="modal" data-bs-target="#registerProduct">상품등록</button>
        </div>
        <div id="registerProduct" class="modal fade registerProduct" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <input type="hidden" id="productIdx" name="productIdx" value="">
                    <input type="hidden" id="deleteProductIdxArr" name="deleteProductIdxArr" value="">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>상품등록</strong></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"  aria-label="Close" id="registerProductCloseBtn"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row m-auto p-1">
                            <label for="category" class="col-form-label col-sm-4">
                                <span class="badge bg-danger">필수</span> 카테고리명
                            </label>
                            <select id="category" name="categoryIdx" class="form-select form-select-sm col required-value">
                                <option value="" selected>카테고리 선택</option>
                            </select>
                        </div>

                        <div class="row m-auto p-1">
                            <label for="productName" class="col-form-label col-sm-4">
                                <span class="badge badge-sm bg-danger">필수</span> 상품명
                            </label>
                            <input type="text" name="productName" id="productName"
                                   class="form-control form-control-sm col required-value" value="">
                        </div>

                        <div class="row m-auto p-1">
                            <label for="subdivision" class="col-form-label col-sm-4 ">세부구분</label>
                            <select id="subdivision" name="subdivision" class="form-select form-select-sm col">
                                <option value="" selected>선택안함</option>
                                <option value="1">남성</option>
                                <option value="2">여성</option>
                            </select>
                        </div>
                        <hr>
                        <div class="row m-auto ">
                            <div class="catalog p-1">
                                <div class="card bg-light">
                                    <div class="card-header">검사항목 등록</div>
                                    <div class="card-body">
                                        <div class="row m-auto p-1">
                                            <label for="catalogCode" class="col-form-label col-sm-3">항목코드</label>
                                            <input type="text" id="catalogCode" name="catalogCode" class="form-control form-control-sm col"
                                                   value="">
                                        </div>
                                        <div class="row m-auto p-1">
                                            <label for="catalogName" class="col-form-label col-sm-3">항목명</label>
                                            <input type="text" id="catalogName" name="catalogName" class="form-control form-control-sm col"
                                                   value="">
                                        </div>
                                        <div class="row m-auto p-1">
                                            <div class="card bg-light col" style="height: 7rem">
                                                <div class="card-body p-1" id="catalogList" style="overflow: auto">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row m-auto">
                                            <div class="col p-1 m-0">
                                                <input type="hidden" id="delIdx" value="">
                                                <button type="button" class="btn btn-sm btn-secondary float-start"
                                                        name="removeAllCatalog">전체삭제
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info float-end" name="addCatalog">
                                                    항목추가
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary regist-btn" id="" data-target="registerProduct" data-value="item">상품등록</button>
                        <button type="button" class="btn btn-secondary" id="removeBtn" data-value="item" style="display: none">상품삭제</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="product-catalog" class="modal fade" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5"><strong>항목 상세</strong></h1>
                        <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-hover table-bordered text-nowrap" style="width:100%">
                            <thead>
                            <tr>
                                <th scope="col">번호</th>
                                <th scope="col">사용코드</th>
                                <th scope="col">항목코드</th>
                                <th scope="col">항목명</th>
                            </tr>
                            </thead>
                            <tbody id="productCatalogTable">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/b***-*abc/resources/js/pharmacy/product.js"></script>