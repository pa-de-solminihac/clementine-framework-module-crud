<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['footer'])) {
    $this->getBlock($data['class'] . '/footer', $data, $request);
}
