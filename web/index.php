<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// ※ vendor フォルダが見つからない場合は以下のエラーが出ます
// → tokusho-generator フォルダごと htdocs にコピーしてください

use Tokusho\TokushoGenerator;

$generator = new TokushoGenerator();
$errors    = [];
$html      = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'company_name'      => $_POST['company_name']      ?? '',
        'responsible_name'  => $_POST['responsible_name']  ?? '',
        'address'           => $_POST['address']           ?? '',
        'address_note'      => $_POST['address_note']      ?? '',
        'phone'             => $_POST['phone']             ?? '',
        'email'             => $_POST['email']             ?? '',
        'website_url'       => $_POST['website_url']       ?? '',
        'price_description' => $_POST['price_description'] ?? '',
        'payment_methods'   => $_POST['payment_methods']   ?? '',
        'payment_timing'    => $_POST['payment_timing']    ?? '',
        'shipping_fee'      => $_POST['shipping_fee']      ?? '',
        'delivery_timing'        => $_POST['delivery_timing']        ?? '',
        'software_requirements'  => $_POST['software_requirements']  ?? '',
        'subscription_terms'     => $_POST['subscription_terms']     ?? '',
        'warranty_policy'        => $_POST['warranty_policy']        ?? '',
        'return_policy'          => $_POST['return_policy']          ?? '',
        'other'                  => $_POST['other']                  ?? '',
    ];

    $generator->fill($data);
    $errors = $generator->check();

    // ダウンロードリクエスト
    if (isset($_POST['download']) && empty($errors)) {
        $htmlContent = $generator->toHTML();
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: attachment; filename="tokusho.html"');
        echo $htmlContent;
        exit;
    }

    if (empty($errors)) {
        $html = $generator->toHTML(['standalone' => false]);
    }
}

/**
 * 入力値を安全に出力する
 */
function val(string $key): string
{
    $value = $_POST[$key] ?? '';
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * フィールドにエラークラスを付与する
 */
function errClass(array $errors, string $key): string
{
    return isset($errors[$key]) ? ' is-error' : '';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>特商法ページ 自動生成ツール</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="#main-content" class="skip-link">メインコンテンツへスキップ</a>

    <header class="site-header" role="banner">
        <div class="header-inner">
            <p class="site-title">特商法ページ<span>自動生成ツール</span></p>
            <p class="site-desc">決済サービスの審査も通る特定商取引法ページを、フォームを埋めるだけで自動生成します。</p>
        </div>
    </header>

    <main id="main-content">
        <div class="container">

            <?php if (!empty($errors)) : ?>
            <div class="alert alert--error" role="alert" aria-live="polite">
                <p class="alert__title">⚠ 入力に不備があります</p>
                <ul class="alert__list">
                    <?php foreach ($errors as $fieldErrors) : ?>
                        <?php foreach ($fieldErrors as $msg) : ?>
                            <li><?= htmlspecialchars($msg, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($html !== null) : ?>
            <div class="result" id="result">
                <div class="result__header">
                    <p class="result__title">✅ 生成完了！プレビューを確認してください</p>
                    <form method="post" action="#result">
                        <?php foreach ($_POST as $k => $v) : ?>
                            <?php if ($k !== 'download') : ?>
                                <input type="hidden" name="<?= htmlspecialchars($k, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>"
                                       value="<?= htmlspecialchars((string)$v, ENT_QUOTES | ENT_HTML5, 'UTF-8') ?>">
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <button type="submit" name="download" value="1" class="btn btn--download">
                            HTMLファイルをダウンロード
                        </button>
                    </form>
                </div>
                <div class="preview">
                    <?= $html ?>
                </div>
            </div>
            <?php endif; ?>

            <section class="form-section" aria-labelledby="form-heading">
                <h1 id="form-heading" class="form-heading">
                    <?= ($html !== null) ? '内容を修正する' : '情報を入力してください' ?>
                </h1>

                <form method="post" action="#" novalidate>

                    <fieldset class="fieldset">
                        <legend class="fieldset__legend">事業者情報</legend>

                        <div class="field<?= errClass($errors, 'company_name') ?>">
                            <label class="field__label" for="company_name">
                                販売業者名
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">屋号または法人名。個人の場合は氏名（フルネーム）でも可。</p>
                            <input class="field__input" type="text" id="company_name" name="company_name"
                                   value="<?= val('company_name') ?>"
                                   autocomplete="organization"
                                   aria-describedby="company_name-hint"
                                   aria-required="true"
                                   <?= isset($errors['company_name']) ? 'aria-invalid="true"' : '' ?>>
                        </div>

                        <div class="field<?= errClass($errors, 'responsible_name') ?>">
                            <label class="field__label" for="responsible_name">
                                代表者・責任者の氏名
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">法人なら代表取締役、個人事業主なら事業主の氏名。</p>
                            <input class="field__input" type="text" id="responsible_name" name="responsible_name"
                                   value="<?= val('responsible_name') ?>"
                                   autocomplete="name"
                                   aria-required="true"
                                   <?= isset($errors['responsible_name']) ? 'aria-invalid="true"' : '' ?>>
                        </div>

                        <div class="field<?= errClass($errors, 'address') ?>">
                            <label class="field__label" for="address">
                                所在地
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">郵便番号から建物名まで。住所非公開を希望する場合も、決済サービスの審査には実住所が必要です。</p>
                            <textarea class="field__textarea" id="address" name="address"
                                      rows="3"
                                      autocomplete="street-address"
                                      aria-required="true"
                                      <?= isset($errors['address']) ? 'aria-invalid="true"' : '' ?>><?= val('address') ?></textarea>
                        </div>

                        <div class="field">
                            <label class="field__label" for="address_note">
                                所在地の補足
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">例：「個人情報保護のため、住所は請求があった場合に開示します」</p>
                            <textarea class="field__textarea" id="address_note" name="address_note"
                                      rows="2"><?= val('address_note') ?></textarea>
                        </div>

                        <div class="field<?= errClass($errors, 'phone') ?>">
                            <label class="field__label" for="phone">
                                電話番号
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：03-1234-5678 / 09012345678</p>
                            <input class="field__input" type="tel" id="phone" name="phone"
                                   value="<?= val('phone') ?>"
                                   autocomplete="tel"
                                   aria-required="true"
                                   <?= isset($errors['phone']) ? 'aria-invalid="true"' : '' ?>>
                        </div>

                        <div class="field<?= errClass($errors, 'email') ?>">
                            <label class="field__label" for="email">
                                メールアドレス
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">問い合わせ対応に使うメールアドレス。</p>
                            <input class="field__input" type="email" id="email" name="email"
                                   value="<?= val('email') ?>"
                                   autocomplete="email"
                                   aria-required="true"
                                   <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>>
                        </div>

                        <div class="field<?= errClass($errors, 'website_url') ?>">
                            <label class="field__label" for="website_url">
                                ウェブサイトURL
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">例：https://example.com</p>
                            <input class="field__input" type="url" id="website_url" name="website_url"
                                   value="<?= val('website_url') ?>"
                                   autocomplete="url"
                                   <?= isset($errors['website_url']) ? 'aria-invalid="true"' : '' ?>>
                        </div>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset__legend">販売・支払い情報</legend>

                        <div class="field<?= errClass($errors, 'price_description') ?>">
                            <label class="field__label" for="price_description">
                                販売価格
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：「各商品ページに記載の価格（税込）」「月額 ¥9,800（税込）」</p>
                            <textarea class="field__textarea" id="price_description" name="price_description"
                                      rows="3"
                                      aria-required="true"
                                      <?= isset($errors['price_description']) ? 'aria-invalid="true"' : '' ?>><?= val('price_description') ?></textarea>
                        </div>

                        <div class="field<?= errClass($errors, 'payment_methods') ?>">
                            <label class="field__label" for="payment_methods">
                                支払方法
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：「クレジットカード（Visa / Mastercard / American Express）」</p>
                            <textarea class="field__textarea" id="payment_methods" name="payment_methods"
                                      rows="2"
                                      aria-required="true"
                                      <?= isset($errors['payment_methods']) ? 'aria-invalid="true"' : '' ?>><?= val('payment_methods') ?></textarea>
                        </div>

                        <div class="field<?= errClass($errors, 'payment_timing') ?>">
                            <label class="field__label" for="payment_timing">
                                支払時期
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：「購入手続き完了時」「毎月1日に前払い」</p>
                            <textarea class="field__textarea" id="payment_timing" name="payment_timing"
                                      rows="2"
                                      aria-required="true"
                                      <?= isset($errors['payment_timing']) ? 'aria-invalid="true"' : '' ?>><?= val('payment_timing') ?></textarea>
                        </div>

                        <div class="field<?= errClass($errors, 'shipping_fee') ?>">
                            <label class="field__label" for="shipping_fee">
                                送料
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">デジタル商品の場合は「送料無料（デジタル納品）」と記載。</p>
                            <textarea class="field__textarea" id="shipping_fee" name="shipping_fee"
                                      rows="2"
                                      aria-required="true"
                                      <?= isset($errors['shipping_fee']) ? 'aria-invalid="true"' : '' ?>><?= val('shipping_fee') ?></textarea>
                        </div>

                        <div class="field<?= errClass($errors, 'delivery_timing') ?>">
                            <label class="field__label" for="delivery_timing">
                                サービス提供時期・引き渡し時期
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：「決済完了後、即時ダウンロード可能」「ご注文から3〜5営業日以内に発送」</p>
                            <textarea class="field__textarea" id="delivery_timing" name="delivery_timing"
                                      rows="2"
                                      aria-required="true"
                                      <?= isset($errors['delivery_timing']) ? 'aria-invalid="true"' : '' ?>><?= val('delivery_timing') ?></textarea>
                        </div>
                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset__legend">デジタルコンテンツ・継続契約（該当する場合のみ）</legend>

                        <p class="fieldset__note">以下の項目は、取引形態に応じて該当する場合のみ記載が必要です。該当しない場合は空欄のままで構いません。</p>

                        <div class="field">
                            <label class="field__label" for="software_requirements">
                                動作環境
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">ソフトウェア・デジタルコンテンツを販売する場合に記載してください。<br>例：「Windows 10以降 / macOS 12以降 / ブラウザはChrome・Firefox・Edge最新版」</p>
                            <textarea class="field__textarea" id="software_requirements" name="software_requirements"
                                      rows="3"><?= val('software_requirements') ?></textarea>
                        </div>

                        <div class="field">
                            <label class="field__label" for="subscription_terms">
                                継続契約に関する事項
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">月額・年額プランなど2回以上の継続契約を伴うサービスの場合に記載してください。<br>例：「毎月自動更新。解約はマイページより契約更新日の前日23:59までに手続きください。」</p>
                            <textarea class="field__textarea" id="subscription_terms" name="subscription_terms"
                                      rows="3"><?= val('subscription_terms') ?></textarea>
                        </div>

                        <div class="field">
                            <label class="field__label" for="warranty_policy">
                                契約不適合責任（品質・欠陥に関する責任）
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">引き渡した商品・コンテンツが契約内容に適合しない場合の販売業者の責任について記載してください。<br>例：「コンテンツに重大な欠陥が確認された場合は、修正版の提供または返金にて対応します。」</p>
                            <textarea class="field__textarea" id="warranty_policy" name="warranty_policy"
                                      rows="3"><?= val('warranty_policy') ?></textarea>
                        </div>

                    </fieldset>

                    <fieldset class="fieldset">
                        <legend class="fieldset__legend">返品・その他</legend>

                        <div class="field<?= errClass($errors, 'return_policy') ?>">
                            <label class="field__label" for="return_policy">
                                返品・キャンセルポリシー
                                <span class="field__required" aria-label="必須">必須</span>
                            </label>
                            <p class="field__hint">例：「デジタルコンテンツの性質上、購入後の返品・返金はお断りしています」</p>
                            <textarea class="field__textarea" id="return_policy" name="return_policy"
                                      rows="4"
                                      aria-required="true"
                                      <?= isset($errors['return_policy']) ? 'aria-invalid="true"' : '' ?>><?= val('return_policy') ?></textarea>
                        </div>

                        <div class="field">
                            <label class="field__label" for="other">
                                その他
                                <span class="field__optional">任意</span>
                            </label>
                            <p class="field__hint">特記事項があれば記載してください。</p>
                            <textarea class="field__textarea" id="other" name="other"
                                      rows="3"><?= val('other') ?></textarea>
                        </div>
                    </fieldset>

                    <div class="form-submit">
                        <button type="submit" class="btn btn--primary">
                            特商法ページを生成する
                        </button>
                    </div>

                </form>
            </section>

        </div>
    </main>

    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <p>tokusho-generator &mdash; Powered by PHP &amp; <a href="https://github.com/369work/tokusho-generator" target="_blank" rel="noopener">tokusho/tokusho-generator</a></p>
        </div>
    </footer>

</body>
</html>
