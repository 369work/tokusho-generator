# tokusho-generator

特定商取引法に基づく表示ページを自動生成するPHPライブラリ。

ネット販売における決済サービスの審査通過を念頭に設計されており、必要な記載項目の欠落・形式不備を検出してHTMLページを生成します。

---

## 特徴

- ✅ 特商法の必須項目を網羅（決済サービスの審査対応）
- ✅ 記載漏れ・フォーマット不備を自動チェック
- ✅ スタンドアロンHTMLページの自動生成
- ✅ フレームワーク非依存（Laravel / CakePHP / EC-CUBE など）
- ✅ Composerで導入可能
- ✅ WordPressプラグインのコアとして利用可能

---

## 動作環境

- PHP 8.1 以上

---

## Webツールとして使う（非エンジニア向け）

Composerの知識がなくても、ブラウザ上のフォームから特商法ページを生成できます。

```bash
# リポジトリをクローン
git clone https://github.com/369work/tokusho-generator.git
cd tokusho-generator

# 依存パッケージをインストール
composer install

# PHPの組み込みサーバーで起動
cd web
php -S localhost:8080
```

ブラウザで `http://localhost:8080` を開くとフォームが表示されます。

フォームに情報を入力して「特商法ページを生成する」を押すと、HTMLファイルをダウンロードできます。

---

## インストール（ライブラリとして使う）

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
| `delivery_timing`       | サービス提供時期・引き渡し時期           | ✅   |
| `software_requirements` | 動作環境（ソフトウェア・デジタルコンテンツ取引） | ―    |
| `subscription_terms`    | 継続契約に関する事項（サブスク等）       | ―    |
| `warranty_policy`       | 契約不適合責任（品質・欠陥に関する責任） | ―    |
| `return_policy`         | 返品・キャンセルポリシー               | ✅   |
| `other`                 | その他                               | ―    |

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

GNU General Public License v3.0 or later

このライブラリはGPL-3.0-or-laterのもとで公開されています。
WordPressはGPL-2.0-or-laterで配布されており、ライセンス的に互換性があります。

詳細は [LICENSE](./LICENSE) ファイルを参照してください。

---

## 免責事項

このライブラリは特商法ページの作成を補助するツールです。
生成されたページの法的有効性・完全性・各決済サービスの審査通過を保証するものではありません。
実際の運用にあたっては、最新の法令および各サービスの規約をご自身でご確認ください。

本ライブラリの使用によって生じたいかなる損害・不利益についても、作者は一切の責任を負いません。

---

## Copyright

Copyright (c) 2025 miroku (369work)  
Licensed under the [GPL-3.0-or-later](./LICENSE)
