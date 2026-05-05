<?php

require __DIR__ . '/config.php';

unset($_SESSION['user']);
unset($_SESSION['admin']);

setFlash('success', 'Başarıyla çıkış yaptınız.');

redirect(BASE_URL . '/index.php');

