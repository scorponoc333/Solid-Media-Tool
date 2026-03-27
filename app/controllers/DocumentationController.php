<?php

class DocumentationController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('documentation/index', [
            'pageTitle' => 'Documentation',
        ]);
    }
}
