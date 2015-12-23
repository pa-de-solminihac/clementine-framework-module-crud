<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
    Clementine::getBlock($data['class'] . '/header-' . $data['formtype'], $data, $request);
}
Clementine::getBlock($data['class'] . '/index_content', $data, $request);
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['footer'])) {
    Clementine::getBlock($data['class'] . '/footer-' . $data['formtype'], $data, $request);
}
