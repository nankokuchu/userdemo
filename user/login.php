<!doctype html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ログイン</title>
    <?php
    include_once 'common/head.php';
    ?>
</head>
<body>
<?php
    include_once 'common/header.php';
?>
<div class="main">
    <div class="container mt-5">
        <form class="form p-3" id="register-form" action="">
            <div class="mb-3">
                <label for="username" class="form-label">ユーザーネーム</label>
                <input class="form-control" type="text" name="username" placeholder="username" id="username">
                <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">パスワード</label>
                <input type="password" name="password" class="form-control" id="password">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="check-me" class="form-check-input" id="check-me">
                <label class="form-check-label" for="check-me">Check me out</label>
            </div>
            <button type="submit" class="btn btn-primary">提出</button>
            <a href="/user/register.php" class="badge badge-success">サインアップ</a>
        </form>
    </div>
</div>
<?php
include_once 'common/footer.php';
?>
<script>
    document.getElementById('register-form').addEventListener('submit', function(event) {
        event.preventDefault();
        const form = document.getElementById('register-form');
        const formData = new FormData(form);
        for (let [name, value] of formData.entries()) {
            console.log(name, value);
        }
    });
</script>
</body>
</html>

