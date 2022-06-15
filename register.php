<?php
session_start();
require_once 'dbconnect.php';
require_once 'functions.php';
validateToken();

// passが一致していない場合は「login画面」へ戻る
$pass = filter_input(INPUT_POST, 'pass');
if($pass !== '************') { //★
    $_SESSION['err'] = 'passwordが一致しません';

    $host = $_SERVER['HTTP_HOST'];
    $url = rtrim(dirname($_SERVER['PHP_SELF']), '/');
    header("Location: //$host$url/login.php");
    exit;
}

// act = name の「list」でpostされた時の処理
if(filter_input(INPUT_GET, 'act') === 'list') {
    $high_school = filter_input(INPUT_POST, 'high_school',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
    $odds = filter_input(INPUT_POST, 'odds',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
    $ratio = filter_input(INPUT_POST, 'ratio',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
    $score = filter_input(INPUT_POST, 'score',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
    $win = filter_input(INPUT_POST, 'win',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);

    // 空の配列を削除
    $high_schools = array_filter($high_school);

    $pdo = connect();  
    $pdo->beginTransaction(); //トランザクション

    // 一旦、高校名一覧を全部削除
    try {
        $pdo->query('DELETE FROM lists WHERE 1');
    } catch(\Exception $e){
        exit('接続に失敗しました');
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO lists (high_school, odds, ratio, score, win) VALUES (:high_school, :odds, :ratio, :score, :win)'
        );

        foreach($high_schools as $key => $school) {
            if($win[$key] === '') $win[$key] = 1;
            $stmt->bindValue('high_school', $school, PDO::PARAM_STR);
            $stmt->bindValue('odds',  $odds[$key],   PDO::PARAM_STR);
            $stmt->bindValue('ratio', $ratio[$key],  PDO::PARAM_INT);
            $stmt->bindValue('score', $score[$key],  PDO::PARAM_INT);
            $stmt->bindValue('win',   $win[$key],    PDO::PARAM_INT);
            $stmt->execute();
        }

        $pdo->commit();   //トランザクション
        
    } catch(\Exception $e) {
        $pdo->rollBack(); //トランザクション
        exit('接続に失敗しました');
    }
}

// 高校名一覧を表示する
try {
    $stmt = connect()->query('SELECT * FROM lists');
    $lists = $stmt->fetchAll();
} catch (\Exception $e) {
    exit('接続に失敗しました');
}

// act = name でpostされた時の処理
if(filter_input(INPUT_GET, 'act') === 'name') {
    $sql = 'DELETE FROM selects WHERE hdn = ? AND password = ?';
    $arr = [];
    $arr[] = filter_input(INPUT_POST, 'hdn');
    $arr[] = filter_input(INPUT_POST, 'password');
    try {
        $stmt = connect()->prepare($sql);
        $stmt->execute($arr);
    } catch (\Exception $e) {
        exit('接続に失敗しました');
    }
}

// 登録者一覧を表示
try {
    $stmt = connect()->query('SELECT hdn, password, ip FROM selects ORDER BY id DESC');
    $names = $stmt->fetchAll();
} catch (\Exception $e) {
    exit('接続に失敗しました');
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://use.fontawesome.com/releases/v6.1.1/css/all.css" rel="stylesheet">
    <title>register</title>
    <style>
        *{margin: 0; font-size: 16px; text-decoration: none;}
        form {margin: 10px auto;width: 100%; max-width: 400px;}

        form[action="?act=list"] > section:nth-of-type(1) span {border: none;}
        form[action="?act=list"] > section:nth-of-type(2) input[name="ratio[]"] {color: red;}
        form[action="?act=list"] span {width: 22px; text-align: center;}
        form[action="?act=list"] section {display: flex;}
       .com {
            width: 0; padding: 0;
            height: 22px; line-height: 22px;
            text-align: center;
            border: 1px solid #555;
            background: #fff;
            border-radius: 3px;
        }
        .com1 {flex: 4;}
        .com2 {flex: 1; text-align: center;}

        form[action="?act=list"] button  {margin: 10px 0; width: 100%;}
        form[action="?act=name"] section {position: relative;border: 1px solid #555;}
        form[action="?act=name"] button  {position: absolute; top: 0; right: 0;}
    </style>
</head>
<body>

<div style="text-align: center;">
    <a style="font-size: 18px; font-weight: bold" href="index.php">
    <i class="fa-solid fa-house"></i>
    Home
    </a>
</div>
<form action="?act=list" method="post">
    <section>
        <span></span>
        <span class="com1 com">高校名</span>
        <span class="com1 com">オッズ</span>
        <span class="com2 com">勝率  </span>
        <span class="com2 com">勝数  </span>
        <span class="com2 com">勝負  </span>
    </section>
<?php for($i = 0; $i < 50; $i++) : ?>
    <section>
        <span><?php echo $i + 1 ?></span>
        <input class="com1 com" type="text" name="high_school[]" value="<?php echo $lists[$i]['high_school'] ?? '' ?>">
        <input class="com1 com" type="text" name="odds[]"  value="<?php echo $lists[$i]['odds']  ?? '' ?>">
        <input class="com2 com" type="text" name="ratio[]" value="<?php echo $lists[$i]['ratio'] ?? '' ?>">
        <input class="com2 com" type="text" name="score[]" value="<?php echo $lists[$i]['score'] ?? '' ?>">
        <input class="com2 com" type="text" name="win[]" value="<?php echo $lists[$i]['win'] ?? '' ?>">
    </section>
<?php endfor ?>
    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
    <input type="hidden" name="pass" value="<?php echo $pass ?>">
    <button>編集する</button>
</form>

<!-- 登録者一覧 -->
<div style="text-align: center;">登録者一覧</div>
<?php foreach($names as $name) : ?>
<form action="?act=name" method="post">
    <section>
        [名前] : <span><?php echo $name['hdn'] ?>     </span><br>
        [IP＿] : <span><?php echo $name['ip'] ?>      </span><br>
        [削除] : <span><?php echo $name['password'] ?></span><br>

        <input type="hidden" name="hdn" value="<?php echo $name['hdn'] ?>">
        <input type="hidden" name="password" value="<?php echo $name['password'] ?>">
        <input type="hidden" name="token"   value="<?php echo $_SESSION['token'] ?>">
        <input type="hidden" name="pass" value="<?php echo $pass ?>">
        <button style="color: #4285f4;"><i class="fa-solid fa-trash"></i></button>
    </section>
</form>
<?php endforeach ?>

</body>
</html>