<?php

declare(strict_types=1);

namespace Tokusho;

use Tokusho\Field\FieldRegistry;
use Tokusho\Field\FieldDefinition;
use Tokusho\Validator\TokushoValidator;
use Tokusho\Renderer\HtmlRenderer;
use Tokusho\Exception\ValidationException;

/**
 * 特商法ページ自動生成クラス（エントリーポイント）
 *
 * 特定商取引法に基づく表示ページの生成・バリデーション・データ出力を担う。
 * Stripe審査通過を主目的とした必須項目を網羅している。
 *
 * @example
 * ```php
 * use Tokusho\TokushoGenerator;
 *
 * $generator = new TokushoGenerator([
 *     'company_name'     => '株式会社〇〇',
 *     'responsible_name' => '山田太郎',
 *     'address'          => '東京都渋谷区〇〇1-2-3',
 *     'phone'            => '03-1234-5678',
 *     'email'            => 'info@example.com',
 *     'price_description' => '各商品ページに記載の価格（税込）',
 *     'payment_methods'  => 'クレジットカード（Visa / Mastercard）',
 *     'payment_timing'   => '購入手続き完了時',
 *     'shipping_fee'     => '送料無料（デジタル納品）',
 *     'delivery_timing'  => '決済完了後、即時ダウンロード可能',
 *     'return_policy'    => '商品の性質上、購入後の返品・返金はお断りしています。',
 * ]);
 *
 * $generator->validate(); // 不備チェック（問題があれば ValidationException をスロー）
 * echo $generator->toHTML(); // HTMLページ出力
 * ```
 */
class TokushoGenerator
{
    private FieldRegistry $registry;
    private TokushoValidator $validator;

    /**
     * @param array<string, string> $data    フィールドキー => 値
     * @param array<string, mixed>  $options レンダラーオプション（HtmlRenderer のコンストラクタ参照）
     */
    public function __construct(
        private array $data = [],
        private array $options = [],
    ) {
        $this->registry  = new FieldRegistry();
        $this->validator = new TokushoValidator($this->registry);
    }

    /**
     * 入力データを検証する
     *
     * エラーがある場合は ValidationException をスローする。
     *
     * @throws ValidationException
     */
    public function validate(): void
    {
        $this->validator->validate($this->data);
    }

    /**
     * バリデーションを実行し、例外をスローせずにエラー配列を返す
     *
     * @return array<string, string[]> エラーがなければ空配列
     */
    public function check(): array
    {
        return $this->validator->check($this->data);
    }

    /**
     * 特商法ページのHTMLを生成して返す
     *
     * @return string HTML文字列
     */
    public function toHTML(): string
    {
        $renderer = new HtmlRenderer($this->registry, $this->options);
        return $renderer->render($this->data);
    }

    /**
     * 入力データを配列で返す
     *
     * フィールド定義の順序に従い、ラベルと値のペアで返す。
     *
     * @return array<string, array{label: string, value: string, required: bool}>
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->registry->all() as $key => $field) {
            $result[$key] = [
                'label'    => $field->getLabel(),
                'value'    => isset($this->data[$key]) ? trim((string) $this->data[$key]) : '',
                'required' => $field->isRequired(),
            ];
        }

        return $result;
    }

    /**
     * 入力データをJSONで返す
     *
     * @param int $flags json_encode のフラグ（デフォルトは日本語をそのまま出力）
     * @return string JSON文字列
     */
    public function toJSON(int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        $encoded = json_encode($this->toArray(), $flags);

        if ($encoded === false) {
            return '{}';
        }

        return $encoded;
    }

    /**
     * フィールドの値をセットする
     *
     * @param string $key   フィールドキー
     * @param string $value 値
     */
    public function set(string $key, string $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * 複数フィールドの値をまとめてセットする
     *
     * @param array<string, string> $data
     */
    public function fill(array $data): static
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * フィールドの値を取得する
     *
     * @param string $key     フィールドキー
     * @param string $default 値が未設定の場合のデフォルト値
     */
    public function get(string $key, string $default = ''): string
    {
        return isset($this->data[$key]) ? trim((string) $this->data[$key]) : $default;
    }

    /**
     * フィールドレジストリを取得する（カスタムフィールド追加などに使用）
     */
    public function getRegistry(): FieldRegistry
    {
        return $this->registry;
    }

    /**
     * カスタムフィールドをレジストリに追加する
     *
     * @param FieldDefinition $field 追加するフィールド定義
     */
    public function addField(FieldDefinition $field): static
    {
        $this->registry->add($field);
        return $this;
    }
}
