<div class="container-fluid">
    <div class="container-fluid" style="margin: 10px">
        <h3 class="text-center">xxxxBS 회원정보</h3>
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
                            <option value="Birth">생년월일</option>
                            <option value="Gender">성별</option>
                            <option value="Phone">전화번호</option>
                            <option value="Email">이메일</option>
                            <option value="RegDatetime">회원등록일자</option>
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
                        <div class="form-group searchItem" id="GenderItemBar" style="display: none">
                            <select class="form-select form-select-sm">
                                <option value="1">남</option>
                                <option value="2">여</option>
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
        <table class="table table-hover table-bordered text-nowrap" style="width:100%" id="MembersTable">
            <thead>
            <tr>
                <th scope="col">xxxxID</th>
                <th scope="col">이름</th>
                <th scope="col">생년월일</th>
                <th scope="col">성별</th>
                <th scope="col">전화번호</th>
                <th scope="col">이메일</th>
                <th scope="col">회원등록일자</th>
                <th scope="col">옵션</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div id="MembersEditModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="MembersModalTitle"></h4>
                    <button type="button" class="btn-close closeModal" data-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="MembersId" name="MembersIdx" value="">
                    <div class="mb-3 row">
                        <label for="inputName" class="col-sm-3 col-form-label">이름</label>
                        <div class="col-sm-9">
                            <input type="text" name="name" class="form-control" id="inputName" value="" disabled>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputBirth" class="col-sm-3 col-form-label">생년월일</label>
                        <div class="col-sm-9">
                            <input type="text" name="birth" class="form-control" id="inputBirth" value="" disabled>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputGender" class="col-sm-3 col-form-label">성별</label>
                        <div class="col-sm-9">
                            <select class="form-select" name="gender" id="inputGender" disabled>
                                <option value="남">남</option>
                                <option value="여">여</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputPhone" class="col-sm-3 col-form-label">전화번호</label>
                        <div class="col-sm-9">
                            <input type="text" name="phone" class="form-control" id="inputPhone" value="" disabled>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label for="inputEmail" class="col-sm-3 col-form-label">이메일</label>
                        <div class="col-sm-9">
                            <input type="text" name="email" class="form-control" id="inputEmail" value="" disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary confirmModal" data-dismiss="modal">Edit</button>
                    <button type="button" class="btn btn-secondary closeModal" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
