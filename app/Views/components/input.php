<?php
// Params: $label, $type='text', $id, $name, $value='', $placeholder='', $required=false, $options=[] (for select)

$value = $value ?? '';
$type = $type ?? 'text';
$options = $options ?? [];
$required = $required ?? false;
$placeholder = $placeholder ?? '';
$readonly = $readonly ?? false;
?>


<div class="mb-4">
    <?php if (isset($label)): ?>
        <label for="<?= esc($id) ?>" class="block text-sm font-medium text-blue-700 mb-1">
            <?= esc($label) ?>
            <?php if (!empty($required)): ?><span class="text-red-500">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <?php if ($type === 'textarea'): ?>
        <textarea id="<?= esc($id) ?>" name="<?= esc($name) ?>" placeholder="<?= esc($placeholder) ?>"
            class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
            <?= $required ? 'required' : '' ?>><?= esc($value) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select id="<?= esc($id) ?>" name="<?= esc($name) ?>"
            class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
            <?= $required ? 'required' : '' ?>>
            <option value="">-- Pilih --</option>
            <?php foreach ($options as $optVal => $optLabel): ?>
                <option value="<?= esc($optVal) ?>" <?= $optVal == $value ? 'selected' : '' ?>><?= esc($optLabel) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input
            <input
            type="<?= esc($type) ?>"
            id="<?= esc($id) ?>"
            name="<?= esc($name) ?>"
            value="<?= esc($value) ?>"
            placeholder="<?= esc($placeholder) ?>"
            class="border border-blue-300 rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-400"
            <?= $required ? 'required' : '' ?>
            <?= !empty($readonly) ? 'readonly' : '' ?> />

    <?php endif; ?>
</div>