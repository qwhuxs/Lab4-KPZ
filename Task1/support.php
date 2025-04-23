<?php

abstract class SupportHandler {
    protected $nextHandler;

    public function setNext(SupportHandler $handler) {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle($level) {
        if ($this->canHandle($level)) {
            return $this->process();
        } elseif ($this->nextHandler !== null) {
            return $this->nextHandler->handle($level);
        }
        return "❗ Ваш запит не вдалося обробити.";
    }

    abstract protected function canHandle($level);
    abstract protected function process();
}

class BasicSupport extends SupportHandler {
    protected function canHandle($level) {
        return $level == 1;
    }

    protected function process() {
        return "✅ Ваше питання вирішено на базовому рівні підтримки.";
    }
}

class BillingSupport extends SupportHandler {
    protected function canHandle($level) {
        return $level == 2;
    }

    protected function process() {
        return "✅ Вас з'єднано з відділом з питань оплати.";
    }
}

class TechnicalSupport extends SupportHandler {
    protected function canHandle($level) {
        return $level == 3;
    }

    protected function process() {
        return "✅ Ви з'єднані з технічною підтримкою.";
    }
}

class SupervisorSupport extends SupportHandler {
    protected function canHandle($level) {
        return $level == 4;
    }

    protected function process() {
        return "✅ Ваш запит направлено до керівника служби підтримки.";
    }
}

class ExitSupport extends SupportHandler {
    protected function canHandle($level) {
        return $level == 999;
    }

    protected function process() {
        return "❗ Ви вийшли з системи. До побачення!";
    }
}

class DefaultSupport extends SupportHandler {
    protected function canHandle($level) {
        return true;
    }

    protected function process() {
        return "❌ Жоден рівень не підійшов. Повторіть вибір.";
    }
}

$basic = new BasicSupport();
$billing = new BillingSupport();
$tech = new TechnicalSupport();
$supervisor = new SupervisorSupport();
$exit = new ExitSupport();
$default = new DefaultSupport();
$basic->setNext($billing)->setNext($tech)->setNext($supervisor)->setNext($exit)->setNext($default);

$response = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $choice = (int)($_POST['option'] ?? 0);
    if ($choice > 0) {
        $response = $basic->handle($choice);
    } else {
        $response = "❌ Невірний вибір. Спробуйте знову.";
    }    
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Служба підтримки</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f0f0f0; }
        .container { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
        h1 { color: #333; }
        .response { margin-top: 20px; font-weight: bold; color: #005500; }
        .error { color: #bb0000; }
        button { padding: 10px 20px; font-size: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>📞 Служба підтримки</h1>
    <form method="post">
        <p>Оберіть опцію:</p>
        <label><input type="radio" name="option" value="1"> 1 - Загальні питання</label><br>
        <label><input type="radio" name="option" value="2"> 2 - Оплата</label><br>
        <label><input type="radio" name="option" value="3"> 3 - Технічна підтримка</label><br>
        <label><input type="radio" name="option" value="4"> 4 - З'єднання з керівником</label><br>
        <label><input type="radio" name="option" value="999"> 999 - Вихід з системи</label><br><br>
        <button type="submit">Надіслати</button>
    </form>

    <?php if ($response): ?>
        <div class="response <?= str_starts_with($response, '❌') ? 'error' : '' ?>">
            <?= htmlspecialchars($response) ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
