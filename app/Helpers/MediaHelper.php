<?php
namespace App\Helpers;

class MediaHelper {
    /**
     * Render a standard dynamic media picker component.
     */
    public static function renderField(string $inputName, ?string $currentValue, ?string $fieldId = null): string {
        $fieldId = $fieldId ?: uniqid('media_');
        $hasValue = !empty($currentValue);
        
        $previewHtml = '';
        if ($hasValue) {
            $previewHtml = '<img src="' . htmlspecialchars($currentValue) . '" alt="Aperçu" style="width: 100%; height: 100%; object-fit: cover;">';
        } else {
            $previewHtml = '<i data-lucide="image" style="width: 22px; height: 22px; color: var(--text-muted);"></i>';
        }
        
        $removeBtnStyle = $hasValue ? 'display: inline-flex;' : 'display: none;';
        $filename = $hasValue ? basename($currentValue) : '';
        
        return '
        <div class="media-picker-component" data-field-id="' . htmlspecialchars($fieldId) . '" style="display: flex; gap: 16px; align-items: center;">
            <div class="image-field-preview" id="preview-' . htmlspecialchars($fieldId) . '" style="width: 70px; height: 70px; border-radius: 12px; border: 1px solid var(--border); background-color: rgba(255, 255, 255, 0.5); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                ' . $previewHtml . '
            </div>
            <div style="flex-grow: 1; display: flex; flex-direction: column; gap: 6px;">
                <input type="hidden" name="' . htmlspecialchars($inputName) . '" id="input-' . htmlspecialchars($fieldId) . '" value="' . htmlspecialchars($currentValue ?? '') . '" class="media-input-value">
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn-primary select-media-btn" data-target="input-' . htmlspecialchars($fieldId) . '" data-preview="preview-' . htmlspecialchars($fieldId) . '" data-label="label-' . htmlspecialchars($fieldId) . '" style="padding: 8px 14px; font-size: 0.8rem; height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i data-lucide="image" style="width: 14px; height: 14px;"></i> Choisir...
                    </button>
                    <button type="button" class="btn-danger remove-media-btn" data-target="input-' . htmlspecialchars($fieldId) . '" data-preview="preview-' . htmlspecialchars($fieldId) . '" data-label="label-' . htmlspecialchars($fieldId) . '" style="padding: 8px 14px; font-size: 0.8rem; height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; ' . $removeBtnStyle . '">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Supprimer
                    </button>
                </div>
                <div class="media-name-label" id="label-' . htmlspecialchars($fieldId) . '" style="font-size: 0.72rem; color: var(--text-muted); font-family: monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 250px;">
                    ' . htmlspecialchars($filename) . '
                </div>
            </div>
        </div>';
    }
}
