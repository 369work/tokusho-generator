<?php

declare(strict_types=1);

namespace Tokusho\Validator;

use Tokusho\Field\FieldRegistry;
use Tokusho\Exception\ValidationException;

/**
 * 特商法フィールドのバリデーションクラス
 *
 * 必須項目の欠落・形式不正・文字数超過などをチェックし、
 * エラーがある場合は ValidationException をスローする。
 */
class TokushoValidator
{
    public function __construct(
        private readonly FieldRegistry $registry,
    ) {}

    /**
     * 入力データを検証する
     *
     * @param array<string, string> $data フィールドキー => 値
     * @throws ValidationException エラーが1件以上ある場合
     */
    public function validate(array $data): void
    {
        $errors = [];

        foreach ($this->registry->all() as $key => $field) {
            $value = isset($data[$key]) ? trim((string) $data[$key]) : '';

            // 必須チェック
            if ($field->isRequired() && $value === '') {
                $errors[$key][] = "「{$field->getLabel()}」は必須項目です。";
                continue; // 空の場合は後続チェックをスキップ
            }

            if ($value === '') {
                continue; // 任意項目で空の場合はスキップ
            }

            // 最大文字数チェック
            $maxLength = $field->getMaxLength();
            if ($maxLength !== null && mb_strlen($value) > $maxLength) {
                $errors[$key][] = "「{$field->getLabel()}」は{$maxLength}文字以内で入力してください（現在 " . mb_strlen($value) . " 文字）。";
            }

            // 型別フォーマットチェック
            $formatError = $this->validateFormat($key, $field->getType(), $value);
            if ($formatError !== null) {
                $errors[$key][] = $formatError;
            }
        }

        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
    }

    /**
     * フィールドタイプに応じたフォーマットチェックを行う
     *
     * @return string|null エラーメッセージ（問題なければ null）
     */
    private function validateFormat(string $key, string $type, string $value): ?string
    {
        return match ($type) {
            'email' => $this->validateEmail($value),
            'tel'   => $this->validatePhone($value),
            'url'   => $this->validateUrl($value),
            default => null,
        };
    }

    /**
     * メールアドレスの形式チェック
     */
    private function validateEmail(string $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return 'メールアドレスの形式が正しくありません。';
        }

        return null;
    }

    /**
     * 電話番号の形式チェック（日本の電話番号を想定）
     */
    private function validatePhone(string $value): ?string
    {
        // ハイフンあり・なし、国際形式（+81）を許容
        $normalized = preg_replace('/[\s\-\(\)ー－]/', '', $value);

        if ($normalized === null) {
            return '電話番号の形式が正しくありません。';
        }

        if (!preg_match('/^(\+81|0)\d{9,12}$/', $normalized)) {
            return '電話番号の形式が正しくありません（例：03-1234-5678 / 09012345678）。';
        }

        return null;
    }

    /**
     * URLの形式チェック
     *
     * ウェブサイトURLとして有効な http / https のみ許可する。
     * ftp など他スキームは実用上不要なため除外する。
     */
    private function validateUrl(string $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return 'URLの形式が正しくありません（例：https://example.com）。';
        }

        // http / https のみ許可
        if (!preg_match('/^https?:\/\//i', $value)) {
            return 'URLは https:// または http:// から始まる形式で入力してください。';
        }

        return null;
    }

    /**
     * バリデーションを実行し、例外をスローせずにエラー配列を返す
     *
     * @param array<string, string> $data
     * @return array<string, string[]> エラーが無ければ空配列
     */
    public function check(array $data): array
    {
        try {
            $this->validate($data);
            return [];
        } catch (ValidationException $e) {
            return $e->getErrors();
        }
    }
}
