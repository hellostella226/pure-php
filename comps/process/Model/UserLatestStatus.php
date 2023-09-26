<?php

namespace Model;

class MemberStatus extends PDOFactory
{
    public function __construct()
    {
    }

    public function updateMemberStatus(array $param)
    {
        $pdo = new PDOFactory();
        $conn = $pdo->PDOCreate();
        $param['orderIdx'] = $this->getOrderData($conn, (int)$param['UsersIdx'], (int)$param['productIdx'])['OrderIdx'];

        $result = $this->insertDupUpdate($conn, $param);
        $conn = null;
        $pdo = null;

        return $result;
    }

    private function getOrderData($conn, int $UsersIdx, int $productIdx)
    {
        $productQuery = ($productIdx === 1) ? "p.ParentProductIdx = :ProductIdx" : "p.ProductIdx = :ProductIdx";
        $sql = "SELECT 
                    og.PaysIdx  
                FROM o.Pays o
                JOIN o.Paysing og ON og.PaysIdx = o.PaysIdx
                JOIN abc.Product p ON p.ProductIdx = og.ProductIdx
                WHERE o.UsersIdx = :UsersIdx AND {$productQuery}
                ORDER BY o.PaysIdx DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':UsersIdx', $UsersIdx, $conn::PARAM_INT);
        $stmt->bindValue(':ProductIdx', $productIdx, $conn::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch($conn::FETCH_ASSOC);
        $result = $row ?? [];

        return $result;
    }

    public function insertDupUpdate($conn, array $params)
    {
        $set = [];
        if (isset($params['process'])) {
            $set[] = "`Process` = :Process";
        }
        if (isset($params['statusCode'])) {
            $set[] = "`StatusCode` = :StatusCode";
        }

        $sql = "INSERT INTO abc.MemberStatus (
                    UsersIdx, OrderIdx, ProductIdx, StatusCode) 
                VALUES (
                    :UsersIdx, :OrderIdx, :ProductIdx, 21000)
                ON DUPLICATE KEY UPDATE 
                    " . implode(',', $set) . ", LatestDatetime = NOW()";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':UsersIdx', $params['UsersIdx'], $conn::PARAM_INT);
        $stmt->bindParam(':OrderIdx', $params['orderIdx'], $conn::PARAM_INT);
        $stmt->bindParam(':ProductIdx', $params['productIdx'], $conn::PARAM_INT);
        if (isset($params['process'])) {
            $stmt->bindParam(':Process', $params['process'], $conn::PARAM_STR);
        }
        if (isset($params['statusCode'])) {
            $stmt->bindParam(':StatusCode', $params['statusCode'], $conn::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetch();

        return $result;
    }

    public function insert($conn, array $params, $statusCode = "21000")
    {
        $sql = "INSERT INTO abc.MemberStatus (
                    UsersIdx, OrderIdx, ProductIdx, StatusCode) 
                VALUES (
                    :UsersIdx, :OrderIdx, :ProductIdx, :StatusCode)
                ON DUPLICATE KEY UPDATE 
                    LatestDatetime = NOW()";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':UsersIdx', $params['UsersIdx'], $conn::PARAM_INT);
        $stmt->bindParam(':OrderIdx', $params['OrderIdx'], $conn::PARAM_INT);
        $stmt->bindParam(':ProductIdx', $params['ProductIdx'], $conn::PARAM_INT);
        $stmt->bindParam(':StatusCode', $statusCode, $conn::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result;
    }
}