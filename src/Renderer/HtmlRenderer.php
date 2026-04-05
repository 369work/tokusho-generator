<?php

declare(strict_types=1);

namespace Tokusho\Renderer;

use Tokusho\Field\FieldRegistry;

/**
 * 特商法ページのHTMLを生成するクラス
 *
 * 入力データをもとに、スタンドアロンで使えるHTMLページを生成する。
 * セマンティックHTMLおよびアクセシビリティ（WCAG 2.1 AA）に準拠する。
 */
class HtmlRenderer
{
    /**
     * HTML出力のオプション
     *
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param FieldRegistry         $registry フィールド定義
     * @param array<string, mixed>  $options  生成オプション
     *   - 'page_title'   string  ページの <title> タグの値（デフォルト: '特定商取引法に基づく表示'）
     *   - 'heading'      string  ページ内の <h1> テキスト（デフォルト: '特定商取引法に基づく表示'）
     *   - 'standalone'   bool    完全なHTMLドキュメントを出力するか（デフォルト: true）
     *   - 'lang'         string  html要素の lang 属性（デフォルト: 'ja'）
     */
    public function __construct(
        private readonly FieldRegistry $registry,
        array $options = [],
    ) {
        $this->options = array_merge([
            'page_title' => '特定商取引法に基づく表示',
            'heading'    => '特定商取引法に基づく表示',
            'standalone' => true,
            'lang'       => 'ja',
        ], $options);
    }

    /**
     * 特商法ページのHTMLを生成して返す
     *
     * @param array<string, string> $data フィールドキー => 値
     * @return string 生成されたHTML文字列
     */
    public function render(array $data): string
    {
        $tableRows = $this->buildTableRows($data);

        $bodyContent = $this->buildBody($tableRows);

        if ($this->options['standalone']) {
            return $this->wrapDocument($bodyContent);
        }

        return $bodyContent;
    }

    /**
     * テーブルの行HTMLを組み立てる
     *
     * @param array<string, string> $data
     * @return string
     */
    private function buildTableRows(array $data): string
    {
        $rows = '';

        foreach ($this->registry->all() as $key => $field) {
            $value = isset($data[$key]) ? trim((string) $data[$key]) : '';

            // 任意項目かつ空の場合はスキップ
            if (!$field->isRequired() && $value === '') {
                continue;
            }

            $label = $this->escape($field->getLabel());
            $displayValue = $value !== ''
                ? nl2br($this->escape($value))
                : '<span aria-label="未設定">―</span>';

            $rows .= <<<HTML
                <tr>
                    <th scope="row">{$label}</th>
                    <td>{$displayValue}</td>
                </tr>

            HTML;
        }

        return $rows;
    }

    /**
     * ページ本文HTMLを組み立てる
     */
    private function buildBody(string $tableRows): string
    {
        $heading = $this->escape($this->options['heading']);

        return <<<HTML
<main id="main-content">
    <div class="tokusho-wrapper">
        <article class="tokusho-article" aria-labelledby="tokusho-heading">
            <header class="tokusho-header">
                <span class="tokusho-badge" aria-hidden="true">法律に基づく表示</span>
                <h1 id="tokusho-heading" class="tokusho-heading">{$heading}</h1>
                <p class="tokusho-subtitle">特定商取引に関する法律第11条に基づき、以下の通り表示します。</p>
            </header>

            <div class="tokusho-table-wrap">
                <table class="tokusho-table">
                    <caption class="sr-only">特定商取引法に基づく事業者情報</caption>
                    <tbody>
{$tableRows}                    </tbody>
                </table>
            </div>

            <footer class="tokusho-footer">
                <p>本ページの内容は予告なく変更されることがあります。</p>
            </footer>
        </article>
    </div>
</main>
HTML;
    }

    /**
     * 完全なHTMLドキュメントでラップする
     */
    private function wrapDocument(string $bodyContent): string
    {
        $lang       = $this->escape($this->options['lang']);
        $pageTitle  = $this->escape($this->options['page_title']);

        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{$pageTitle}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ============================================================
           CSS変数（カラーパレット・スペーシング）
           ============================================================ */
        :root {
            --color-bg:          #F7F6F3;
            --color-surface:     #FFFFFF;
            --color-navy:        #1C2340;
            --color-navy-light:  #2E3A5C;
            --color-gold:        #B89A5E;
            --color-gold-light:  #D4B97A;
            --color-text:        #2D2D2D;
            --color-text-muted:  #6B6B6B;
            --color-border:      #E8E5DF;
            --color-row-hover:   #F0EDE6;

            --font-serif: 'Noto Serif JP', 'Yu Mincho', '游明朝', serif;
            --font-sans:  'Noto Sans JP', 'Hiragino Sans', 'Hiragino Kaku Gothic ProN', sans-serif;

            --radius-sm:  4px;
            --radius-md:  8px;
            --radius-lg:  16px;

            --shadow-card: 0 2px 24px rgba(28, 35, 64, 0.08), 0 1px 4px rgba(28, 35, 64, 0.04);
        }

        /* ============================================================
           リセット・ベース
           ============================================================ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-sans);
            font-size: 15px;
            line-height: 1.8;
            color: var(--color-text);
            background-color: var(--color-bg);
            -webkit-font-smoothing: antialiased;
        }

        /* ============================================================
           アクセシビリティ
           ============================================================ */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .skip-link {
            position: absolute;
            top: -100%;
            left: 1rem;
            padding: 0.5rem 1rem;
            background: var(--color-navy);
            color: #fff;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            text-decoration: none;
            z-index: 9999;
            transition: top 0.2s;
        }
        .skip-link:focus {
            top: 1rem;
        }

        /* ============================================================
           レイアウト
           ============================================================ */
        #main-content {
            min-height: 100vh;
            padding: 3rem 1.5rem 5rem;
        }

        .tokusho-wrapper {
            max-width: 820px;
            margin: 0 auto;
        }

        /* ============================================================
           カード（メインコンテナ）
           ============================================================ */
        .tokusho-article {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            animation: fadeUp 0.5s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ============================================================
           ヘッダー
           ============================================================ */
        .tokusho-header {
            background: var(--color-navy);
            padding: 2.5rem 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        /* 装飾：背景の幾何学パターン */
        .tokusho-header::before {
            content: '';
            position: absolute;
            top: -40px;
            right: -40px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 40px solid rgba(184, 154, 94, 0.12);
            pointer-events: none;
        }
        .tokusho-header::after {
            content: '';
            position: absolute;
            bottom: -60px;
            right: 80px;
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 30px solid rgba(184, 154, 94, 0.07);
            pointer-events: none;
        }

        .tokusho-badge {
            display: inline-block;
            font-family: var(--font-sans);
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            color: var(--color-gold);
            border: 1px solid var(--color-gold);
            border-radius: 2px;
            padding: 0.2em 0.75em;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
        }

        .tokusho-heading {
            font-family: var(--font-serif);
            font-size: clamp(1.25rem, 3vw, 1.75rem);
            font-weight: 600;
            color: #FFFFFF;
            letter-spacing: 0.05em;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .tokusho-subtitle {
            margin-top: 0.625rem;
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.55);
            letter-spacing: 0.02em;
            position: relative;
            z-index: 1;
        }

        /* ============================================================
           テーブル
           ============================================================ */
        .tokusho-table-wrap {
            padding: 0.5rem 0;
        }

        .tokusho-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tokusho-table tr {
            border-bottom: 1px solid var(--color-border);
            transition: background 0.15s;
        }

        .tokusho-table tr:last-child {
            border-bottom: none;
        }

        .tokusho-table tr:hover {
            background: var(--color-row-hover);
        }

        .tokusho-table th,
        .tokusho-table td {
            text-align: left;
            vertical-align: top;
            padding: 1rem 2rem;
        }

        .tokusho-table th {
            font-family: var(--font-sans);
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--color-navy);
            letter-spacing: 0.03em;
            width: 32%;
            white-space: nowrap;
            padding-right: 1rem;
        }

        /* th の左にゴールドのアクセントライン */
        .tokusho-table th::before {
            content: '';
            display: inline-block;
            width: 3px;
            height: 0.85em;
            background: var(--color-gold);
            border-radius: 2px;
            margin-right: 0.625rem;
            vertical-align: middle;
            flex-shrink: 0;
        }

        .tokusho-table td {
            font-size: 0.9375rem;
            color: var(--color-text);
            line-height: 1.75;
        }

        /* ============================================================
           フッター
           ============================================================ */
        .tokusho-footer {
            border-top: 1px solid var(--color-border);
            padding: 1.25rem 2rem;
            background: var(--color-bg);
        }

        .tokusho-footer p {
            font-size: 0.75rem;
            color: var(--color-text-muted);
            letter-spacing: 0.02em;
        }

        /* ============================================================
           レスポンシブ
           ============================================================ */
        @media (max-width: 640px) {
            #main-content {
                padding: 1.5rem 1rem 4rem;
            }

            .tokusho-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .tokusho-table th,
            .tokusho-table td {
                display: block;
                width: 100%;
                padding: 0.75rem 1.5rem;
            }

            .tokusho-table th {
                white-space: normal;
                padding-bottom: 0.25rem;
                border-bottom: none;
            }

            .tokusho-table td {
                padding-top: 0;
            }

            .tokusho-table tr {
                padding: 0.5rem 0;
            }

            .tokusho-footer {
                padding: 1rem 1.5rem;
            }
        }

        /* ============================================================
           印刷
           ============================================================ */
        @media print {
            body {
                background: #fff;
            }

            .tokusho-article {
                box-shadow: none;
            }

            .tokusho-header {
                background: #1C2340 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .tokusho-table tr:hover {
                background: transparent;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">メインコンテンツへスキップ</a>

{$bodyContent}
</body>
</html>
HTML;
    }

    /**
     * HTML特殊文字をエスケープする
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
