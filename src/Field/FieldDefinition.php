<?php

declare(strict_types=1);

namespace Tokusho\Field;

/**
 * 特商法フィールドの定義クラス
 *
 * 各フィールドのキー・ラベル・必須フラグ・説明・バリデーションルールを保持する。
 */
class FieldDefinition
{
    /**
     * @param string   $key         フィールドキー（内部識別子）
     * @param string   $label       表示ラベル（日本語）
     * @param bool     $required    必須フラグ
     * @param string   $description 入力ガイドの説明文
     * @param string   $type        入力タイプ（text / email / tel / textarea / url）
     * @param int|null $maxLength   最大文字数（null = 制限なし）
     */
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly bool $required,
        private readonly string $description = '',
        private readonly string $type = 'text',
        private readonly ?int $maxLength = null,
    ) {}

    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }
}
