<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
    $this->getBlock($data['class'] . '/header', $data, $request);
}
