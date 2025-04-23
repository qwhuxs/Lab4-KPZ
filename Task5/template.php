<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>📝 Текстовий Редактор (Memento)</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .editor {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        textarea {
            width: 100%;
            height: 200px;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            resize: none;
            font-family: inherit;
        }

        .buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        button {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            background-color: #3498db;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="editor">
        <h1>📝 Текстовий Редактор (Memento)</h1>
        <form method="post">
        <textarea name="text"><?= $content ?></textarea>
            <div class="buttons">
                <button name="action" value="type">Зберегти</button>
                <button name="action" value="undo">Скасувати</button>
            </div>
        </form>
    </div>

</body>
</html>