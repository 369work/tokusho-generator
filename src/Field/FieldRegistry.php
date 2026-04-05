<?php

declare(strict_types=1);

namespace Tokusho\Field;

/**
 * 特商法に必要なフィールド定義の一覧を管理するクラス
 *
 * 特定商取引法に基づく表示に必要な項目をすべて定義する。
 * Stripe審査通過を想定した必須項目を網羅している。
 */
class FieldRegistry
{
    /**
     * 定義済みフィールドの配列
     *
     * @var FieldDefinition[]
     */
    private array $fields = [];

    public function __construct()
    {
        $this->registerDefaultFields();
    }

    /**
     * 特商法の標準フィールドをすべて登録する
     */
    private function registerDefaultFields(): void
    {
        $definitions = [
            new FieldDefinition(
                key: 'company_name',
                label: '販売業者名',
                required: true,
                description: '屋号または法人名を入力してください。個人の場合は氏名（フルネーム）でも可です。',
                type: 'text',
                maxLength: 200,
            ),
            new FieldDefinition(
                key: 'responsible_name',
                label: '代表者または通信販売に関する業務の責任者の氏名',
                required: true,
                description: '法人の場合は代表取締役等の氏名。個人事業主の場合は事業主の氏名を入力してください。',
                type: 'text',
                maxLength: 100,
            ),
            new FieldDefinition(
                key: 'address',
                label: '所在地',
                required: true,
                description: '郵便番号・都道府県・市区町村・番地・建物名まで入力してください。住所非公開を希望する場合でも、Stripe審査には実住所が必要です。',
                type: 'textarea',
                maxLength: 500,
            ),
            new FieldDefinition(
                key: 'address_note',
                label: '所在地に関する補足（任意）',
                required: false,
                description: '「個人情報保護のため、住所は請求があった場合に開示します」などの注記を入力できます。',
                type: 'textarea',
                maxLength: 300,
            ),
            new FieldDefinition(
                key: 'phone',
                label: '電話番号',
                required: true,
                description: 'ハイフンあり・なしどちらでも可。例：03-1234-5678 / 09012345678',
                type: 'tel',
                maxLength: 20,
            ),
            new FieldDefinition(
                key: 'email',
                label: 'メールアドレス',
                required: true,
                description: '問い合わせ対応に使用するメールアドレスを入力してください。',
                type: 'email',
                maxLength: 254,
            ),
            new FieldDefinition(
                key: 'website_url',
                label: 'ウェブサイトURL（任意）',
                required: false,
                description: '販売を行っているウェブサイトのURLを入力してください。',
                type: 'url',
                maxLength: 500,
            ),
            new FieldDefinition(
                key: 'price_description',
                label: '販売価格',
                required: true,
                description: '商品・サービスの価格を記載してください。例：「各商品ページに記載の価格（税込）」「月額 ¥9,800（税込）」',
                type: 'textarea',
                maxLength: 1000,
            ),
            new FieldDefinition(
                key: 'payment_methods',
                label: '支払方法',
                required: true,
                description: '対応している支払方法をすべて記載してください。例：「クレジットカード（Visa / Mastercard / American Express）」',
                type: 'textarea',
                maxLength: 500,
            ),
            new FieldDefinition(
                key: 'payment_timing',
                label: '支払時期',
                required: true,
                description: '支払いが発生するタイミングを記載してください。例：「購入手続き完了時」「月次請求（毎月1日）」',
                type: 'textarea',
                maxLength: 300,
            ),
            new FieldDefinition(
                key: 'shipping_fee',
                label: '送料',
                required: true,
                description: 'デジタル商品・サービスの場合は「送料無料（デジタル納品）」と記載してください。',
                type: 'textarea',
                maxLength: 300,
            ),
            new FieldDefinition(
                key: 'delivery_timing',
                label: 'サービス提供時期・引き渡し時期',
                required: true,
                description: '商品の発送時期またはサービスの利用開始時期を記載してください。例：「決済完了後、即時ダウンロード可能」「ご注文から3〜5営業日以内に発送」',
                type: 'textarea',
                maxLength: 500,
            ),
            // -------------------------------------------------------
            // 以下3項目：特商法施行規則による追加記載事項
            // 該当する取引形態のみ表示される任意項目
            // -------------------------------------------------------
            new FieldDefinition(
                key: 'software_requirements',
                label: '動作環境',
                required: false,
                description: 'ソフトウェアまたはデジタルコンテンツを販売する場合に記載してください。例：「Windows 10以降 / macOS 12以降 / ブラウザはChrome・Firefox・Edge最新版」',
                type: 'textarea',
                maxLength: 1000,
            ),
            new FieldDefinition(
                key: 'subscription_terms',
                label: '継続契約に関する事項',
                required: false,
                description: 'サブスクリプションなど2回以上の継続契約を伴う場合に記載してください。例：「毎月自動更新。解約は契約更新日の前日23:59までにマイページより手続きください。」',
                type: 'textarea',
                maxLength: 1000,
            ),
            new FieldDefinition(
                key: 'warranty_policy',
                label: '契約不適合責任（品質・欠陥に関する責任）',
                required: false,
                description: '引き渡した商品・コンテンツが種類または品質において契約内容に適合しない場合の販売業者の責任を記載してください。例：「コンテンツに重大な欠陥が確認された場合は、修正版の提供または返金で対応します。」',
                type: 'textarea',
                maxLength: 1000,
            ),
            new FieldDefinition(
                key: 'return_policy',
                label: '返品・キャンセルポリシー',
                required: true,
                description: '返品・キャンセルの条件を明記してください。デジタル商品の場合は「商品の性質上、購入後の返品・返金はお断りしています」などを記載してください。',
                type: 'textarea',
                maxLength: 1000,
            ),
            new FieldDefinition(
                key: 'other',
                label: 'その他（任意）',
                required: false,
                description: '特記事項があれば記載してください。',
                type: 'textarea',
                maxLength: 1000,
            ),
        ];

        foreach ($definitions as $definition) {
            $this->fields[$definition->getKey()] = $definition;
        }
    }

    /**
     * すべてのフィールド定義を取得する
     *
     * @return array<string, FieldDefinition>
     */
    public function all(): array
    {
        return $this->fields;
    }

    /**
     * 指定キーのフィールド定義を取得する
     */
    public function get(string $key): ?FieldDefinition
    {
        return $this->fields[$key] ?? null;
    }

    /**
     * 必須フィールドのみ取得する
     *
     * @return array<string, FieldDefinition>
     */
    public function getRequired(): array
    {
        return array_filter($this->fields, fn (FieldDefinition $f) => $f->isRequired());
    }

    /**
     * カスタムフィールドを追加する（拡張用）
     */
    public function add(FieldDefinition $field): void
    {
        $this->fields[$field->getKey()] = $field;
    }
}
