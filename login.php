<?php
session_start();
require_once 'functions.php';
createToken();

$err = $_SESSION['err'] ?? '';
$_SESSION['err'] = '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://use.fontawesome.com/releases/v6.1.1/css/all.css" rel="stylesheet">
    <title>login</title>
    <style>
        body {margin: 0; background: #333;}
        form {
            width: 280px;
            margin: 70px auto 50px;
        }
        input, button {
            font-size: 18px;
            border: none;
            width: 100%;
            padding: 0 5px;
            height: 30px; line-height: 30px;
            background: #fff;
            border-radius: 3px;
        }
        button {
            cursor: pointer;
            margin: 5px 0 20px;
            width: auto;
            background: #ddd;
        }
        a {
            text-decoration: none;
            font-weight: bold;
            font-size: 20px;
            color: #fff;
        }

        #container {
            position: relative;
            width: 280px; height: 280px;
            border-radius: 50%;
            margin: auto;
            display: flex;
            justify-content: center;
        }
        #container > div {
            position: absolute;
            border-radius: 5px;
        }
        #seconds {
            height: 180px; width: 2px;
            background: #fff;
            transform-origin: center 140px;
        }
        #minutes {
            height: 120px; width: 5px;
            background: #eee;
            transform-origin: center 140px;
        }
        #hours {
            height: 100px; width: 10px;
            background: #ccc;
            transform-origin: center 140px;
        }
        div#circl {
            height: 20px; width: 20px;
            background: #fff;;
            top: 130px;
            border-radius: 50%;
        }
        .memory {
            height: 2px; width: 2px;
            background: #fff;
            transform-origin: center 140px;
        }
    </style>
</head>
<body>
   <form action="register.php" method="post">
       <div style="color: #fff;"><?php echo $err; ?></div>
       <input type="text" name="pass" placeholder="パスワード"><br>
       <button>ログイン <i class="fa-solid fa-right-to-bracket"></i></button><br>
       
       <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
       <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?>">
   </form>

    <div id="container">
        <div id="hours"></div>
        <div id="minutes"></div>
        <div id="seconds"></div>
        <div id="circl"></div>
    </div>
    <script>
        for(let i = 0; i < 60; i++) {
            const memory = document.createElement('div');
            memory.classList.add('memory');
            if(i % 5 === 0) {
                memory.style.height = '10px';
            }
            memory.style.transform = `rotate(${i * 6}deg)`;
            container.appendChild(memory);
        }

        function func() {
            const d = new Date();
            let s = d.getSeconds() * 6;
            let m = d.getMinutes() * 6 + s / 60;
            let h = d.getHours()  * 30 + m / 12;
            seconds.style.transform = `rotate(${s}deg)`;
            minutes.style.transform = `rotate(${m}deg) translate(0, 20px)`;
            hours.style.transform   = `rotate(${h}deg) translate(0, 40px)`;
            setTimeout(func, 1000);
        }
        func()
    </script>
</body>
</html>