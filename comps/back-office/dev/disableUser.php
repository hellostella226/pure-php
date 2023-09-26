<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/b***-*abc/config/config.index.php";
if(!isDev){
    header("HTTP/1.0 404 Not Found");
    exit;
}
if (isset($_POST['UsersIdx'])) {
    $pdo = new PDOFactory();
    $conn = $pdo->PDOCreate();
    $sql = "SELECT M.* FROM
                    abc.Members As M 
                    JOIN abc.Users AS MM ON MM.MembersIdx = M.MembersIdx                         
                    WHERE MM.UsersIdx = :UsersIdx AND MM.IsOut IS FALSE AND M.Name = :Name";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':UsersIdx', $_POST['UsersIdx']);
    $stmt->bindParam(':Name', $_POST['name']);
    $stmt->execute();
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!is_array($rows)){
        $conn = null;
        $pdo = null;
        echo "<script>alert('수정대상 회원정보가 존재하지않습니다.이름, 회원 ID를 확인해주세요.'); history.back();</script>";
        exit;
    }
    $name = $rows['Name'] . "_" . $rows['MembersIdx'];
    $sql = "UPDATE abc.`Members` 
                SET `Name` = :Name
                WHERE MembersIdx = :Idx ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':Idx', $rows['MembersIdx'], PDO::PARAM_INT);
    $stmt->bindValue(':Name', $name);
    $stmt->execute();
    $conn = null;
    $pdo = null;
    unset($_POST);
    echo "<script>alert('수정이 완료되었습니다. 변경이름: " . $name . "');location.replace('{$_SERVER['PHP_SELF']}'); </script>";
    exit;
}

?>
<html>
<head>
</head>
<body>
<h3>테스트용 기존 회원 정보 변경 페이지</h3>
<form method="post">
    <div>회원 ID : <input type="text" name="UsersIdx" required></div>
    <div>이름(회원 정보 내 이름) : <input type="text" name="name" required></div>
    <input type="submit">
</form>
<script></script>
</body>
</html>
