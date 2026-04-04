<?php

declare(strict_types=1);

namespace Tokusho\Tests\Field;

use PHPUnit\Framework\TestCase;
use Tokusho\Field\FieldDefinition;
use Tokusho\Field\FieldRegistry;

/**
 * FieldRegistry のテスト
 *
 * フィールド定義の登録・取得・追加が正しく動作するかを検証する。
 */
class FieldRegistryTest extends TestCase
{
    private FieldRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new FieldRegistry();
    }

    // ---------------------------------------------------------------
    // デフォルトフィールドの確認
    // ---------------------------------------------------------------

    public function test_デフォルトで14件のフィールドが登録されている(): void
    {
        $this->assertCount(14, $this->registry->all());
    }

    public function test_必須フィールドが11件登録されている(): void
    {
        $required = $this->registry->getRequired();
        $this->assertCount(11, $required);
    }

    public function test_任意フィールドが3件登録されている(): void
    {
        $all      = $this->registry->all();
        $optional = array_filter($all, fn ($f) => !$f->isRequired());

        $this->assertCount(3, $optional);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('requiredFieldKeyProvider')]
    public function test_必須フィールドのキーが存在する(string $key): void
    {
        $field = $this->registry->get($key);
        $this->assertNotNull($field, "フィールド '{$key}' が登録されていません");
        $this->assertTrue($field->isRequired(), "フィールド '{$key}' は必須のはずです");
    }

    /**
     * @return array<string, array{string}>
     */
    public static function requiredFieldKeyProvider(): array
    {
        return [
            'company_name'      => ['company_name'],
            'responsible_name'  => ['responsible_name'],
            'address'           => ['address'],
            'phone'             => ['phone'],
            'email'             => ['email'],
            'price_description' => ['price_description'],
            'payment_methods'   => ['payment_methods'],
            'payment_timing'    => ['payment_timing'],
            'shipping_fee'      => ['shipping_fee'],
            'delivery_timing'   => ['delivery_timing'],
            'return_policy'     => ['return_policy'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('optionalFieldKeyProvider')]
    public function test_任意フィールドのキーが存在する(string $key): void
    {
        $field = $this->registry->get($key);
        $this->assertNotNull($field, "フィールド '{$key}' が登録されていません");
        $this->assertFalse($field->isRequired(), "フィールド '{$key}' は任意のはずです");
    }

    /**
     * @return array<string, array{string}>
     */
    public static function optionalFieldKeyProvider(): array
    {
        return [
            'address_note' => ['address_note'],
            'website_url'  => ['website_url'],
            'other'        => ['other'],
        ];
    }

    // ---------------------------------------------------------------
    // get() のテスト
    // ---------------------------------------------------------------

    public function test_存在するキーでフィールドを取得できる(): void
    {
        $field = $this->registry->get('company_name');

        $this->assertInstanceOf(FieldDefinition::class, $field);
        $this->assertSame('company_name', $field->getKey());
        $this->assertSame('販売業者名', $field->getLabel());
    }

    public function test_存在しないキーはnullを返す(): void
    {
        $field = $this->registry->get('non_existent_field');

        $this->assertNull($field);
    }

    // ---------------------------------------------------------------
    // add() のテスト（カスタムフィールド追加）
    // ---------------------------------------------------------------

    public function test_カスタムフィールドを追加できる(): void
    {
        $before = count($this->registry->all());

        $this->registry->add(new FieldDefinition(
            key: 'license_number',
            label: '古物商許可番号',
            required: false,
            description: '古物商許可を取得している場合に記載',
            type: 'text',
            maxLength: 100,
        ));

        $this->assertCount($before + 1, $this->registry->all());
        $this->assertNotNull($this->registry->get('license_number'));
    }

    public function test_同じキーで追加すると既存のフィールドが上書きされる(): void
    {
        $original = $this->registry->get('company_name');
        $this->assertNotNull($original);

        $this->registry->add(new FieldDefinition(
            key: 'company_name',
            label: '上書きラベル',
            required: false,
        ));

        $overwritten = $this->registry->get('company_name');
        $this->assertNotNull($overwritten);
        $this->assertSame('上書きラベル', $overwritten->getLabel());
        // 件数は変わらない
        $this->assertCount(14, $this->registry->all());
    }
}
