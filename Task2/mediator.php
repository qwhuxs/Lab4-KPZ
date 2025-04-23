<?php
session_start();

class CommandCentre {
    public array $runways = [];
    public array $aircrafts = [];

    public function registerRunway(Runway $runway) {
        $this->runways[] = $runway;
    }

    public function registerAircraft(Aircraft $aircraft) {
        $this->aircrafts[] = $aircraft;
    }

    public function landAircraft(Aircraft $aircraft): string {
        foreach ($this->runways as $runway) {
            if ($runway->isAvailable()) {
                $runway->assignAircraft($aircraft);
                $aircraft->assignRunway($runway);
                return "🛬 Літак {$aircraft->getName()} приземлився на смугу {$runway->getId()}";
            }
        }
        return "❌ Немає вільних смуг для літака {$aircraft->getName()}";
    }

    public function takeOffAircraft(Aircraft $aircraft): string {
        $runway = $aircraft->getCurrentRunway();
        if ($runway !== null) {
            $runway->release();
            $aircraft->releaseRunway();
            return "🛫 Літак {$aircraft->getName()} вилетів зі смуги {$runway->getId()}";
        }
        return "❌ Літак {$aircraft->getName()} не на смузі";
    }
}

class Aircraft {
    private string $name;
    private ?Runway $currentRunway = null;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }

    public function assignRunway(Runway $runway) {
        $this->currentRunway = $runway;
    }

    public function releaseRunway() {
        $this->currentRunway = null;
    }

    public function getCurrentRunway(): ?Runway {
        return $this->currentRunway;
    }
}

class Runway {
    private string $id;
    private ?Aircraft $aircraftOnRunway = null;

    public function __construct() {
        $this->id = uniqid('RWY-');
    }

    public function getId(): string {
        return $this->id;
    }

    public function isAvailable(): bool {
        return $this->aircraftOnRunway === null;
    }

    public function assignAircraft(Aircraft $aircraft) {
        $this->aircraftOnRunway = $aircraft;
    }

    public function release() {
        $this->aircraftOnRunway = null;
    }
}

if (!isset($_SESSION['centre'])) {
    $cc = new CommandCentre();

    $r1 = new Runway();
    $r2 = new Runway();

    $a1 = new Aircraft("Boeing 737");
    $a2 = new Aircraft("Airbus A320");

    $cc->registerRunway($r1);
    $cc->registerRunway($r2);
    $cc->registerAircraft($a1);
    $cc->registerAircraft($a2);

    $_SESSION['centre'] = serialize($cc);
}

$cc = unserialize($_SESSION['centre']);

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aircraftName = $_POST['aircraft'];
    $action = $_POST['action'];

    foreach ($cc->aircrafts as $aircraft) {
        if ($aircraft->getName() === $aircraftName) {
            if ($action === 'land') {
                $message = $cc->landAircraft($aircraft);
            } elseif ($action === 'takeoff') {
                $message = $cc->takeOffAircraft($aircraft);
            }
            break;
        }
    }

    $_SESSION['centre'] = serialize($cc);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Аеропорт - Посередник</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f3;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            margin: auto;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px #ccc;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        select, button {
            padding: 10px;
            font-size: 16px;
            margin: 10px 0;
            width: 100%;
            box-sizing: border-box;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            font-weight: bold;
            background: #e0ffe0;
            color: #006600;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>✈️ Центр керування польотами</h1>

    <form method="post">
        <label for="aircraft">Виберіть літак:</label>
        <select name="aircraft" id="aircraft">
            <?php foreach ($cc->aircrafts as $air): ?>
                <option value="<?= $air->getName() ?>"><?= $air->getName() ?></option>
            <?php endforeach; ?>
        </select>

        <button name="action" value="land">🛬 Посадити</button>
        <button name="action" value="takeoff">🛫 Підняти</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>
</body>
</html>
