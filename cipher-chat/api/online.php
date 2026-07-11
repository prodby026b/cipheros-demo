<?php
/**
 * POST api/online.php — بروزرسانی وضعیت آنلاین
 * این endpoint به‌صورت دوره‌ای از frontend فراخوانی می‌شود (هر ۳۰ ثانیه).
 * ورودی (JSON): {} (بدون ورودی)
 */
require_once __DIR__ . '/_bootstrap.php';

$me = chat_user();

touch_chat_user($conn, $me);

json_response(['ok' => true]);
