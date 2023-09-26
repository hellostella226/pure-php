<div class="container-fluid">
    <div class="container-fluid" style="margin: 10px">
        <h3 class="text-center">상품등록</h3>
    </div>
    <hr class="mb-3">
    <div class="searchContainer" style="display: block">
        <!-- 검색영역 -->
        <div class="row mb-1">
            <div class="col-md-1" id="searchDiv">
                <select id="searchColumn" class="form-select form-select-sm">
                    <option value="">검색컬럼 선택</option>
                    <option value="productCode">상품코드</option>
                    <option value="categoryName">카테고리명</option>
                    <option value="productName">상품명</option>
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
        <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="productTable">
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

        </table>
    </div>
    <div class="row">
        <nav class="nav col flex justify-content-center">
            <ul class="pagination" id="pagination">

            </ul>
        </nav>
    </div>
    <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#registerProduct">상품등록</button>
</div>
<div id="registerProduct" class="modal fade" tabindex="-1" data-bs-backdrop="static" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><strong>상품등록</strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"  aria-label="Close" id="registerProductCloseBtn"></button>
            </div>
            <div class="modal-body">
                <div class="row m-auto p-1">
                    <label for="category" class="col-form-label col-sm-4">
                        <span class="badge bg-danger">필수</span>  카테고리명
                    </label>
                    <select id="category" name="category" class="form-select form-select-sm col">
                        <option value="" selected>카테고리 선택</option>
                    </select>
                </div>

                <div class="row m-auto p-1">
                    <label for="productName" class="col-form-label col-sm-4">
                        <span class="badge badge-sm bg-danger">필수</span>  상품명
                    </label>
                    <input type="text" id="productName" class="form-control form-control-sm col" value="">
                </div>

                <div class="row m-auto p-1">
                    <label for="subdivision" class="col-form-label col-sm-4 ">세부구분</label>
                    <select id="subdivision" class="form-select form-select-sm col">
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
                                    <input type="text" id="catalogCode" class="form-control form-control-sm col"
                                           value="">
                                </div>
                                <div class="row m-auto p-1">
                                    <label for="catalogName" class="col-form-label col-sm-3">항목명</label>
                                    <input type="text" id="catalogName" class="form-control form-control-sm col"
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
                <button type="button" class="btn btn-primary" id="registerProductBtn">상품등록</button>
            </div>
        </div>
    </div>
</div>
<div id="editProduct" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><strong>상품수정</strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="editProductCloseBtn"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="productIdx" value="">
                <div class="row m-auto p-1">
                    <label for="category_e" class="col-form-label col-sm-4">
                        <span class="badge bg-danger">필수</span>  카테고리명
                    </label>
                    <select id="category_e" name="category" class="form-select form-select-sm col">
                        <option value="" selected>카테고리 선택</option>
                    </select>
                </div>
                <div class="row m-auto p-1">
                    <label for="productName_e" class="col-form-label col-sm-4">
                        <span class="badge bg-danger">필수</span>  상품명
                    </label>
                    <input type="text" id="productName_e" class="form-control form-control-sm col" value="">
                </div>
                <div class="row m-auto p-1">
                    <label for="subdivision_e" class="col-form-label col-sm-4">세부구분</label>
                    <select id="subdivision_e" class="form-select form-select-sm col">
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
                                    <label for="catalogCode_e" class="col-form-label col-sm-3">항목코드</label>
                                    <input type="text" id="catalogCode_e" class="form-control form-control-sm col"
                                           value="">
                                </div>
                                <div class="row m-auto p-1">
                                    <label for="catalogName_e" class="col-form-label col-sm-3">항목명</label>
                                    <input type="text" id="catalogName_e" class="form-control form-control-sm col"
                                           value="">
                                </div>
                                <div class="row m-auto p-1">
                                    <div class="card bg-light col" style="height: 7rem">
                                        <div class="card-body p-1 col" id="catalogList_e" style="overflow: auto">

                                        </div>
                                    </div>
                                </div>
                                <div class="row m-auto">
                                    <div class="col p-1 m-0">
                                        <button type="button" class="btn btn-sm btn-secondary float-start"
                                                name="removeAllCatalog" id="removeAllCatalog_e">전체삭제
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info float-end" name="addCatalog" id="addCatalog_e">
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
                <button type="button" class="btn btn-primary" id="productEditBtn">상품수정</button>
                <button type="button" class="btn btn-secondary" id="productDeleteBtn">등록삭제</button>
            </div>
        </div>
    </div>
</div>
<div id="catalogModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><strong>검사항목</strong></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="productCatalogTable">
                    <thead>
                    <tr>
                        <th scope="col">번호</th>
                        <th scope="col">사용코드</th>
                        <th scope="col">항목코드</th>
                        <th scope="col">항목명</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div   >