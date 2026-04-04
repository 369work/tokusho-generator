<?php

declare(strict_types=1);

namespace Tokusho\Tests;

use PHPUnit\Framework\TestCase;
use Tokusho\Exception\ValidationException;
use Tokusho\Field\FieldDefinition;
use Tokusho\TokushoGenerator;

/**
 * TokushoGenerator のテスト
 *
 * エントリーポイントとなるメインクラスの動作を総合的に検証する。
 */
class TokushoGeneratorTest extends TestCase
{
    use ValidDataFixture;

    // ---------------------------------------------------------------
    // コンストラクタ・set・fill・get
    // ---------------------------------------------------------------

    public function test_コンストラクタで渡したデータをgetで取得できる(): void
    {
        $generator = new TokushoGenerator(['company_name' => '株式会社テスト']);

        $this->assertSame('株式会社テスト', $generator->get('company_name'));
    }

    public function test_存在しないキーのgetはデフォルト値を返す(): void
    {
        $generator = new TokushoGenerator();

        $this->assertSame('', $generator->get('company_name'));
        $this->assertSame('デフォルト', $generator->get('company_name', 'デフォルト'));
    }

    public function test_setでフィールドをセットできる(): void
    {
        $generator = new TokushoGenerator();
        $generator->set('company_name', '株式会社テスト');

        $this->assertSame('株式会社テスト', $generator->get('company_name'));
    }

    public function test_setはメソッドチェーンできる(): void
    {
        $generator = new TokushoGenerator();
        $result    = $generator
            ->set('company_name', '株式会社テスト')
            ->set('email', 'info@example.com');

        $this->assertSame($generator, $result);
        $this->assertSame('info@example.com', $generator->get('email'));
    }

    public function test_fillで複数フィールドをまとめてセットできる(): void
    {
        $generator = new TokushoGenerator();
        $generator->fill([
            'company_name' => '株式会社テスト',
            'email'        => 'info@example.com',
        ]);

        $this->assertSame('株式会社テスト', $generator->get('company_name'));
        $this->assertSame('info@example.com', $generator->get('email'));
    }

    public function test_getは値の前後の空白をトリムして返す(): void
    {
        $generator = new TokushoGenerator(['company_name' => '  株式会社テスト  ']);

        $this->assertSame('株式会社テスト', $generator->get('company_name'));
    }

    // ---------------------------------------------------------------
    // validate() / check()
    // ---------------------------------------------------------------

    public function test_正常なデータでvalidateは例外をスローしない(): void
    {
        $generator = new TokushoGenerator($this->validData());

        $this->expectNotToPerformAssertions();
        $generator->validate();
    }

    public function test_必須項目が空のデータでvalidateはValidationExceptionをスローする(): void
    {
        $generator = new TokushoGenerator();

        $this->expectException(ValidationException::class);
        $generator->validate();
    }

    public function test_正常なデータでcheckは空配列を返す(): void
    {
        $generator = new TokushoGenerator($this->validData());

        $this->assertSame([], $generator->check());
    }

    public function test_エラーがある場合checkはエラー配列を返す(): void
    {
        $generator = new TokushoGenerator();
        $errors    = $generator->check();

        $this->assertIsArray($errors);
        $this->assertNotEmpty($errors);
    }

    // ---------------------------------------------------------------
    // toArray()
    // ---------------------------------------------------------------

    public function test_toArrayはすべてのフィールドを返す(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $array     = $generator->toArray();

        // FieldRegistry のデフォルト 14 件が全部含まれること
        $this->assertCount(14, $array);
    }

    public function test_toArrayの各要素にlabel_value_requiredキーがある(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $array     = $generator->toArray();

        foreach ($array as $key => $item) {
            $this->assertArrayHasKey('label', $item, "{$key} に label がありません");
            $this->assertArrayHasKey('value', $item, "{$key} に value がありません");
            $this->assertArrayHasKey('required', $item, "{$key} に required がありません");
        }
    }

    public function test_toArrayのrequiredが正しく設定されている(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $array     = $generator->toArray();

        $this->assertTrue($array['company_name']['required']);
        $this->assertFalse($array['address_note']['required']);
    }

    public function test_toArrayのvalueは入力した値と一致する(): void
    {
        $generator = new TokushoGenerator(['company_name' => '株式会社テスト']);
        $array     = $generator->toArray();

        $this->assertSame('株式会社テスト', $array['company_name']['value']);
    }

    // ---------------------------------------------------------------
    // toJSON()
    // ---------------------------------------------------------------

    public function test_toJSONは有効なJSON文字列を返す(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $json      = $generator->toJSON();

        $this->assertJson($json);
    }

    public function test_toJSONをデコードするとtoArrayと同じ構造になる(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $array     = $generator->toArray();
        $decoded   = json_decode($generator->toJSON(), true);

        $this->assertSame($array, $decoded);
    }

    public function test_toJSONはデフォルトで日本語をエスケープしない(): void
    {
        $generator = new TokushoGenerator(['company_name' => '日本語テスト']);
        $json      = $generator->toJSON();

        $this->assertStringContainsString('日本語テスト', $json);
    }

    // ---------------------------------------------------------------
    // toHTML()
    // ---------------------------------------------------------------

    public function test_toHTMLはHTML文字列を返す(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $html      = $generator->toHTML();

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('</html>', $html);
    }

    public function test_toHTMLにオプションを渡せる(): void
    {
        $generator = new TokushoGenerator(
            data: $this->validData(),
            options: ['page_title' => 'カスタムタイトル'],
        );

        $html = $generator->toHTML();

        $this->assertStringContainsString('<title>カスタムタイトル</title>', $html);
    }

    // ---------------------------------------------------------------
    // addField() / getRegistry()
    // ---------------------------------------------------------------

    public function test_addFieldでカスタムフィールドを追加できる(): void
    {
        $generator = new TokushoGenerator($this->validData());
        $generator->addField(new FieldDefinition(
            key: 'license_number',
            label: '古物商許可番号',
            required: false,
        ));

        $array = $generator->toArray();

        $this->assertArrayHasKey('license_number', $array);
    }

    public function test_addFieldはメソッドチェーンできる(): void
    {
        $generator = new TokushoGenerator();
        $result    = $generator->addField(new FieldDefinition(
            key: 'custom_field',
            label: 'カスタムフィールド',
            required: false,
        ));

        $this->assertSame($generator, $result);
    }

    public function test_getRegistryはFieldRegistryのインスタンスを返す(): void
    {
        $generator = new TokushoGenerator();
        $registry  = $generator->getRegistry();

        $this->assertInstanceOf(\Tokusho\Field\FieldRegistry::class, $registry);
    }
}
