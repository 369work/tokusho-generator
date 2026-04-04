# tokusho-generator

特定商取引法に基づく表示ページを自動生成するPHPライブラリ。

**Stripe審査通過**を主目的に設計されており、必要な記載項目の欠落・形式不備を検出してHTMLページを生成します。

---

## 特徴

- ✅ 特商法の必須項目を網羅（Stripe審査対応）
- ✅ 記載漏れ・フォーマット不備を自動チェック
- ✅ スタンドアロンHTMLページの自動生成
- ✅ フレームワーク非依存（Laravel / CakePHP / EC-CUBE など）
- ✅ Composerで導入可能
- ✅ WordPressプラグインのコアとして利用可能

---

## 動作環境

- PHP 8.1 以上

---

## インストール

```bash
composer require tokusho/tokusho-generator
```

---

## 基本的な使い方

```php
use Tokusho\TokushoGenerator;
use Tokusho\Exception\ValidationException;

$generator = new TokushoGenerator([
    'company_name'      => '山田太郎商店',
    'responsible_name'  => '山田太郎',
    'address'           => '〒150-0001 東京都渋谷区神宮前1-2-3',
    'phone'             => '03-1234-5678',
    'email'             => 'info@example.com',
    'price_description' => '各商品ページに記載の価格（税込）',
    'payment_methods'   => 'クレジットカード（Visa / Mastercard）',
    'payment_timing'    => '購入手続き完了時',
    'shipping_fee'      => '送料無料（デジタル納品）',
    'delivery_timing'   => '決済完了後、即時ダウンロード可能',
    'return_policy'     => '商品の性質上、購入後の返品・返金はお断りしています。',
]);

// バリデーション（エラーがあれば例外をスロー）
try {
    $generator->validate();
} catch (ValidationException $e) {
    foreach ($e->getErrors() as $field => $errors) {
        echo "[{$field}] " . implode(', ', $errors) . "\n";
    }
}

// HTMLページ生成
echo $generator->toHTML();

// 配列として取得
$array = $generator->toArray();

// JSONとして取得
$json = $generator->toJSON();
```

---

## 入力フィールド一覧

| キー               | ラベル                       | 必須 |
|--------------------|------------------------------|------|
| `company_name`     | 販売業者名                   | ✅   |
| `responsible_name` | 代表者・責任者の氏名         | ✅   |
| `address`          | 所在地                       | ✅   |
| `address_note`     | 所在地に関する補足           | ―    |
| `phone`            | 電話番号                     | ✅   |
| `email`            | メールアドレス               | ✅   |
| `website_url`      | ウェブサイトURL              | ―    |
| `price_description`| 販売価格                     | ✅   |
| `payment_methods`  | 支払方法                     | ✅   |
| `payment_timing`   | 支払時期                     | ✅   |
| `shipping_fee`     | 送料                         | ✅   |
| `delivery_timing`  | サービス提供時期・引き渡し時期 | ✅  |
| `return_policy`    | 返品・キャンセルポリシー     | ✅   |
| `other`            | その他                       | ―    |

---

## API リファレンス

### `TokushoGenerator`

#### コンストラクタ

```php
new TokushoGenerator(array $data = [], array $options = [])
```

**オプション（`$options`）:**

| キー          | 型     | デフォルト値               | 説明                               |
|---------------|--------|----------------------------|------------------------------------|
| `page_title`  | string | `特定商取引法に基づく表示` | HTMLの `<title>` タグの値          |
| `heading`     | string | `特定商取引法に基づく表示` | ページ内 `<h1>` のテキスト         |
| `standalone`  | bool   | `true`                     | `false` にすると `<main>` 以下のみ出力 |
| `lang`        | string | `ja`                       | `<html lang="">` の値              |

#### メソッド

| メソッド          | 戻り値                         | 説明                                            |
|-------------------|--------------------------------|-------------------------------------------------|
| `validate()`      | `void`                         | エラーがあれば `ValidationException` をスロー   |
| `check()`         | `array<string, string[]>`      | エラー配列を返す（例外なし）                    |
| `toHTML()`        | `string`                       | 特商法ページのHTMLを生成                        |
| `toArray()`       | `array`                        | ラベル・値・必須フラグの配列を返す              |
| `toJSON()`        | `string`                       | JSON文字列を返す                                |
| `set($key, $val)` | `static`                       | フィールドの値をセット（メソッドチェーン可）    |
| `fill(array)`     | `static`                       | 複数フィールドをまとめてセット                  |
| `get($key)`       | `string`                       | フィールドの値を取得                            |
| `addField($def)`  | `static`                       | カスタムフィールドを追加                        |

---

## カスタムフィールドの追加

```php
use Tokusho\Field\FieldDefinition;

$generator->addField(new FieldDefinition(
    key: 'license_number',
    label: '古物商許可番号',
    required: true,
    description: '古物商許可を取得している場合は記載してください。',
    type: 'text',
    maxLength: 100,
));
```

---

## WordPressプラグインとの連携

本ライブラリはWordPressプラグインのコアとして設計されています。
プラグイン側でComposerオートロードを読み込み、`TokushoGenerator` を呼び出す形で統合します。

```php
// WordPress プラグイン内での使用例
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

use Tokusho\TokushoGenerator;

function my_plugin_render_tokusho_page(): string
{
    $data = get_option('my_plugin_tokusho_data', []);

    $generator = new TokushoGenerator($data, ['standalone' => false]);

    return $generator->toHTML();
}
```

---

## ライセンス

MIT License
