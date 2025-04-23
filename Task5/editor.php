<?php
session_start();

class TextDocument {
    private string $text;

    public function __construct(string $text = "") {
        $this->text = $text;
    }

    public function setText(string $text): void {
        $this->text = $text;
    }

    public function getText(): string {
        return $this->text;
    }

    public function createMemento(): string {
        return $this->text;
    }

    public function restore(string $memento): void {
        $this->text = $memento;
    }
}

class TextEditor {
    private TextDocument $document;
    private array $history = [];

    public function __construct() {
        $this->document = new TextDocument();
        if (isset($_SESSION['history'])) {
            $this->history = $_SESSION['history'];
        }
        if (isset($_SESSION['text'])) {
            $this->document->setText($_SESSION['text']);
        }
    }

    public function type(string $newText): void {
        $this->history[] = $this->document->createMemento();
        $this->document->setText($newText);
        $this->syncSession();
    }

    public function undo(): void {
        if (!empty($this->history)) {
            $memento = array_pop($this->history);
            $this->document->restore($memento);
            $this->syncSession();
        }
    }

    public function getText(): string {
        return $this->document->getText();
    }

    private function syncSession(): void {
        $_SESSION['text'] = $this->document->getText();
        $_SESSION['history'] = $this->history;
    }
}

$editor = new TextEditor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'type') {
        $editor->type($_POST['text']);
    } elseif ($_POST['action'] === 'undo') {
        $editor->undo();
    }
}

$content = htmlspecialchars($editor->getText());
include 'template.php';
