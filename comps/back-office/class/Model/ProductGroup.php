<?php

namespace Model;

use Matrix\Exception;
use PDOFactory;

class ProductGroup extends PDOFactory
{
    protected $pdo = null;
    protected $dbConnection = null;
    protected $fieldList = [
        'productGroupCode' => [
            'fieldName' => "ProductGroupIdx",
            'expression' => "=",
            'type' => "int",
        ],
        'productGroupName' => [
            'fieldName' => "`ProductGroupName`",
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
     * 상품그룹 데이터 조회 Model
     * @param array $params
     * @return array|false
     */
    public function selectProductGroupList(array $params)
    {
        $pagination = $params['pagination'];
        $search = $params['search'];

        // 기본 쿼리문
        $sql = "SELECT 
                    RegDatetime, ProductGroupIdx, ProductGroupName
                FROM abc.ProductGroup 
                WHERE BusinessManageIdx = 1 AND IsUse = b'1'";
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
        $productList = [];

        $sql = "SELECT PGM.ProductGroupIdx, P.ParentProductIdx, P.ProductName 
                FROM abc.ProductGroupManage PGM
                JOIN abc.ProductGroup PG ON PG.ProductGroupIdx = PGM.ProductGroupIdx
                JOIN abc.Product P ON P.ProductIdx = PGM.ProductIdx
                WHERE PG.BusinessManageIdx = 1 AND PG.IsUse = b'1'";
        $stmt = $this->dbConnection->query($sql);
        $rows = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

        $i = 1;
        $product = [
            'product_1' => "",
            'product_2' => "",
            'product_3' => "",
            'product_4' => "",
            'product_5' => "",
        ];
        if (count($rows) > 0){
            $firstProductGroup = $rows[0]['ProductGroupIdx'];
            foreach ($rows as $row) {
                if ($row['ProductGroupIdx'] !== $firstProductGroup){
                    $i = 1;
                    $firstProductGroup = $row['ProductGroupIdx'];
                    $product = [
                        'product_1' => "",
                        'product_2' => "",
                        'product_3' => "",
                        'product_4' => "",
                        'product_5' => "",
                    ];
                }
                $product['product_' . $i] = $row['ProductName'];
                $productList[$row['ProductGroupIdx']] = $product;
                $i++;
            }

            foreach ($result as $key => $val) {
                $result[$key] = array_merge($val, $productList[$val['ProductGroupIdx']]);
            }
        } else {
            foreach ($result as $key => $val) {
                $result[$key] = array_merge($val, $product);
            }
        }

        return $result;
    }

    /**
     * pagination을 위한 총 카운트 조회
     * @param array $params
     * @return int
     */
    public function getProductGroupCnt(array $params)
    {
        $search = $params['search'];

        // 기본 쿼리문
        $sql = "SELECT 
                    COUNT(*) AS ProductGroupCnt
                FROM abc.ProductGroup 
                WHERE BusinessManageIdx = 1 AND IsUse = b'1'";
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
                $stmt->bindValue(":{$search['column']}", $search['value']);
            }
        }
        $stmt->execute();
        $row = $stmt->fetch($this->dbConnection::FETCH_ASSOC);

        $productGroupCnt = (int)$row['ProductGroupCnt'] ?? 0;

        return $productGroupCnt;
    }

    /**
     * 상품 목록 조회
     * @return array|false
     */
    public function getProductList(int $categoryIdx)
    {
        $sql = "SELECT P.ProductIdx, PP.ProductName AS CategoryName, P.ProductName
                FROM abc.Product P 
                JOIN abc.Product PP ON PP.ProductIdx = P.ParentProductIdx
                WHERE P.IsUse = b'1'";
        if ($categoryIdx) {
            $sql .= " AND PP.ProductIdx = :categoryIdx";
        }
        $stmt = $this->dbConnection->prepare($sql);
        if ($categoryIdx) {
            $stmt->bindValue(':categoryIdx', $categoryIdx, $this->dbConnection::PARAM_INT);
        }
        $stmt->execute();
        $productList = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

        return $productList;
    }

    public function getClientCustomerList(int $productGroupIdx)
    {
        $sql = "SELECT ClientControlIdx FROM abc.ClientControl
                WHERE ProductGroupIdx = :productGroupIdx";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->dbConnection::PARAM_INT);
        $stmt->execute();
        $clientCustomerList = $stmt->fetchAll($this->dbConnection::FETCH_COLUMN) ?? [];

        return $clientCustomerList;
    }


    /**
     * 상품그룹 등록 Model
     * @param array $params
     * @return array|false
     */
    public function insertProductGroup(array $params)
    {
        try {
            $this->dbConnection->beginTransaction();

            $sql = "INSERT INTO abc.ProductGroup (BusinessManageIdx, ProductGroupName)
                    VALUES (1, :productGroupName)";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bindValue(':productGroupName', $params['productGroupName']);
            $stmt->execute();

            $productGroupIdx = (int)$this->dbConnection->lastInsertId() ?? 0;
            if (!$productGroupIdx) {
                throw new \Exception("상품그룹 Idx를 확인해주세요.", 1500);
            }

            $productList = $params['productList'];

            $placeholder = "(" . $productGroupIdx . ", ?)";
            $placeholders = implode(',', array_fill(0, count($productList), $placeholder));

            $sql = "INSERT INTO abc.ProductGroupManage (ProductGroupIdx, ProductIdx)
                    VALUES {$placeholders}";
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->execute($productList);

            $this->dbConnection->commit();

        } catch (\PDOException $PDOException) {
            $this->dbConnection->rollBack();
            $productGroupIdx = 0;
        } catch (\Exception $exception) {
            $this->dbConnection->rollBack();
            $productGroupIdx = 0;
        }

        return $productGroupIdx;
    }

    /**
     * 상품그룹명 Update
     * @param int $productGroupIdx
     * @param array $params
     * @return array|false
     */
    public function updateProductGroup(int $productGroupIdx, array $params)
    {
        $response = [
            'result' => false,
            'productGroupIdx' => $productGroupIdx
        ];

        $productGroupName = $params['productGroupName'] ?? "";

        //상품 그룹명 수정
        $sql = "UPDATE abc.ProductGroup
                SET ProductGroupName = :productGroupName
                WHERE ProductGroupIdx = :productGroupIdx";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindValue(':productGroupName', $productGroupName);
        $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->dbConnection::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $response['result'] = true;
        }

        return $response;
    }

    /***
     * 상품 비활성화 로직
     * @param array $params
     * @return array|false
     */
    public function deleteProductGroup(int $productGroupIdx)
    {
        $response = [
            'result' => false,
            'productGroupIdx' => $productGroupIdx
        ];

        //상품그룹 비활성화 로직
        $sql = "UPDATE abc.ProductGroup SET IsUse = b'0' WHERE ProductGroupIdx = :productGroupIdx";
        $stmt = $this->dbConnection->prepare($sql);
        $stmt->bindValue(':productGroupIdx', $productGroupIdx, $this->dbConnection::PARAM_INT);
        $result = $stmt->execute();

        if ($result) {
            $response['result'] = true;
        }

        return $response;
    }

    /**
     * 카테고리 목록 조회
     * @return array|false
     */
    public function getProductCategory()
    {
        $sql = "SELECT ProductIdx, ProductName FROM abc.Product 
                WHERE ParentProductIdx IS NULL";
        $stmt = $this->dbConnection->query($sql);
        $productCategory = $stmt->fetchAll($this->dbConnection::FETCH_ASSOC) ?? [];

        return $productCategory;
    }


    public function __destruct()
    {
        $this->pdo = null;
        $this->dbConnection = null;
    }
}