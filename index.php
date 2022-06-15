<?php
session_start();
require_once 'dbconnect.php';
require_once 'functions.php';
createToken();

$err = [];

if($_SERVER["REQUEST_METHOD"] === "POST") {
    validateToken();

    if(lists()[0]['ratio']) { //受付期間外だったら

        $err[] = '受付期間外です！';

    } else if(filter_input(INPUT_POST, 'send') === 'del') {

        $_SESSION['None'] = 'none';
        // 削除キー正しければ 登録を削除した後 GETで更新される
        // 削除項目が０だった場合は エラー表示
        $err[] = delete_f(); 

    } else {
        // $ip = $_SERVER["REMOTE_ADDR"]; //★
        $ip = gethostbyaddr($_SERVER["HTTP_X_FORWARDED_FOR"]);//★
        $password = h(filter_input(INPUT_POST, 'password'));
        
        if(!$hdn = h(mb_substr(mbtrim(filter_input(INPUT_POST, 'hdn')), 0, 15))) {
            $err[] = '名前を入力して下さい';
        } else if(inspectionN($hdn, $password)) {
            $err[] = inspectionN($hdn, $password);
        }
        
        if(!preg_match("/\A[a-z\d]{1,20}+\z/i", $password)) $err[] = '削除キーを正しく入力して下さい';
        
        $school = filter_input(INPUT_POST, 'school',FILTER_DEFAULT,FILTER_REQUIRE_ARRAY);
        if (!isset($school)) {
            $err[] = '８校選んで check を入れて下さい';
        } else if (count($school) !== 8) {
            $err[] = '８校選んで check を入れて下さい';
        } else if (count($school) === 8) {
            if(inspection($school)) {
                $err[] = inspection($school);
            }
        }
    }

    // バリデーションのエラーが０だったら
    if(count($err) === 0) {
        $_SESSION['None'] = 'none';
        // 登録処理
        insert_f($hdn, $password, $ip); 
        // 上記にエラーがなければ GET処理で更新 lists(); selects();
    }
}

// ↓GET処理
// lists();
// selects();

// ↓win=0の時にpushされる配列
$arr = [];

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://use.fontawesome.com/releases/v6.1.1/css/all.css" rel="stylesheet">
    <title>baseball</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@300&display=swap');
        * {
            margin:0; padding:0;
            font-size: 18px; font-weight:normal;
            font-family:'Noto Sans JP',sans-serif;
            text-decoration:none;
            box-sizing:border-box;
            list-style:none;
        }
        header, main{
            width: 100%; max-width: 370px;
            text-align: center;
        }
        header {
            margin: 50px auto 20px;
        }
        main {
            margin: 0 auto 60px;
        }
        .list_form, section {
            -webkit-appearance:none;
            border-radius: 3px;
            border: 1px solid #333;
            padding: 3px;
        }
        a {
            color: #777;
        }
        label {
            font-family: sans-serif;
        }
        .hdn, .password, .form_btn {
            border: 1px solid #333; border-radius: 3px;
            height: 30px; line-height: 30px;
            margin-bottom: 3px; padding: 0 5px;
            background: #fff;
            width: 100%;
        }
        .form_btn {
            font-weight: bold;
            border: none;
            background: #777;
            color: #fff;
            cursor: pointer;
            transition: .3s;
        }
        section {
            position: relative;
            margin: 10px 0;
        }
        form.send {
            display: flex;
        }
        input[name="action"] {
            width: 0; flex: 1;
        }
        .schools {
            display: inline-block; 
            width: 49%;
        }
        .uchikeshi {
            text-decoration: line-through;
            color: #aaa;
        }
        ul > :nth-child(2) {
            color: #777; font-size: 16px;
        }
        ul > :nth-child(11) {
            font-weight: bold;
        }
        .info {
            color: #4285f4;
        }
        .key {
            position: absolute; top: 2;
        }
        .del {
            width: 30px; color: #4285f4;
        }
    </style>
</head>

<body>

<header>
    <a href="" style="display:block; font-size:26px;">
        野 球 <i class="fa-solid fa-baseball"></i> 場
    </a>
    <a href="https://fir-6a9a9.web.app/baseball">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        野球〇場HP
    </a>/
    <a href="login.php">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        管理者編集
    </a>
</header>  

<main> 
    <!-- エラー表示 -->
    <?php foreach($err as $e) { ?>
        <div style="text-align:left; padding:2px 5px; color:#4285f4;"><?php echo $e ?></div>
    <?php } ?>

    <form style="display:<?php echo $_SESSION['None'] ?? 'transparent'; ?>;" class="list_form" action="" method="post">
        <?php $_SESSION['None'] = 'transparent' ?>
        <input class="hdn" type="text" name="hdn" value="<?php echo isset($hdn) ? $hdn : '' ?>" maxlength="100" placeholder="名前">
        <input class="password" type="password" name="password" value="<?php echo isset($password) ? $password : '' ?>" maxlength="100" placeholder="削除キー(半角英数20文字以内)">
        <button class="form_btn">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            8校 選んで登録
        </button>
        <div style="margin: 0 0 15px;">
            ※　削除キーが合っていれば<br>
            何度でも選び直しができます
        </div>
        
        <?php foreach(lists() as $list) : ?>
            <?php if($list['win'] == 0) array_push($arr, $list['high_school']); ?>
            <?php if(in_array($list['high_school'], $school ?? [])): ?>
            <label style="display: block;">
                <input type="checkbox" name="school[]" value="<?php echo $list['high_school']; ?>" <?php echo 'checked' ?>>
                <?php echo $list['mix']; ?>
            </label>
            <?php else: ?>
            <label style="display: block;">
                <input type="checkbox" name="school[]" value="<?php echo $list['high_school']; ?>">
                <?php echo $list['mix']; ?>
            </label>
            <?php endif ?>
        <?php endforeach ?>
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
    </form>

    <?php foreach(selects() as $key => $select) : ?>
    <section>
        <div class="key"><?php echo $key + 1 ?></div>
        <ul>
        <?php foreach($select as $i => $s) : ?>
            <?php if($i === 'hdn' || $i === 'created' || $i === 'sum'): ?> 
                <li class="info"><?php echo $s ?></li>
            <?php else : ?>
                <?php if(in_array($s, $arr)): ?>
                    <li class="schools uchikeshi"><?php echo $s ?></li>
                <?php else : ?>
                    <li class="schools"><?php echo $s ?></li>
                <?php endif ?>
            <?php endif ?> 
        <?php endforeach ?> 
        </ul>
        
        <form class="send" action="" method="post">
            <input type="text" name="action" placeholder="削除キー">
            <input type="hidden" name="action_n" value="<?php echo $select['hdn'] ?>">
            <button class="del"><i class="fa-solid fa-trash"></i></button>
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <input type="hidden" name="send" value="del">
        </form>
    </section>
    <?php endforeach ?>
</main>
</body>
</html>