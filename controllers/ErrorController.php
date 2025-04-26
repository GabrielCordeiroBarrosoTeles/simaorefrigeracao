<?php
class ErrorController {
    public function index() {
        http_response_code(404);
        require VIEWS_DIR . '/404.php';
    }
}
