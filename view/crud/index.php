<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
    $this->getBlock($data['class'] . '/header-' . $data['formtype'], $data, $request);
}
$this->getBlock($data['class'] . '/index_content', $data, $request);
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['footer'])) {
    $this->getBlock($data['class'] . '/footer-' . $data['formtype'], $data, $request);
}
