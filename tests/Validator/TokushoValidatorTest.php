<?php

declare(strict_types=1);

namespace Tokusho\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Tokusho\Exception\ValidationException;
use Tokusho\Field\FieldRegistry;
use Tokusho\Tests\ValidDataFixture;
use Tokusho\Validator\TokushoValidator;

/**
 * TokushoValidator のテスト
 *
 * 必須チェック・形式チェック・文字数チェックを網羅的に検証する。
 */
class TokushoValidatorTest extends TestCase
{
    use ValidDataFixture;

    private TokushoValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TokushoValidator(new FieldRegistry());
    }

    // ---------------------------------------------------------------
    // 正常系 — validate()
    // ---------------------------------------------------------------

    public function test_正常なデータではvalidateが例外をスローしない(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate($this->validData());
    }

    public function test_任意項目が空でもvalidateが通過する(): void
    {
        $data = $this->validData();
        unset($data['address_note'], $data['website_url'], $data['other']);

        $this->expectNotToPerformAssertions();
        $this->validator->validate($data);
    }

    // ---------------------------------------------------------------
    // 必須チェック — validate()
    // ---------------------------------------------------------------

    /**
     * @dataProvider requiredFieldProvider
     */
    public function test_必須項目が空の場合はValidationExceptionがスローされる(string $key): void
    {
        $data       = $this->validData();
        $data[$key] = '';

        $this->expectException(ValidationException::class);
        $this->validator->validate($data);
    }

    /**
     * @dataProvider requiredFieldProvider
     */
    public function test_必須項目が空の場合のエラーに該当フィールドが含まれる(string $key): void
    {
        $data       = $this->validData();
        $data[$key] = '';

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey($key, $e->getErrors());
        }
    }

    public function test_必須項目が空白だけの場合もエラーになる(): void
    {
        $data                = $this->validData();
        $data['company_name'] = '   ';

        $this->expectException(ValidationException::class);
        $this->validator->validate($data);
    }

    public function test_複数の必須項目が空の場合は複数エラーが返る(): void
    {
        $data                   = $this->validData();
        $data['company_name']   = '';
        $data['email']          = '';
        $data['return_policy']  = '';

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('company_name', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('return_policy', $errors);
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function requiredFieldProvider(): array
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

    // ---------------------------------------------------------------
    // メールアドレスの形式チェック
    // ---------------------------------------------------------------

    /**
     * @dataProvider validEmailProvider
     */
    public function test_正しいメールアドレス形式はエラーにならない(string $email): void
    {
        $data          = $this->validData();
        $data['email'] = $email;

        $this->expectNotToPerformAssertions();
        $this->validator->validate($data);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validEmailProvider(): array
    {
        return [
            '基本的な形式'           => ['info@example.com'],
            'サブドメインあり'       => ['user@mail.example.co.jp'],
            'プラス記号あり'         => ['user+tag@example.com'],
            'ドットあり'             => ['first.last@example.com'],
        ];
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_不正なメールアドレス形式はエラーになる(string $email): void
    {
        $data          = $this->validData();
        $data['email'] = $email;

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->getErrors());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidEmailProvider(): array
    {
        return [
            '@マークなし'    => ['notanemail'],
            'ドメインなし'   => ['user@'],
            'ローカルなし'   => ['@example.com'],
            '二重@'          => ['user@@example.com'],
            'スペースあり'   => ['user @example.com'],
        ];
    }

    // ---------------------------------------------------------------
    // 電話番号の形式チェック
    // ---------------------------------------------------------------

    /**
     * @dataProvider validPhoneProvider
     */
    public function test_正しい電話番号形式はエラーにならない(string $phone): void
    {
        $data          = $this->validData();
        $data['phone'] = $phone;

        $this->expectNotToPerformAssertions();
        $this->validator->validate($data);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validPhoneProvider(): array
    {
        return [
            '固定電話ハイフンあり'   => ['03-1234-5678'],
            '固定電話ハイフンなし'   => ['0312345678'],
            '携帯電話ハイフンあり'   => ['090-1234-5678'],
            '携帯電話ハイフンなし'   => ['09012345678'],
            '国際形式'               => ['+81312345678'],
            'フリーダイヤル'         => ['0120-123-456'],
        ];
    }

    /**
     * @dataProvider invalidPhoneProvider
     */
    public function test_不正な電話番号形式はエラーになる(string $phone): void
    {
        $data          = $this->validData();
        $data['phone'] = $phone;

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('phone', $e->getErrors());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidPhoneProvider(): array
    {
        return [
            '数字が短すぎる'     => ['0312345'],
            '文字列が混入'       => ['abc-defg-hijk'],
            '記号のみ'           => ['----'],
        ];
    }

    // ---------------------------------------------------------------
    // URLの形式チェック
    // ---------------------------------------------------------------

    /**
     * @dataProvider validUrlProvider
     */
    public function test_正しいURL形式はエラーにならない(string $url): void
    {
        $data                  = $this->validData();
        $data['website_url']   = $url;

        $this->expectNotToPerformAssertions();
        $this->validator->validate($data);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validUrlProvider(): array
    {
        return [
            'https'          => ['https://example.com'],
            'http'           => ['http://example.com'],
            'パスあり'        => ['https://example.com/shop/tokusho'],
            'クエリあり'      => ['https://example.com/?page=tokusho'],
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    public function test_不正なURL形式はエラーになる(string $url): void
    {
        $data                = $this->validData();
        $data['website_url'] = $url;

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('website_url', $e->getErrors());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidUrlProvider(): array
    {
        return [
            'スキームなし'       => ['example.com'],
            'ftpスキーム'         => ['ftp://example.com'],   // http/https のみ許可
            'スペースあり'       => ['https://exam ple.com'],
        ];
    }

    // ---------------------------------------------------------------
    // 文字数チェック
    // ---------------------------------------------------------------

    public function test_最大文字数以内であればエラーにならない(): void
    {
        $data                  = $this->validData();
        $data['company_name']  = str_repeat('あ', 200); // maxLength = 200

        $this->expectNotToPerformAssertions();
        $this->validator->validate($data);
    }

    public function test_最大文字数を超えるとエラーになる(): void
    {
        $data                  = $this->validData();
        $data['company_name']  = str_repeat('あ', 201); // maxLength = 200

        try {
            $this->validator->validate($data);
            $this->fail('ValidationException がスローされるべきです');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('company_name', $e->getErrors());
        }
    }

    // ---------------------------------------------------------------
    // check() のテスト
    // ---------------------------------------------------------------

    public function test_check_正常なデータでは空配列を返す(): void
    {
        $errors = $this->validator->check($this->validData());

        $this->assertSame([], $errors);
    }

    public function test_check_エラーがある場合はエラー配列を返す(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = '';

        $errors = $this->validator->check($data);

        $this->assertArrayHasKey('company_name', $errors);
        $this->assertNotEmpty($errors['company_name']);
    }

    public function test_check_例外をスローしない(): void
    {
        // エラーがあっても例外ではなく配列で返ることを確認
        $errors = $this->validator->check([]);

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    // ---------------------------------------------------------------
    // ValidationException のテスト
    // ---------------------------------------------------------------

    public function test_ValidationException_getErrorsで全エラーを取得できる(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = '';
        $data['email']        = 'invalid';

        try {
            $this->validator->validate($data);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('company_name', $errors);
            $this->assertArrayHasKey('email', $errors);
        }
    }

    public function test_ValidationException_getFieldErrorsで特定フィールドのエラーを取得できる(): void
    {
        $data          = $this->validData();
        $data['email'] = 'invalid';

        try {
            $this->validator->validate($data);
        } catch (ValidationException $e) {
            $fieldErrors = $e->getFieldErrors('email');
            $this->assertNotEmpty($fieldErrors);
        }
    }

    public function test_ValidationException_存在しないフィールドのgetFieldErrorsは空配列(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = '';

        try {
            $this->validator->validate($data);
        } catch (ValidationException $e) {
            $fieldErrors = $e->getFieldErrors('non_existent');
            $this->assertSame([], $fieldErrors);
        }
    }
}
