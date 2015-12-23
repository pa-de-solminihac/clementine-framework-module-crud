<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
    Clementine::getBlock($data['class'] . '/header', $data, $request);
}
