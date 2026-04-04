<?php

declare(strict_types=1);

namespace Tokusho\Exception;

use RuntimeException;

/**
 * バリデーションエラー例外クラス
 *
 * 特商法ページの入力内容に不備があった場合にスローされる。
 */
class ValidationException extends RuntimeException
{
    /**
     * バリデーションエラーの一覧
     *
     * @var array<string, string[]> キー: フィールドキー、値: エラーメッセージの配列
     */
    private array $errors;

    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(array $errors)
    {
        $this->errors = $errors;

        $messages = [];
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = "[{$field}] {$error}";
            }
        }

        parent::__construct(implode("\n", $messages));
    }

    /**
     * エラー一覧を取得する
     *
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 特定フィールドのエラーを取得する
     *
     * @param string $fieldKey フィールドキー
     * @return string[]
     */
    public function getFieldErrors(string $fieldKey): array
    {
        return $this->errors[$fieldKey] ?? [];
    }

    /**
     * エラーが存在するかどうかを確認する
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
