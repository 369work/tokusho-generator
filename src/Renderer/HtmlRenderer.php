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
    <article aria-labelledby="tokusho-heading">
        <h1 id="tokusho-heading">{$heading}</h1>
        <table>
            <caption class="sr-only">特定商取引法に基づく事業者情報</caption>
            <tbody>
{$tableRows}            </tbody>
        </table>
    </article>
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
    <style>
        /* 最小限のデフォルトスタイル。実際の運用ではサイトのCSSに合わせて調整してください */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Hiragino Sans', 'Hiragino Kaku Gothic ProN',
                         'Noto Sans JP', sans-serif;
            line-height: 1.7;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* スクリーンリーダー専用テキスト */
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

        #main-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #333;
            padding-bottom: 0.5rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            text-align: left;
            vertical-align: top;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f5f5f5;
            font-weight: bold;
            width: 30%;
            white-space: nowrap;
        }

        @media (max-width: 600px) {
            table,
            thead,
            tbody,
            tr,
            th,
            td {
                display: block;
                width: 100%;
            }

            th {
                white-space: normal;
                border-bottom: none;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="sr-only">メインコンテンツへスキップ</a>

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
