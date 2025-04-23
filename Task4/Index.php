<?php

abstract class LightNode
{
    abstract public function outerHTML(): string;
    abstract public function innerHTML(): string;
}

class LightTextNode extends LightNode
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = htmlspecialchars($text);
    }

    public function innerHTML(): string
    {
        return $this->text;
    }

    public function outerHTML(): string
    {
        return $this->text;
    }
}

class LightElementNode extends LightNode
{
    private string $tagName;
    private string $displayType;
    private string $closingType;
    private array $cssClasses = [];
    private array $attributes = [];
    private array $children = [];
    private array $eventListeners = [];

    public function __construct(string $tagName, string $displayType = 'block', string $closingType = 'pair')
    {
        $this->tagName = $tagName;
        $this->displayType = $displayType;
        $this->closingType = $closingType;
    }

    public function addClass(string $className): void
    {
        $this->cssClasses[] = $className;
    }

    public function setAttribute(string $name, string $value): void
    {
        $this->attributes[$name] = htmlspecialchars($value);
    }

    public function appendChild(LightNode $node): void
    {
        $this->children[] = $node;
    }

    public function addEventListener(string $event, string $jsHandler): void
    {
        $this->eventListeners[$event] = $jsHandler;
    }

    public function getChildCount(): int
    {
        return count($this->children);
    }

    public function innerHTML(): string
    {
        return implode('', array_map(fn($child) => $child->outerHTML(), $this->children));
    }

    private function buildAttributes(): string
    {
        $attrs = [];

        if (!empty($this->cssClasses)) {
            $attrs[] = 'class="' . implode(' ', $this->cssClasses) . '"';
        }

        foreach ($this->attributes as $name => $value) {
            $attrs[] = "$name=\"$value\"";
        }

        foreach ($this->eventListeners as $event => $handler) {
            $attrs[] = "on{$event}=\"$handler\"";
        }

        return $attrs ? ' ' . implode(' ', $attrs) : '';
    }

    public function outerHTML(): string
    {
        $attrStr = $this->buildAttributes();

        if ($this->closingType === 'single') {
            return "<{$this->tagName}{$attrStr}/>";
        }

        return "<{$this->tagName}{$attrStr}>" . $this->innerHTML() . "</{$this->tagName}>";
    }
}

function createFromArray(array $data): LightNode
{
    if (isset($data['text'])) {
        return new LightTextNode($data['text']);
    }

    $element = new LightElementNode(
        $data['tag'] ?? 'div',
        $data['display'] ?? 'block',
        $data['closing'] ?? 'pair'
    );

    if (!empty($data['class'])) {
        foreach ((array)$data['class'] as $cls) {
            $element->addClass($cls);
        }
    }

    if (!empty($data['attributes'])) {
        foreach ($data['attributes'] as $name => $value) {
            $element->setAttribute($name, $value);
        }
    }

    if (!empty($data['events'])) {
        foreach ($data['events'] as $event => $handler) {
            $element->addEventListener($event, $handler);
        }
    }

    if (!empty($data['children'])) {
        foreach ($data['children'] as $child) {
            $element->appendChild(createFromArray($child));
        }
    }

    return $element;
}

$list = new LightElementNode('ul', 'block', 'pair');
$list->addClass('list');

$items = ["Перший", "Другий", "Третій"];

foreach ($items as $text) {
    $item = new LightElementNode('li');
    $item->appendChild(new LightTextNode("$text пункт"));
    $item->addEventListener("click", "alert('Ви натиснули: $text пункт')");
    $list->appendChild($item);
}

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>LightHTML – Спостерігач</title>
    <style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: #f0f0f0;
    padding: 40px;
    margin: 0;
}

h1 {
    text-align: center;
    color: #333;
}

.output {
    background: #fff;
    padding: 20px;
    border-left: 4px solid #2ecc71;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    margin: 20px auto;
    width: 80%;
    max-width: 600px;
}

h2 {
    margin-top: 0;
    color: #3498db;
}

pre {
    background-color: #f9f9f9;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

ul {
    padding-left: 20px;
}

li {
    cursor: pointer;
    padding: 8px;
    margin: 4px 0;
    background: #ecf0f1;
    border-radius: 4px;
    transition: background 0.3s ease;
}

li:hover {
    background: #dfe6e9;
}

.resized-img {
    width: 10px !important;
    height: auto !important;
    display: block;
    margin: 10px auto;
}
    </style>
</head>
<body>

<h1>👁️‍🗨️ LightHTML – Спостерігач</h1>

<div class="output">
    <h2>outerHTML:</h2>
    <pre><?= htmlspecialchars($list->outerHTML()) ?></pre>

    <h2>Фактичний HTML з обробкою подій:</h2>
    <?= $list->outerHTML() ?>
</div>

</body>
</html>
