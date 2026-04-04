<?php

declare(strict_types=1);

namespace Tokusho\Tests;

/**
 * テスト用のフィクスチャデータを提供するトレイト
 *
 * 各テストクラスで共通して使う「バリデーションが通る正常なデータ」を
 * 一箇所にまとめることで、テストコードの重複を防ぐ。
 */
trait ValidDataFixture
{
    /**
     * バリデーションが通る最小限の正常データを返す
     *
     * @return array<string, string>
     */
    private function validData(): array
    {
        return [
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
        ];
    }
}
