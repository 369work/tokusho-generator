<?php

declare(strict_types=1);

namespace Tokusho\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Tokusho\Field\FieldRegistry;
use Tokusho\Renderer\HtmlRenderer;
use Tokusho\Tests\ValidDataFixture;

/**
 * HtmlRenderer のテスト
 *
 * HTMLの構造・エスケープ・オプション反映・アクセシビリティ要素を検証する。
 */
class HtmlRendererTest extends TestCase
{
    use ValidDataFixture;

    private FieldRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new FieldRegistry();
    }

    // ---------------------------------------------------------------
    // standalone モード（デフォルト）
    // ---------------------------------------------------------------

    public function test_standaloneモードでDOCTYPEが出力される(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringStartsWith('<!DOCTYPE html>', $html);
    }

    public function test_standaloneモードでhtmlタグが含まれる(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('<html lang="ja">', $html);
    }

    public function test_standaloneモードでtitleタグが含まれる(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('<title>特定商取引法に基づく表示</title>', $html);
    }

    public function test_standaloneモードでスキップリンクが含まれる(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('href="#main-content"', $html);
        $this->assertStringContainsString('メインコンテンツへスキップ', $html);
    }

    // ---------------------------------------------------------------
    // standalone: false モード
    // ---------------------------------------------------------------

    public function test_standalone_falseではDOCTYPEが出力されない(): void
    {
        $renderer = new HtmlRenderer($this->registry, ['standalone' => false]);
        $html     = $renderer->render($this->validData());

        $this->assertStringNotContainsString('<!DOCTYPE html>', $html);
    }

    public function test_standalone_falseではmainタグから始まる(): void
    {
        $renderer = new HtmlRenderer($this->registry, ['standalone' => false]);
        $html     = $renderer->render($this->validData());

        $this->assertStringStartsWith('<main', $html);
    }

    // ---------------------------------------------------------------
    // オプションの反映
    // ---------------------------------------------------------------

    public function test_page_titleオプションがtitleタグに反映される(): void
    {
        $renderer = new HtmlRenderer($this->registry, [
            'page_title' => 'カスタムタイトル',
        ]);
        $html = $renderer->render($this->validData());

        $this->assertStringContainsString('<title>カスタムタイトル</title>', $html);
    }

    public function test_headingオプションがh1に反映される(): void
    {
        $renderer = new HtmlRenderer($this->registry, [
            'heading' => 'カスタム見出し',
        ]);
        $html = $renderer->render($this->validData());

        $this->assertStringContainsString('カスタム見出し', $html);
    }

    public function test_langオプションがhtml要素のlang属性に反映される(): void
    {
        $renderer = new HtmlRenderer($this->registry, ['lang' => 'en']);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('lang="en"', $html);
    }

    // ---------------------------------------------------------------
    // HTMLの構造・アクセシビリティ
    // ---------------------------------------------------------------

    public function test_mainタグにid属性が設定されている(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('id="main-content"', $html);
    }

    public function test_h1にidが設定されaria属性で参照されている(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('id="tokusho-heading"', $html);
        $this->assertStringContainsString('aria-labelledby="tokusho-heading"', $html);
    }

    public function test_tableのthにscope_row属性が設定されている(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('scope="row"', $html);
    }

    public function test_tableにcaptionが設定されている(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('<caption', $html);
        $this->assertStringContainsString('特定商取引法に基づく事業者情報', $html);
    }

    // ---------------------------------------------------------------
    // データの出力
    // ---------------------------------------------------------------

    public function test_入力した会社名がHTMLに出力される(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        $this->assertStringContainsString('山田太郎商店', $html);
    }

    public function test_入力した全フィールドがHTMLに含まれる(): void
    {
        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($this->validData());

        foreach ($this->validData() as $value) {
            $this->assertStringContainsString($value, $html);
        }
    }

    public function test_任意項目が空の場合はテーブル行に含まれない(): void
    {
        $data = $this->validData();
        // address_note は任意項目で未入力
        unset($data['address_note']);

        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($data);

        // ラベルそのものが出力されていないことを確認
        $this->assertStringNotContainsString('所在地に関する補足', $html);
    }

    public function test_必須項目が空の場合はダッシュ記号で出力される(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = '';

        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($data);

        // 必須項目は空でも行が出力されダッシュが入る
        $this->assertStringContainsString('販売業者名', $html);
        $this->assertStringContainsString('―', $html);
    }

    public function test_改行文字がbr要素に変換される(): void
    {
        $data                 = $this->validData();
        $data['address']      = "東京都渋谷区\n神宮前1-2-3";

        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($data);

        $this->assertStringContainsString('<br />', $html);
    }

    // ---------------------------------------------------------------
    // XSSエスケープ
    // ---------------------------------------------------------------

    public function test_スクリプトタグがエスケープされる(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = '<script>alert("XSS")</script>';

        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($data);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_シングルクォートがエスケープされる(): void
    {
        $data                 = $this->validData();
        $data['company_name'] = "山田's商店";

        $renderer = new HtmlRenderer($this->registry);
        $html     = $renderer->render($data);

        // ENT_QUOTES | ENT_HTML5 ではシングルクォートは &apos; にエンコードされる
        // 生の ' がそのまま出力されていないことを確認する
        $this->assertStringNotContainsString("山田's商店", $html);
        $this->assertStringContainsString('山田', $html);
        $this->assertStringContainsString('商店', $html);
    }

    public function test_page_titleにスクリプトが含まれていてもエスケープされる(): void
    {
        $renderer = new HtmlRenderer($this->registry, [
            'page_title' => '<script>alert(1)</script>',
        ]);
        $html = $renderer->render($this->validData());

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
