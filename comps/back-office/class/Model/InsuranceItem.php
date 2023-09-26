<?

namespace Model;

use Matrix\Exception;
use PDOFactory;

class InsuranceItem extends PDOFactory
{
    protected $pdo = null;
    protected $dbConnection = null;
    protected $fieldList = [
        'ibCompany' => [
            'fieldName' => "SCM.ServiceCompanyName",
            'expression' => "LIKE",
            'type' => "string",
        ],
        'insuranceIdx' => [
            'fieldName' => "PI.InsureanceIdx",
            'expression' => "=",
            'type' => "int",
        ],
        'insuranceCode' => [
            'fieldName' => "PI.ItemCode",
            'expression' => "LIKE",
            'type' => "string",
        ],
        'insuranceName' => [
            'fieldName' => "PI.ItemName",
            'expression' => "LIKE",
            'type' => "string",
        ],
        'itemCode' => [
            'fieldName' => "I.ItemCode",
            'expression' => "LIKE",
            'type' => "string",
        ],
        'itemName' => [
            'fieldName' => "I.ItemName",
            'expression' => "LIKE",
            'type' => "string",
        ],
    ];

    public function __construct()
    {
        $this->pdo = new PDOFactory();
        $this->dbConnection = $this->pdo->PDOCreate();
    }

    /**
     * **사 및 **상품 데이터 조회 Model
     * @param array $params
     * @return array|false
     */
    public function selectInsuranceItemList(array $params)
    {
        $pagination = $params['pagination'];
        $search = $params['search'];

        // 기본 쿼리문
        $sql = "SELECT 
                    SCM.ServiceCompanyName, PI.InsureanceIdx, PI.ItemCode AS InsuranceCode, 
                    PI.ItemName AS InsuranceName, I.InsureanceIdx AS InsuranceItemManageIdx, I.ItemCode, I.ItemName
                FROM abc.Insureance PI
                JOIN abc.ServiceControl SCM ON SCM.ServiceControlIdx = PI.ServiceControlIdx
                LEFT JOIN abc.Insureance I ON I.ParentItemIdx = PI.InsureanceIdx AND I.IsUse = b'1' AND I.ParentItemIdx IS NOT NULL
                WHERE SCM.IsContract = b'1' AND PI.ParentItemIdx IS NULL AND PI.IsUse = b'1'";

        // 조건절
        $field = [];
        if (isset($search['column'], $search['value']) && $search['column'] && $search['value']) {
            $field = $this->fieldList[$search['column']];
            $sql .= " AND {$field['fieldName']} {$field['expression']} :{$search['column']}";
        }
        // pagination
        $sql .= " LIMIT :startNo, :rowNum";

        $stmt = $this->dbConnection->prepare($sql);
        if (isset($search['column'], $search['value']) && $search['column'] && $search['value']) {
            if ($field['type'] === 'int') {
                $stmt->bindValue(":{$search['column']}", $search['value'], $this->dbConnection::PARAM_INT);
            } else {
                $stmt->bindValue(":{$search['column']}", $search['value'] . "%");
            }
        }
        $stmt->bindValue(':startNo', $pagination['startNo'], $this->dbConnection::PARAM_INT);
        $stmt->bindValue(':rowNum', $pagination['rowNum'], $this->dbConnection::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

        return $result;
    }

    /**
     * pagination을 위한 총 카운트 조회
     * @param array $params
     * @return int
     */
    public function getInsuranceItemCnt(array $params)
    {
        $search = $params['search'];

        $sql = "SELECT 
                    COUNT(*) AS InsuranceItemCnt	
                FROM abc.Insureance PI
                JOIN abc.ServiceControl SCM ON SCM.ServiceControlIdx = PI.ServiceControlIdx
                LEFT JOIN abc.Insureance I ON I.ParentItemIdx = PI.InsureanceIdx AND I.IsUse = b'1' 
                AND I.ParentItemIdx IS NOT NULL
                WHERE SCM.IsContract = b'1' AND PI.ParentItemIdx IS NULL AND PI.IsUse = b'1'";
        // 조건절
        $field = [];
        if (isset($search['column'], $search['value']) && $search['column'] && $search['value']) {
            $field = $this->fieldList[$search['column']];
            $sql .= " AND {$field['fieldName']} {$field['expression']} :{$search['column']}";
        }
        $stmt = $this->dbConnection->prepare($sql);
        if (isset($search['column'], $search['value']) && $search['column'] && $search['value']) {
            if ($field['type'] === 'int') {
                $stmt->bindValue(":{$search['column']}", $search['value'], $this->dbConnection::PARAM_INT);
            } else {
                $stmt->bindValue(":{$search['column']}", $search['value'] . "%");
            }
        }
        $stmt->execute();
        $row = $stmt->fetch($this->dbConnection::FETCH_ASSOC);

        $insuranceItemCnt = (int)$row['InsuranceItemCnt'] ?? 0;

        return $insuranceItemCnt;
    }

    public function getServiceCompanyList()
    {
        $sql = "SELECT ServiceControlIdx, ServiceCompanyName FROM abc.ServiceControl 
                WHERE IsContract = b'1'";
        $stmt = $this->dbConnection->query($sql);
        $ibCompanyList = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

        return $ibCompanyList;
    }

    /**
     * **사 또는 **상품 정보 조회
     * @param int $insuranceIdx
     * @return array|mixed
     */
    public function getInsuranceItem(int $insuranceIdx)
    {
        // insuranceIdx 정보 가져오기
        $sql = "SELECT * FROM abc.Insureance WHERE InsureanceIdx = :insuranceIdx";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindValue(':insuranceIdx', $insuranceIdx, $this->dbConnection::PARAM_INT);
        $stmt->execute();

        $itemInfo = $stmt->fetch($this->dbConnection::FETCH_ASSOC) ?? [];
        if (count($itemInfo) === 0) {
            return [];
        }
        $parentItemIdx = 0;
        $itemIdx = 0;
        if ($itemInfo['ParentItemIdx']) {
            $itemIdx = $insuranceIdx;
        } else {
            $parentItemIdx = $insuranceIdx;
        }

        $sql = "SELECT 
                    SCM.ServiceCompanyName, PI.InsureanceIdx, PI.ItemCode AS InsuranceCode, 
                    PI.ItemName AS InsuranceName, I.InsureanceIdx AS InsuranceItemManageIdx, I.ItemCode, I.ItemName
                FROM abc.Insureance PI
                JOIN abc.ServiceControl SCM ON SCM.ServiceControlIdx = PI.ServiceControlIdx
                LEFT JOIN abc.Insureance I ON I.ParentItemIdx = PI.InsureanceIdx AND I.IsUse = b'1' 
                AND I.ParentItemIdx IS NOT NULL
                WHERE SCM.IsContract = b'1' AND PI.ParentItemIdx IS NULL AND PI.IsUse = b'1'";
        $sql .= ($itemIdx) ? " AND I.InsureanceIdx = :itemIdx" : " AND PI.InsureanceIdx = :parentItemIdx";
        $stmt = $this->dbConnection->prepare($sql);
        if ($itemIdx) {
            $stmt->bindValue(':itemIdx', $itemIdx, $this->dbConnection::PARAM_INT);
        } else {
            $stmt->bindValue(':parentItemIdx', $parentItemIdx, $this->dbConnection::PARAM_INT);
        }
        $stmt->execute();
        $insuranceItem = $stmt->fetch($this->dbConnection::FETCH_ASSOC) ?? [];

        return $insuranceItem;
    }


    /**
     * **사 등록 Model
     * @param array $params
     * @return array
     */
    public function insertInsurance(int $ibCompanyIdx, array $params)
    {
        $response = [
            'result' => false,
            'success' => 0,
            'fail' => 0,
        ];
        $columnName = ['insuranceCode', 'insuranceName'];

        try {
            $sql = "SELECT ItemCode FROM abc.Insureance
                    WHERE ServiceControlIdx = :ibCompanyIdx AND ParentItemIdx IS NULL AND IsUse = b'1'";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindValue(':ibCompanyIdx', $ibCompanyIdx, $this->dbConnection::PARAM_INT);
            $stmt->execute();
            $insuranceList = $stmt->fetchAll($this->dbConnection::FETCH_COLUMN) ?? [];

            $itemInsert = [];
            foreach ($params as $value) {
                if (empty(array_filter($value))) {
                    continue;
                }

                $data = array_combine($columnName, $value);

                if (!$data['insuranceCode'] || !$data['insuranceName']) {
                    $response['fail']++;
                    continue;
                }

                if (count($insuranceList) > 0) {
                    if (in_array($data['insuranceCode'], $insuranceList)) {
                        $response['fail']++;
                        continue;
                    }
                }

                $itemInsert[] = [
                    'insuranceCode' => $data['insuranceCode'],
                    'insuranceName' => $data['insuranceName'],
                ];

                $response['success']++;
            }

            if (count($itemInsert) === 0) {
                return $response;
            }

            $placeHolder = "(" . $ibCompanyIdx . "," . implode(',', array_fill(0, count($itemInsert[0]), "?")) . ")";
            $placeHolders = implode(',', array_fill(0, count($itemInsert), $placeHolder));

            $sql = "INSERT INTO abc.Insureance (ServiceControlIdx, ItemCode, ItemName)
                    VALUES {$placeHolders}";
            $stmt = $this->dbConnection->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $itemInsert));
            $stmt->execute($flat);

            $response['result'] = true;

        } catch (\PDOException $PDOException) {
            $response['result'] = false;
        }

        return $response;
    }

    /**
     * **상품 등록 Model
     * @param array $params
     * @return array
     */
    public function insertInsuranceItem(array $params)
    {
        $response = [
            'result' => false,
            'success' => 0,
            'fail' => 0,
        ];
        $columnName = ['parentItemIdx', 'itemCode', 'itemName'];

        try {
            $sql = "SELECT InsureanceIdx FROM abc.Insureance
                    WHERE ParentItemIdx IS NULL AND IsUse = b'1'";
            $stmt = $this->dbConnection->query($sql);
            $insuranceList = $stmt->fetchAll($this->dbConnection::FETCH_COLUMN) ?? [];

            $sql = "SELECT ItemCode, ParentItemIdx FROM abc.Insureance
                    WHERE ParentItemIdx IS NOT NULL AND IsUse = b'1'";
            $stmt = $this->dbConnection->query($sql);
            $insuranceItemList = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

            $checkList = [];
            if (count($insuranceItemList) > 0) {
                foreach ($insuranceItemList as $insuranceItem) {
                    $checkList[] = "{$insuranceItem['ItemCode']}_{$insuranceItem['ParentItemIdx']}";
                }
            }

            $itemInsert = [];
            foreach ($params as $value) {
                if (empty(array_filter($value))) {
                    continue;
                }

                $data = array_combine($columnName, $value);

                if (!$data['parentItemIdx'] || !$data['itemCode'] || !$data['itemName']) {
                    $response['fail']++;
                    continue;
                }

                if (count($insuranceList) > 0) {
                    if (!in_array($data['parentItemIdx'], $insuranceList)) {
                        $response['fail']++;
                        continue;
                    }
                }

                if (count($checkList) > 0) {
                    $checkVal = "{$data['itemCode']}_{$data['parentItemIdx']}";
                    if (in_array($checkVal, $checkList)) {
                        $response['fail']++;
                        continue;
                    }
                }

                $itemInsert[] = [
                    'itemCode' => $data['itemCode'],
                    'itemName' => $data['itemName'],
                    'parentItemIdx' => $data['parentItemIdx'],
                ];

                $response['success']++;
            }

            if (count($itemInsert) === 0) {
                return $response;
            }

            $placeHolder = "(" . implode(',', array_fill(0, count($itemInsert[0]), "?")) . ")";
            $placeHolders = implode(',', array_fill(0, count($itemInsert), $placeHolder));

            $sql = "INSERT INTO abc.Insureance (ItemCode, ItemName, ParentItemIdx)
                    VALUES {$placeHolders}";
            $stmt = $this->dbConnection->prepare($sql);
            $flat = call_user_func_array('array_merge', array_map('array_values', $itemInsert));
            $stmt->execute($flat);

            $response['result'] = true;

        } catch (\PDOException $PDOException) {
            $response['result'] = false;
        }

        return $response;
    }

    /**
     * **사 및 **상품 정보 Update Model
     * @param int $insuranceIdx
     * @param array $params
     * @return array|false
     */
    public function updateInsuranceItem(int $insuranceIdx, array $params)
    {
        $insuranceColumn = [
            'insuranceCode' => "ItemCode = :insuranceCode",
            'insuranceName' => "ItemName = :insuranceName",
        ];

        $itemColumn = [
            'itemCode' => "ItemCode = :itemCode",
            'itemName' => "ItemName = :itemName",
        ];

        $response = [
            'result' => false,
            'insuranceIdx' => $insuranceIdx,
        ];

        //실제 상품 사용여부 및 상품 데이터 조회
        try {
            $this->dbConnection->beginTransaction();

            // **사 정보 확인 및 업데이트
            $sql = "SELECT InsureanceIdx, ItemCode, ItemName FROM abc.Insureance
                    WHERE InsureanceIdx = :insuranceIdx AND ParentItemIdx IS NULL";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindValue(':insuranceIdx', $params['insuranceIdx'], $this->dbConnection::PARAM_INT);
            $stmt->execute();
            $insuranceInfo = $stmt->fetch($this->dbConnection::FETCH_ASSOC) ?? [];
            if (empty($insuranceInfo['InsureanceIdx'])) {
                throw new \Exception('필수 데이터 오류', 453);
            }

            if ($insuranceInfo['ItemCode'] == $params['insuranceCode']) {
                unset($insuranceColumn['insuranceCode']);
            }
            if ($insuranceInfo['ItemName'] == $params['insuranceName']) {
                unset($insuranceColumn['insuranceName']);
            }

            if (count($insuranceColumn) > 0) {
                $placeholder = implode(',', $insuranceColumn);

                $sql = "UPDATE abc.Insureance
                        SET {$placeholder} 
                        WHERE InsureanceIdx = :insuranceIdx";
                $stmt = $this->dbConnection->prepare($sql);

                if (isset($insuranceColumn['insuranceCode'])) {
                    $stmt->bindValue(':insuranceCode', $params['insuranceCode']);
                }
                if (isset($insuranceColumn['insuranceName'])) {
                    $stmt->bindValue(':insuranceName', $params['insuranceName']);
                }
                $stmt->bindValue(':insuranceIdx', $params['insuranceIdx'], $this->dbConnection::PARAM_INT);
                $stmt->execute();
            }

            if ($params['itemIdx'] && $params['itemName'] && $params['itemCode']) {
                // **상품 정보 확인 및 업데이트
                $sql = "SELECT InsureanceIdx, ItemCode, ItemName, ParentItemIdx 
                        FROM abc.Insureance
                        WHERE InsureanceIdx = :itemIdx AND ParentItemIdx = :insuranceIdx";
                $stmt = $this->dbConnection->prepare($sql);
                $stmt->bindValue(':itemIdx', $params['itemIdx'], $this->dbConnection::PARAM_INT);
                $stmt->bindValue(':insuranceIdx', $params['insuranceIdx'], $this->dbConnection::PARAM_INT);
                $stmt->execute();
                $itemInfo = $stmt->fetch($this->dbConnection::FETCH_ASSOC) ?? [];
                if (empty($itemInfo['InsureanceIdx'])) {
                    throw new \Exception('필수 데이터 오류', 453);
                }

                if ($itemInfo['ItemCode'] == $params['itemCode']) {
                    unset($itemColumn['itemCode']);
                }
                if ($itemInfo['ItemName'] == $params['itemName']) {
                    unset($itemColumn['itemName']);
                }

                if (count($itemColumn) > 0) {
                    $placeholder = implode(',', $itemColumn);

                    $sql = "UPDATE abc.Insureance
                            SET {$placeholder} 
                            WHERE InsureanceIdx = :itemIdx";
                    $stmt = $this->dbConnection->prepare($sql);

                    if (isset($itemColumn['itemCode'])) {
                        $stmt->bindValue(':itemCode', $params['itemCode']);
                    }
                    if (isset($itemColumn['itemName'])) {
                        $stmt->bindValue(':itemName', $params['itemName']);
                    }
                    $stmt->bindValue(':itemIdx', $params['itemIdx'], $this->dbConnection::PARAM_INT);
                    $stmt->execute();
                }
            }

            if (!$params['itemIdx'] && $params['itemName'] && $params['itemCode']) {
                $sql = "INSERT INTO abc.Insureance (ItemCode, ItemName, ParentItemIdx)
                        VALUES (:itemCode, :itemName, :insuranceIdx)";
                $stmt = $this->dbConnection->prepare($sql);
                $stmt->bindValue(':itemCode', $params['itemCode']);
                $stmt->bindValue(':itemName', $params['itemName']);
                $stmt->bindValue(':insuranceIdx', $params['insuranceIdx'], $this->dbConnection::PARAM_INT);
                $stmt->execute();

                $response['insuranceIdx'] = $this->dbConnection->lastInsertId();
            }

            $this->dbConnection->commit();

            $response['result'] = true;
        } catch (\PDOException $PDOException) {
            $this->dbConnection->rollBack();
            $response['result'] = false;
        } catch (\Exception $exception) {
            $this->dbConnection->rollBack();
            $response['result'] = false;
            $response['detail'] = [
                'code' => $exception->getCode(),
                'msg' => $exception->getMessage(),
            ];
        }

        return $response;
    }

    /***
     * 상품 비활성화 로직
     * @param array $params
     * @return array|false
     */
    public function deleteInsuranceItem(int $insuranceIdx)
    {
        $response = [
            'result' => false,
            'productIdx' => $insuranceIdx
        ];

        //**사 또는 **상품 비활성화 로직
        try {
            $sql = "SELECT UsersIdx, OrderIdx FROM abc.Contract
                    WHERE InsureanceIdx = :insuranceIdx";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindValue(':insuranceIdx', $insuranceIdx, $this->dbConnection::PARAM_INT);
            $stmt->execute();
            $contractList = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];
            if (count($contractList) !== 0) {
                throw new \Exception('이미 계약된 **입니다. 삭제하실 수 없습니다.', 454);
            }

            $sql = "UPDATE abc.Insureance SET IsUse = b'0' WHERE InsureanceIdx = :insuranceIdx";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindValue(':insuranceIdx', $insuranceIdx, $this->dbConnection::PARAM_INT);
            $stmt->execute();

            $response['result'] = true;

        } catch (\PDOException $PDOException) {
            $response['result'] = false;
        } catch (\Exception $exception) {
            $response['result'] = false;
            $response['detail'] = [
                'code' => $exception->getCode(),
                'msg' => $exception->getMessage(),
            ];
        }

        return $response;
    }

    public function __destruct()
    {
        $this->pdo = null;
        $this->dbConnection = null;
    }
}