<div class="container-fluid">
    <div class="" style="margin: 10px">
        <h3 class="text-left">결제내역관리</h3>
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
                            <option value="pm.PayOrderIdx">주문번호</option>
                            <option value="pm.PayOrderCode">주문코드</option>
                            <option value="pm.PayMethod">결제수단</option>
                            <option value="pm.ItemsIdx">굿즈코드</option>
                            <option value="pm.GoodsName">굿즈명</option>
                            <option value="pm.PaysAmount">결제금액</option>
                            <option value="scm.ServiceCompanyName">사용처</option>
                            <option value="pm.CompanyName">회사명</option>
                            <option value="pm.BuyerName">결제자명</option>
                            <option value="pm.BuyerPhone">휴대폰번호</option>
                            <option value="pm.ApprovedDatetime">결제일시</option>
                            <option value="pm.PaysStatus">결제상태</option>
                            <option value="rm.RefundType">취소방법</option>
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
                <div class="col-sm-11 mt-3" style="text-align:right;">
                    <button class="btn btn-primary excel-down-btn" data-list="front" data-id="adminTable" data-hidden="1" data-name="얼리닥터_결제내역관리" type="button">Excel</button>
                </div>
            </div>
            <table class="table table-hover table-bordered text-nowrap sortable">
                <thead>
                <tr>
                    <th scope="col">선택</th>
                    <th scope="col">번호</th>
                    <th scope="col" data-column="pm.PayOrderIdx"><button class="sort-btn">주문번호<span aria-hidden="true"></span></button></th>
                    <th scope="col">관련주문번호</th>
                    <th scope="col" data-column="pm.PayOrderCode"><button class="sort-btn">주문코드<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.PayMethod"><button class="sort-btn">결제수단<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.ItemsIdx"><button class="sort-btn">굿즈코드<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.GoodsName"><button class="sort-btn">굿즈명<span aria-hidden="true"></span></button></th>
                    <th scope="col">결제금액</th>
                    <th scope="col" data-column="scm.ServiceCompanyName"><button class="sort-btn">사용처<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.CompanyName"><button class="sort-btn">회사명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.BuyerName"><button class="sort-btn">결제자명<span aria-hidden="true"></span></button></th>
                    <th scope="col" data-column="pm.BuyerPhone"><button class="sort-btn">휴대폰번호<span aria-hidden="true"></span></button></th>
                    <th scope="col">결제일시</th>
                    <th scope="col">결제상태</th>
                    <th scope="col">취소방법</th>
                </tr>
                </thead>
                <tbody id="adminTable"></tbody>
            </table>
            <ul class="pagination justify-content-center" id="pagination"></ul>
        </div>
        <div class="row">
            <div class="col-md-12 d-flex justify-content-end">
                <div>
                    <button type="button" class="btn btn-secondary modal-init-btn" data-bs-target="#payRefund" id="payRefundBtn">결제취소</button>
                </div>
            </div>
        </div>
    </div>
    <div id="payRefund" class="modal fade refundPayment" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">결제취소</h4>
                    <button type="button" class="btn-close closeModal" data-bs-dismiss="modal" aria-label="Close">
                    </button>
                </div>
                <div class="modal-body" style="font-size: small">
                    <form>
                        <input type="hidden" name="payOrderIdx" value="">
                        <input type="hidden" name="paidOrderQuantity" id="orderQuantity" value="">
                        <input type="hidden" name="salesPrice" value="">
                        <input type="hidden" name="couponCode" value="">
                        <input type="hidden" name="discountMethod" value="">
                        <input type="hidden" name="discount" value="">
                        <input type="hidden" name="totalDiscountAmount" value="">
                        <input type="hidden" name="approvedOrderAmount" value="">
                        <input type="hidden" name="kcpTno" value="">
                        <input type="hidden" name="payType" value="">
                        <ul>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>주문구분</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-check-input required-value" type="radio" name="orderType" id="orderType1" value="2" checked>
                                        <label class="form-check-label" for="orderType1">자동(KCP 취소 API)</label>
                                        <input class="form-check-input required-value" type="radio" name="orderType" id="orderType2" value="3">
                                        <label class="form-check-label" for="orderType2">수동(수기로 취소)</label>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>주문번호</p>
                                    </div>:
                                    <div class="col">
                                        <p id="payOrderCode"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>결제수단</p>
                                    </div>:
                                    <div class="col">
                                        <p id="payMethod"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>판매상품</p>
                                    </div>:
                                    <div class="col">
                                        <p id="goodsName"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>결제금액</p>
                                    </div>:
                                    <div class="col">
                                        <p id="approvedOrderAmount"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>사용처</p>
                                    </div>:
                                    <div class="col">
                                        <p id="serviceCompanyName"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>회사명</p>
                                    </div>:
                                    <div class="col">
                                        <p id="companyName"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>결제자명</p>
                                    </div>:
                                    <div class="col">
                                        <p id="buyerName"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>결제일시</p>
                                    </div>:
                                    <div class="col">
                                        <p id="approvedDatetime"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>취소구분</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-check-input readonly required-value" type="radio" name="refundType" id="refundType1" value="STSC">
                                        <label class="form-check-label" for="refundType1">전체취소</label>
                                        <input class="form-check-input readonly required-value" type="radio" name="refundType" id="refundType2" value="STPC">
                                        <label class="form-check-label" for="refundType2">부분취소</label>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>취소가능수량</p>
                                    </div>:
                                    <div class="col">
                                        <p id="remainOrderQuantity"></p>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>취소수량</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-control form-control-sm required-value" type="number" name="orderQuantity" value="" min=1 max=>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>결제취소금액</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-control form-control-sm required-value" type="text" name="orderAmount" value="" readonly>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>남은결제금액</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-control form-control-sm required-value" type="text" name="remainingAmount" value="" readonly>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="row">
                                    <div class="col-3">
                                        <p>취소사유</p>
                                    </div>:
                                    <div class="col">
                                        <input class="form-control form-control-sm required-value" type="text" name="refundDesc" value="" maxlength="100">
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary regist-btn" data-target="refundPayment">확인</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                </div>
            </div>
        </div>
    </div>
    <script src="/b***-*abc/resources/js/offerabc/payment.js"></script>