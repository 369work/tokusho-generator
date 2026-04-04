<?php

declare(strict_types=1);

/**
 * TokushoGenerator の基本的な使用例
 *
 * Composer でオートロードを設定してから実行してください。
 *   composer install
 *   php example/basic.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Tokusho\TokushoGenerator;
use Tokusho\Exception\ValidationException;

// ----------------------------------------------------------------
// 1. 基本的な使い方
// ----------------------------------------------------------------

$generator = new TokushoGenerator([
    'company_name'      => '山田太郎商店',
    'responsible_name'  => '山田太郎',
    'address'           => '〒150-0001 東京都渋谷区神宮前1-2-3 〇〇ビル 4F',
    'address_note'      => '個人情報保護のため、住所は請求があった場合に速やかに開示します。',
    'phone'             => '03-1234-5678',
    'email'             => 'info@example.com',
    'website_url'       => 'https://example.com',
    'price_description' => '各商品ページに記載の価格（税込）',
    'payment_methods'   => 'クレジットカード（Visa / Mastercard / American Express / JCB）',
    'payment_timing'    => '購入手続き完了時に決済が確定します。',
    'shipping_fee'      => '送料無料（デジタル納品のため）',
    'delivery_timing'   => '決済完了後、即時ダウンロード可能です。',
    'return_policy'     => 'デジタルコンテンツの性質上、購入後の返品・返金はお断りしています。'
                         . 'ただし、当社の責に帰すべき事由による場合はこの限りではありません。',
]);

// ----------------------------------------------------------------
// 2. バリデーション
// ----------------------------------------------------------------

echo "=== バリデーション ===\n";

// check() はエラー配列を返す（例外なし）
$errors = $generator->check();

if (empty($errors)) {
    echo "✅ バリデーション通過\n";
} else {
    echo "❌ バリデーションエラー:\n";
    foreach ($errors as $field => $fieldErrors) {
        foreach ($fieldErrors as $error) {
            echo "  [{$field}] {$error}\n";
        }
    }
}

echo "\n";

// validate() は例外をスローする
try {
    $generator->validate();
    echo "✅ validate() 通過\n";
} catch (ValidationException $e) {
    echo "❌ ValidationException:\n" . $e->getMessage() . "\n";
}

echo "\n";

// ----------------------------------------------------------------
// 3. データ出力
// ----------------------------------------------------------------

echo "=== toArray() ===\n";
$array = $generator->toArray();
foreach ($array as $key => $item) {
    $required = $item['required'] ? '【必須】' : '【任意】';
    echo "{$required} {$item['label']}: {$item['value']}\n";
}

echo "\n";

// ----------------------------------------------------------------
// 4. HTML生成（スタンドアロン）
// ----------------------------------------------------------------

echo "=== toHTML() ===\n";
$html = $generator->toHTML();

// ファイルに保存する例
$outputPath = __DIR__ . '/output/tokusho.html';

if (!is_dir(__DIR__ . '/output')) {
    mkdir(__DIR__ . '/output', 0755, true);
}

file_put_contents($outputPath, $html);
echo "HTMLを保存しました: {$outputPath}\n\n";

// ----------------------------------------------------------------
// 5. 部分HTML生成（WordPressなどへの組み込み用）
// ----------------------------------------------------------------

$generatorPartial = new TokushoGenerator(
    data: [
        'company_name'      => '株式会社サンプル',
        'responsible_name'  => '佐藤花子',
        'address'           => '大阪府大阪市北区梅田1-1-1',
        'phone'             => '06-1234-5678',
        'email'             => 'info@sample.co.jp',
        'price_description' => '月額 ¥9,800（税込）',
        'payment_methods'   => 'クレジットカード',
        'payment_timing'    => '毎月1日に前払いで請求されます。',
        'shipping_fee'      => '送料無料',
        'delivery_timing'   => '登録完了後、即日ご利用いただけます。',
        'return_policy'     => 'サービスの性質上、一度お支払いいただいた料金の返金は致しかねます。',
    ],
    options: [
        'standalone' => false, // <html>〜</html>でラップしない
        'heading'    => '特定商取引法に基づく表示',
    ],
);

echo "=== 部分HTML（standalone: false）===\n";
echo $generatorPartial->toHTML();
echo "\n\n";

// ----------------------------------------------------------------
// 6. バリデーションエラーが発生するケース
// ----------------------------------------------------------------

echo "=== バリデーションエラーのテスト ===\n";

$invalid = new TokushoGenerator([
    'company_name' => '',               // 必須項目が空
    'email'        => 'not-an-email',   // 形式不正
    'phone'        => '不正な電話番号',  // 形式不正
]);

$errors = $invalid->check();

foreach ($errors as $field => $fieldErrors) {
    foreach ($fieldErrors as $error) {
        echo "  [{$field}] {$error}\n";
    }
}
