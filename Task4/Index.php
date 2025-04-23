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

class NetworkImageStrategy
{
    public function load($source)
    {
        return "<img src=\"$source\" alt=\"Network Image\" />";
    }
}

class FileSystemImageStrategy
{
    public function load($source)
    {
        return "<img src=\"images/$source\" alt=\"Local Image\" />";
    }
}

class LightImageNode extends LightNode
{
    private $strategy;
    private $source;

    public function __construct($source, $isNetwork = false)
    {
        $this->source = $source;
        $this->strategy = $isNetwork ? new NetworkImageStrategy() : new FileSystemImageStrategy();
    }

    public function innerHTML(): string
    {
        return $this->strategy->load($this->source);
    }

    public function outerHTML(): string
    {
        return $this->innerHTML();
    }
}

?>

<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <title>LightHTML – Стратегія</title>
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <h1>📸 LightHTML – Стратегія</h1>

    <div class="output">
        <h2>outerHTML:</h2>
        <pre>
<?php
$list = new LightElementNode('ul', 'block', 'pair');
$list->addClass('list');

$items = ["Перший", "Другий", "Третій"];

foreach ($items as $text) {
    $item = new LightElementNode('li');
    $item->appendChild(new LightTextNode("$text пункт"));
    $list->appendChild($item);
}

$imageFromNetwork = new LightImageNode('https://plus.unsplash.com/premium_photo-1744991859949-6297ee4b8f96?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', true);
$imageFromFile = new LightImageNode('priroda2.jpg');

echo htmlspecialchars($list->outerHTML());
?>
    </pre>

        <h2>Фактичний HTML:</h2>
        <?= $list->outerHTML() ?>

        <h2>Зображення з мережі:</h2>
        <?= $imageFromNetwork->outerHTML() ?>

        <h2>Локальне зображення:</h2>
        <?= $imageFromFile->outerHTML() ?>
        <pre>
<?php echo htmlspecialchars(realpath('images/priroda2.jpg')); ?>
</pre>

    </div>

</body>

</html>