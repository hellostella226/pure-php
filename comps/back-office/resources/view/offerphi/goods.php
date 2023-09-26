<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">결제경로 관리</h3>
    </div>
    <div class="form-group">
        <hr>
        <div class="container-fluid table-responsive">
            <div class="row">
                <!-- 검색영역 -->
                <div class="row justify-content-end">
                    <div class="col-md-1" id="searchDiv">
                        <select id="searchColumn" name="searchColumn" class="form-select form-select-sm">
                            <option value="">검색컬럼 선택</option>
                            <option value="scm.ServiceCompanyName">사용처</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="row">
                            <label for="searchValue"></label>
                            <input type="text" class="form-control form-control-sm col" name="searchValue"
                                   id="searchValue" value="">
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
            </div>
            <table class="table table-hover table-bordered text-nowrap sortable">
                <thead>
                <tr>
                    <th scope="col">선택</th>
                    <th scope="col" data-column="gm.RegDatetime"><button class="sort-btn">등록일자<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="gm.ItemsIdx"><button class="sort-btn">굿즈코드<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="scm.ServiceCompanyName"><button class="sort-btn">사용처<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="gm.GoodsName"><button class="sort-btn">굿즈명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="gm.SalesPrice"><button class="sort-btn">계약단가<span aria-hidden="true"></span></button></th>
                    <th scope="col">결제 URL</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex justify-content-end">
                <div>
                    <button type="button" class="btn btn-primary modal-init-btn" data-bs-target="#Items" id="registerGoods">등록</button>
                    <button type="button" class="btn btn-info" id="editGoods">수정</button>
                    <button type="button" class="btn btn-secondary" id="deleteGoods">삭제</button>
                </div>
            </div>
        </div>
    </div>
    <div id="Items" class="modal fade registGoods" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="goodsModalTitle"></h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" name="ItemsIdx" value="">
                        <input type="hidden" name="ItemsType" value="">
                        <div class="mb-1 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 사용처</p>
                            <p class="col-sm-8" id="serviceCompanyList">
                                <select class="form-select form-select-sm required-value" id="serviceCompany" name="ServiceControlIdx">
                                    <option value="">사용처 선택</option>
                                </select>
                            </p>
                        </div>
                        <div class="mb-1 row">
                            <p class="col-sm-4">굿즈명</p>
                            <p class="col-sm-8">
                                <input type="text" id="goodsName" name="goodsName"
                                       class="form-control form-control-sm" value="" maxlength="20">
                            </p>
                        </div>
                        <div class="mb-1 row">
                            <p class="col-sm-4"><span class="badge bg-danger">필수</span> 계약단가</p>
                            <p class="col-sm-8">
                                <input type="text" id="salesPrice" name="salesPrice"
                                       class="form-control form-control-sm required-value" value="" maxlength="20">
                            </p>
                        </div>
                    </form>
                </div>
                <div class="goodsModalContent"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="registGoods"></button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                </div>
            </div>
        </div>
    </div>
    <div id="goodsDelete" class="modal fade deleteGoods" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="goodsDelModalTitle">기존 결제 경로 삭제</h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" name="ItemsIdx" value="">
                        <div class="col-9" id="goodsInfo" style="margin-left: 12%">

                        </div>
                        <div>
                            <p class="text-center">삭제한 데이터는 복구가 어려울 수 있습니다.<br>그래도 삭제한다면 삭제를 눌러주세요.</p>
                            <p class="text-center"></p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="deleteGoods">삭제</button>
                </div>
            </div>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/offerabc/good.js"></script>